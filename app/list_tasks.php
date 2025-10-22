<?php
require __DIR__.'/db.php';
echo '<pre>';
print_r(
  $pdo->query("SELECT id, title, html_file FROM tasks ORDER BY id")->fetchAll()
);
