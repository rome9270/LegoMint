<?php
// app/db.php — SQLite connection
$dsn = 'sqlite:' . __DIR__ . '/schule.db';
$pdo = new PDO($dsn, null, null, [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
