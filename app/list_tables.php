<?php
require __DIR__.'/db.php';
$tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name")
              ->fetchAll(PDO::FETCH_COLUMN);
echo "<pre>";
print_r($tables);
