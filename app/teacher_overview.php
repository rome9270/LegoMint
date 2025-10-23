<?php
// app/teacher_overview.php
session_start();
require __DIR__.'/db.php';

// Nur Lehrkräfte reinlassen
if (empty($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'teacher') {
  http_response_code(403);
  echo "Nur für Lehrkräfte. <a href='01_main.php'>Zurück</a>";
  exit;
}

// --- Daten laden ---
$tasks = $pdo->query("SELECT id, title FROM tasks ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
$students = $pdo->query("SELECT id, student_number, name FROM users WHERE role='student' ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// Alle Status-Einträge auf einmal holen
$stRows = $pdo->query("
  SELECT user_id, task_id, status, sim_ok, download_ok, updated_at
  FROM student_tasks
")->fetchAll(PDO::FETCH_ASSOC);

// In Map umwandeln: $stMap[user_id][task_id] = row
$stMap = [];
foreach ($stRows as $r) {
  $stMap[$r['user_id']][$r['task_id']] = $r;
}

// CSV-Export?
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
  header('Content-Type: text/csv; charset=utf-8');
  header('Content-Disposition: attachment; filename=uebersicht.csv');

  $out = fopen('php://output', 'w');
  // Kopfzeile
  $head = ['Schüler', 'Nr.'];
  foreach ($tasks as $t) $head[] = $t['title'];
  fputcsv($out, $head, ';');

  foreach ($students as $s) {
    $row = [$s['name'], $s['student_number']];
    foreach ($tasks as $t) {
      $cell = '-';
      if (isset($stMap[$s['id']][$t['id']])) {
        $st = $stMap[$s['id']][$t['id']];
        // Anzeige priorisieren: status falls gesetzt, sonst aus Flags ableiten
        if ($st['status']) $cell = $st['status'];
        else if ($st['sim_ok'] && $st['download_ok']) $cell = 'korrekt';
        else if ($st['sim_ok'] || $st['download_ok']) $cell = 'teilweise';
        else $cell = 'nicht_bearbeitet';
        if (!empty($st['updated_at'])) $cell .= ' ('.$st['updated_at'].')';
      }
      $row[] = $cell;
    }
    fputcsv($out, $row, ';');
  }
  fclose($out);
  exit;
}

function renderCell($st) {
  if (!$st) return '<span class="chip none">–</span>';
  $status = $st['status'] ?: (
    ($st['sim_ok'] && $st['download_ok']) ? 'korrekt' :
    (($st['sim_ok'] || $st['download_ok']) ? 'teilweise' : 'nicht_bearbeitet')
  );
  $when = $st['updated_at'] ? '<div class="ts">'.$st['updated_at'].'</div>' : '';

  switch ($status) {
    case 'korrekt':           $cls='ok';    $txt='✅ korrekt'; break;
    case 'nicht_korrekt':     $cls='bad';   $txt='❌ nicht korrekt'; break;
    case 'teilweise':         $cls='mid';   $txt='🟡 teilweise'; break;
    case 'bearbeitet':        $cls='mid';   $txt='🟡 bearbeitet'; break;
    case 'nicht_bearbeitet':
    default:                  $cls='none';  $txt='⚪ nicht'; break;
  }
  // kleine Hinweise, falls gewünscht
  $flags = [];
  if ((int)$st['sim_ok']===1) $flags[]='Sim';
  if ((int)$st['download_ok']===1) $flags[]='Down';
  $hint = $flags ? '<div class="flags">'.implode(' · ',$flags).'</div>' : '';

  return '<span class="chip '.$cls.'">'.$txt.'</span>'.$hint.$when;
}
?>
<!doctype html>
<meta charset="utf-8">
<title>Lehrerübersicht</title>
<link rel="stylesheet" href="../CSS/01_main.css">
<style>
  body{max-width:1200px;margin:40px auto;padding:0 12px;}
  h1{ text-align:center; margin-bottom:10px; }
  .legend{ display:flex; gap:12px; justify-content:center; margin: 8px 0 18px; font-size:14px; color:#444;}
  .legend .chip{padding:4px 8px; border-radius:999px; font-weight:600;}
  .chip.ok{ background:#dcfce7; border:1px solid #22c55e;}
  .chip.mid{ background:#fef9c3; border:1px solid #eab308;}
  .chip.bad{ background:#fee2e2; border:1px solid #ef4444;}
  .chip.none{ background:#f3f4f6; border:1px solid #d1d5db;}
  .flags{ font-size:12px; color:#555; margin-top:4px;}
  .ts{ font-size:11px; color:#777; }
  table{ width:100%; border-collapse:collapse; }
  th,td{ border-bottom:1px solid #e5e7eb; padding:10px 8px; vertical-align:top; }
  th{ text-align:left; background:#fafafa; position:sticky; top:0; }
  .sticky-left{ position:sticky; left:0; background:white; z-index:2;}
  .controls{ display:flex; gap:10px; justify-content:space-between; align-items:center; margin: 16px 0;}
  .btn{ display:inline-block; padding:8px 12px; border:1px solid #d1d5db; border-radius:10px; text-decoration:none;}
  .btn.primary{ background:#4CAF50; color:white; border-color:#4CAF50; }
  .table-wrap{ overflow:auto; border:1px solid #e5e7eb; border-radius:12px;}
</style>

<h1>Lehrerübersicht – Wer hat was geschafft?</h1>

<div class="legend">
  <span class="chip ok">✅ korrekt</span>
  <span class="chip mid">🟡 teilweise</span>
  <span class="chip bad">❌ nicht korrekt</span>
  <span class="chip none">⚪ nicht</span>
</div>

<div class="controls">
  <a class="btn" href="01_main.php">← Zur Hauptseite</a>
  <div>
    <a class="btn" href="?export=csv">CSV exportieren</a>
  </div>
</div>

<div class="table-wrap">
<table>
  <thead>
    <tr>
      <th class="sticky-left">Schüler</th>
      <th class="sticky-left">Nr.</th>
      <?php foreach($tasks as $t): ?>
        <th><?= htmlspecialchars($t['title']) ?></th>
      <?php endforeach; ?>
    </tr>
  </thead>
  <tbody>
    <?php foreach($students as $s): ?>
      <tr>
        <td class="sticky-left"><strong><?= htmlspecialchars($s['name']) ?></strong></td>
        <td class="sticky-left"><?= htmlspecialchars($s['student_number']) ?></td>
        <?php foreach($tasks as $t):
          $st = $stMap[$s['id']][$t['id']] ?? null; ?>
          <td><?= renderCell($st) ?></td>
        <?php endforeach; ?>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
</div>
