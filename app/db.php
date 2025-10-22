<?php
// Einmal am Anfang: deutsche Zeitzone für Ausgabe
date_default_timezone_set('Europe/Berlin');

// Pfad zur SQLite-Datei
$dbFile = __DIR__ . '/data/schule.db';

// Ordner anlegen, falls fehlt
if (!is_dir(__DIR__ . '/data')) {
  mkdir(__DIR__ . '/data', 0777, true);
}

// Verbindung herstellen
$pdo = new PDO('sqlite:' . $dbFile);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
$pdo->exec('PRAGMA foreign_keys = ON;');
