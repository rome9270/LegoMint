<?php
// app/teacher_quick.php
session_start();
require __DIR__.'/db.php';

// nur Lehrkraft/Admin
$role = $_SESSION['user']['role'] ?? '';
if (!in_array($role, ['teacher','admin'], true)) {
  http_response_code(403);
  echo "Nur für Lehrkräfte. <a href='01_main.php'>Zurück</a>";
  exit;
}

// Aufgaben laden
$tasks = $pdo->query("SELECT id, title FROM tasks ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
// ausgewählte Aufgabe (Standard: erste)
$taskId = isset($_GET['task_id']) ? (int)$_GET['task_id'] : ($tasks[0]['id'] ?? 0);

// Schüler laden
$students = $pdo->query("SELECT id, student_number, name FROM users WHERE role='student' ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// Status zu der ausgewählten Aufgabe holen
$st = $pdo->prepare("
  SELECT user_id, status, sim_ok, download_ok, updated_at
  FROM student_tasks
  WHERE task_id = ?
");
$st->execute([$taskId]);
$rows = $st->fetchAll(PDO::FETCH_ASSOC);

$map = [];
foreach ($rows as $r) $map[$r['user_id']] = $r;

function makeBadge($row) {
  if (!$row) return '<span class="dot none" title="nicht bearbeitet"></span>';
  $status = $row['status'] ?: (
    ($row['sim_ok'] && $row['download_ok']) ? 'korrekt' :
    (($row['sim_ok'] || $row['download_ok']) ? 'teilweise' : 'nicht_bearbeitet')
  );
  $cls = match($status){
    'korrekt'         => 'ok',
    'teilweise'       => 'mid',
    'bearbeitet'      => 'mid',
    'nicht_korrekt'   => 'bad',
    default           => 'none'
  };
  $dt = $row['updated_at'] ? '<small class="ts">'.htmlspecialchars($row['updated_at']).'</small>' : '';
  return '<span class="dot '.$cls.'" title="'.$status.'"></span> '.$dt;
}
?>
<!doctype html>
<meta charset="utf-8">
<title>Lehrer – Schnellcheck</title>
<link rel="stylesheet" href="../CSS/01_main.css">
<style>
  body{max-width:900px;margin:30px auto;padding:0 12px;}
  h1{margin:0 0 14px;}
  form{display:flex;gap:10px;align-items:center;margin-bottom:12px;}
  select{padding:6px 8px;border:1px solid #d1d5db;border-radius:8px;}
  table{width:100%;border-collapse:collapse}
  th,td{border-bottom:1px solid #e5e7eb;padding:10px 8px;vertical-align:middle}
  th{text-align:left;background:#fafafa}
  .btn{padding:6px 10px;border:1px solid #d1d5db;border-radius:8px;background:#f8fafc;cursor:pointer}
  .btn.ok{background:#dcfce7;border-color:#22c55e}
  .btn.mid{background:#fef9c3;border-color:#eab308}
  .btn.none{background:#f3f4f6;border-color:#d1d5db}
  .btn.bad{background:#fee2e2;border-color:#ef4444}
  .dot{display:inline-block;width:12px;height:12px;border-radius:50%;vertical-align:middle;margin-right:6px;border:1px solid #999}
  .dot.ok{background:#22c55e;border-color:#16a34a}
  .dot.mid{background:#eab308;border-color:#ca8a04}
  .dot.bad{background:#ef4444;border-color:#b91c1c}
  .dot.none{background:#e5e7eb;border-color:#cbd5e1}
  .ts{color:#666}
</style>

<h1>Lehrer – Schnellcheck</h1>

<form method="get">
  <label>Aufgabe:
    <select name="task_id" onchange="this.form.submit()">
      <?php foreach($tasks as $t): ?>
        <option value="<?= $t['id'] ?>" <?= $t['id']===$taskId?'selected':'' ?>>
          <?= htmlspecialchars($t['title']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </label>
  <a class="btn" href="01_main.php">← Zur Hauptseite</a>
</form>

<table>
  <thead>
    <tr>
      <th>Schüler</th>
      <th>Nr.</th>
      <th>Status</th>
      <th>Aktion</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach($students as $s): 
      $row = $map[$s['id']] ?? null; ?>
      <tr data-user="<?= $s['id'] ?>">
        <td><strong><?= htmlspecialchars($s['name']) ?></strong></td>
        <td><?= htmlspecialchars($s['student_number']) ?></td>
        <td class="status-cell"><?= makeBadge($row) ?></td>
        <td>
          <button class="btn ok"   onclick="setStatus(<?= $s['id'] ?>,'korrekt');return false;">korrekt</button>
          <button class="btn mid"  onclick="setStatus(<?= $s['id'] ?>,'teilweise');return false;">teilweise</button>
          <button class="btn bad"  onclick="setStatus(<?= $s['id'] ?>,'nicht_korrekt');return false;">nicht</button>
          <button class="btn none" onclick="setStatus(<?= $s['id'] ?>,'nicht_bearbeitet');return false;">zurücksetzen</button>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<script>
async function setStatus(userId, status){
  const taskId = <?= (int)$taskId ?>;
  const res = await fetch('update_status.php', {
    method: 'POST',
    credentials: 'include',
    headers: {'Content-Type':'application/x-www-form-urlencoded'},
    body: new URLSearchParams({ user_id:String(userId), task_id:String(taskId), status })
  });
  if (!res.ok) { alert('Fehler beim Speichern'); return; }
  // UI aktualisieren: Punkt + Datum vom Server holen
  // einfache Neu-Ladung der Seite:
  location.reload();
}
</script>
