<?php
require __DIR__ . '/db.php';

/*
  Dieses Skript legt Test-Accounts an oder aktualisiert sie (UPSERT):
  - Schüler S1001..S1004
  - Lehrer L1 (Passwort: lehrer123)
  - Admin  A1 (Passwort: admin123)
  Du kannst es beliebig oft aufrufen.
*/

$users = [
  ['S1001', 'Alex',   'student', 'schueler1'],
  ['S1002', 'Sophie', 'student', 'schueler2'],
  ['S1003', 'Sophi',  'student', 'schueler3'],
  ['S1004', 'Mari',   'student', 'schueler4'],
  ['L1',    'RoMe',   'teacher', 'lehrer123'], // Lehrer
  ['A1',    'Admin',  'admin',   'admin123'],  // Admin
];

/* SQLite-UPSERT:
   - Wenn student_number bereits existiert, werden name/role/password_hash aktualisiert.
   - Voraussetzung: in users ist student_number UNIQUE (ist bei dir der Fall).
*/
$sql = "
INSERT INTO users (student_number, name, role, password_hash)
VALUES (:sn, :name, :role, :hash)
ON CONFLICT(student_number) DO UPDATE SET
  name = excluded.name,
  role = excluded.role,
  password_hash = excluded.password_hash
";
$stmt = $pdo->prepare($sql);

$inserted = 0; $updated = 0;
foreach ($users as [$sn,$name,$role,$pw]) {
  $hash = password_hash($pw, PASSWORD_DEFAULT);
  $stmt->execute([':sn'=>$sn, ':name'=>$name, ':role'=>$role, ':hash'=>$hash]);
  // SQLite zählt das als 1 Änderung, egal ob Insert oder Update.
  // Zur groben Info:
  $updated++; 
}

echo "<!doctype html><meta charset='utf-8'><body style='font-family:Arial,sans-serif'>";
echo "<h3>Benutzer angelegt/aktualisiert</h3>";
echo "<ul>";
echo "<li>Lehrer: <b>L1 / lehrer123</b></li>";
echo "<li>Admin: <b>A1 / admin123</b></li>";
echo "<li>Schüler: S1001..S1004 / schueler1..schueler4</li>";
echo "</ul>";
echo "<p><a href='login.html'>→ Zum Login</a></p>";
echo "</body>";
