<?php
// app/login.php
session_start();
require __DIR__ . '/db.php';

// Form-Felder
$login = trim($_POST['user'] ?? '');
$pw    = $_POST['pw'] ?? '';

if ($login === '' || $pw === '') {
  header('Location: login.html');
  exit;
}

// Nutzer aus DB holen (per Name ODER Schülernummer)
$stmt = $pdo->prepare("
  SELECT id, name, role, student_number, password_hash
  FROM users
  WHERE name = :login OR student_number = :login
  LIMIT 1
");
$stmt->execute([':login' => $login]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Passwort prüfen
if (!$user || !password_verify($pw, $user['password_hash'])) {
  http_response_code(401);
  echo "<!doctype html><meta charset='utf-8'>
        <link rel='stylesheet' href='../CSS/addons_login.css'>
        <div class='form'>
          <h2>Login fehlgeschlagen</h2>
          <p>Bitte erneut versuchen.</p>
          <a class='btn primary' href='login.html'>Zurück zum Login</a>
        </div>";
  exit;
}

// Rolle aus dem EINGEGEBENEN Login ableiten (nicht aus $_POST['username']!)
$roleFromName = 'student';
if (preg_match('/^[lL]/', $login)) {
  $roleFromName = 'teacher';
} elseif (preg_match('/^[aA]/', $login)) {
  $roleFromName = 'admin';
}

// Session setzen (Rolle aus Login hat Vorrang)
session_regenerate_id(true);
$_SESSION['user'] = [
  'id'             => (int)$user['id'],
  'name'           => $user['name'],
  'student_number' => $user['student_number'] ?? '',
  'role'           => $roleFromName,   // <- L…/A…-Regel
];

// Weiter zur Startseite (deine app/01_main.php macht dann ggf. Redirect auf HTML)
header('Location: 01_main.php');
exit;
