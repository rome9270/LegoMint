<?php
// C:\xampp\htdocs\LegoMint\app\list_users.php
require __DIR__ . '/db.php';
header('Content-Type: text/plain; charset=utf-8');

$rows = $pdo->query("
  SELECT id, student_number, name, role
  FROM users
  ORDER BY student_number
")->fetchAll(PDO::FETCH_ASSOC);

print_r($rows);
