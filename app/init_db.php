<?php
require __DIR__.'/db.php';

// Schema laden und ausführen
$schema = file_get_contents(__DIR__ . '/../db/db_schema.sql');
$pdo->exec($schema);

// Seed nur, wenn noch keine Tasks existieren
$has = (int)$pdo->query("SELECT COUNT(*) FROM tasks")->fetchColumn();
if ($has === 0 && file_exists(__DIR__.'/../db/db_seed.sql')) {
  $seed = file_get_contents(__DIR__.'/../db/db_seed.sql');
  $pdo->exec($seed);
}

// einfache Rückmeldung
echo "<!doctype html><meta charset='utf-8'>
<link rel='stylesheet' href='../CSS/addons_login.css'>
<div class='form'><h2>DB initialisiert ✔</h2>
<p><a class='btn primary' href='login.html'>Zum Login</a></p></div>";
