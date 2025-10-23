<?php
// app/upload_users.php
// CSV-Import (Lehrer/Admin). Erwartet Header: student_number,name,role,password
session_start();
require __DIR__ . '/db.php';

$u = $_SESSION['user'] ?? null;
if (!$u || !in_array(($u['role'] ?? ''), ['teacher','admin'], true)) {
  http_response_code(403);
  echo "<!doctype html><meta charset='utf-8'><p>Kein Zugriff.</p>";
  exit;
}

function normalize_role(string $r): string {
  $r = strtolower(trim($r));
  if ($r === 'l' || $r === 't') return 'teacher';
  if ($r === 'a')             return 'admin';
  if (!in_array($r, ['student','teacher','admin'], true)) return 'student';
  return $r;
}

function detect_delim(string $line): string {
  // sehr einfache Heuristik: zähle ; und , in der Header-Zeile
  $sc = substr_count($line, ';');
  $cc = substr_count($line, ',');
  if ($sc >= $cc) return ';';
  return ',';
}

$report = null;
$rows   = [];

if (!empty($_POST['import']) && isset($_FILES['csv']) && is_uploaded_file($_FILES['csv']['tmp_name'])) {
  $tmp  = $_FILES['csv']['tmp_name'];
  $raw  = file_get_contents($tmp);

  // BOM entfernen (Excel "CSV UTF-8")
  $raw  = preg_replace('/^\xEF\xBB\xBF/', '', $raw);

  // Erste Zeile für Delimiter-Erkennung
  $firstLine = strtok($raw, "\r\n");
  $delim     = detect_delim($firstLine ?: '');

  // Jetzt normal per fgetcsv lesen
  $h = fopen($tmp, 'r');
  if (!$h) {
    $report = ['ok'=>false, 'msg'=>'Datei konnte nicht gelesen werden.'];
  } else {
    $header = fgetcsv($h, 0, $delim) ?: [];
    // Header normalisieren
    $header = array_map(fn($v)=>strtolower(trim($v)), $header);

    // akzeptierte Header
    // mindestens student_number + name; role/password optional
    $idx_sn = array_search('student_number', $header);
    $idx_nm = array_search('name',           $header);
    $idx_ro = array_search('role',           $header);
    $idx_pw = array_search('password',       $header);

    if ($idx_sn === false || $idx_nm === false) {
      $report = ['ok'=>false, 'msg'=>'Header fehlt: "student_number" und/oder "name".'];
    } else {
      while (($r = fgetcsv($h, 0, $delim)) !== false) {
        if (count($r) === 1 && trim($r[0]) === '') continue; // leere Zeilen
        $sn = trim($r[$idx_sn] ?? '');
        $nm = trim($r[$idx_nm] ?? '');
        $ro = $idx_ro === false ? 'student' : normalize_role((string)($r[$idx_ro] ?? 'student'));
        $pw = $idx_pw === false ? ''        : (string)($r[$idx_pw] ?? '');
        if ($sn === '' || $nm === '') continue;
        $rows[] = [$sn, $nm, $ro, $pw];
      }
      fclose($h);

      // UPSERT
      $stmt = $pdo->prepare("
        INSERT INTO users (student_number, name, role, password_hash)
        VALUES (:sn, :name, :role, :hash)
        ON CONFLICT(student_number) DO UPDATE SET
          name = excluded.name,
          role = excluded.role,
          password_hash = COALESCE(NULLIF(excluded.password_hash,''), users.password_hash)
      ");

      $added = 0; $updated = 0;
      foreach ($rows as [$sn,$nm,$ro,$pw]) {
        // Wenn Password leer → wir lassen das existierende Hash stehen (siehe COALESCE oben)
        $hash = $pw !== '' ? password_hash($pw, PASSWORD_DEFAULT) : '';
        // Um festzustellen ob "add" oder "update": vorher kurz existenz prüfen
        $exists = (bool)$pdo->prepare("SELECT 1 FROM users WHERE student_number=?")->execute([$sn])
                  && (bool)$pdo->query("SELECT 1 FROM users WHERE student_number=".$pdo->quote($sn))->fetchColumn();

        $stmt->execute([':sn'=>$sn, ':name'=>$nm, ':role'=>$ro, ':hash'=>$hash]);

        if ($exists) $updated++; else $added++;
      }

      $report = ['ok'=>true, 'added'=>$added, 'updated'=>$updated, 'total'=>count($rows), 'delim'=>$delim];
    }
  }
}
?>
<!doctype html>
<meta charset="utf-8">
<link rel="stylesheet" href="../CSS/01_main.css">
<link rel="stylesheet" href="../CSS/addons_login.css">
<title>CSV-Import – Benutzer</title>

<div class="container" style="max-width:900px;margin:24px auto">
  <h1>Benutzer importieren (CSV)</h1>

  <p>Erlaubte Felder in der ersten Zeile (Header): 
    <code>student_number</code>, <code>name</code>, <code>role</code> (student/teacher/admin oder l/t/a), 
    <code>password</code> (optional).<br>
    Trennzeichen: <b>Komma</b> oder <b>Semikolon</b> wird automatisch erkannt.
  </p>

  <details style="margin:12px 0">
    <summary>Beispiel herunterladen</summary>
    <pre style="white-space:pre-wrap;background:#f8fafc;border:1px solid #e5e7eb;padding:10px;border-radius:8px">
student_number;name;role;password
S2001;Mira;student;mira123
S2002;Len;student;len123
L9;Herr Schmidt;teacher;lehrerpass
A9;Root Admin;admin;rootpass
    </pre>
  </details>

  <form method="post" enctype="multipart/form-data" class="form" style="gap:12px;">
    <input type="file" name="csv" accept=".csv,text/csv" required>
    <button class="btn primary" type="submit" name="import" value="1">CSV importieren</button>
    <a class="btn" href="01_main.php">← Zurück</a>
  </form>

  <?php if ($report): ?>
    <div class="card" style="margin-top:18px;">
      <h2>Ergebnis</h2>
      <?php if ($report['ok']): ?>
        <p>Trennzeichen erkannt: <b><?= htmlspecialchars($report['delim']) ?></b></p>
        <ul>
          <li>eingelesen: <b><?= (int)$report['total'] ?></b></li>
          <li>neu angelegt: <b><?= (int)$report['added'] ?></b></li>
          <li>aktualisiert: <b><?= (int)$report['updated'] ?></b></li>
        </ul>
        <?php if (!empty($rows)): ?>
          <table class="table" style="margin-top:12px;">
            <thead><tr><th>Nr</th><th>Student&nbsp;#</th><th>Name</th><th>Rolle</th><th>Passwort leer?</th></tr></thead>
            <tbody>
            <?php $i=1; foreach($rows as [$sn,$nm,$ro,$pw]): ?>
              <tr>
                <td><?= $i++ ?></td>
                <td><?= htmlspecialchars($sn) ?></td>
                <td><?= htmlspecialchars($nm) ?></td>
                <td><?= htmlspecialchars($ro) ?></td>
                <td><?= $pw==='' ? 'ja' : 'nein' ?></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      <?php else: ?>
        <p style="color:#b91c1c">❌ <?= htmlspecialchars($report['msg']) ?></p>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</div>
