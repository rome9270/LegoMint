<?php
require __DIR__ . '/db.php';

// Beispiel-Schüler und Lehrer
$users = [
  ['S1001', 'Alex', 'student', 'schueler1'],
  ['S1002', 'Sophie', 'student', 'schueler2'],
  [null, 'Frau Müller', 'teacher', 'lehrerpasswort']
];

$stmt = $pdo->prepare("
  INSERT INTO users (student_number, name, role, password_hash)
  VALUES (:sn, :name, :role, :hash)
");

$count = 0;
foreach ($users as $u) {
  $stmt->execute([
    ':sn'   => $u[0],
    ':name' => $u[1],
    ':role' => $u[2],
    ':hash' => password_hash($u[3], PASSWORD_DEFAULT)
  ]);
  $count++;
}

echo "<h2>$count Benutzer erfolgreich hinzugefügt ✅</h2>";
echo "<p><a href='login.html'>Zum Login</a></p>";
