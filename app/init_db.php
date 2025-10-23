<?php
// app/init_db.php
// ---------------------------
// Initialisiert die SQLite-Datenbank aus db_schema.sql + db_seed.sql
// und setzt alle Aufgaben korrekt neu.

require __DIR__ . '/db.php';

echo "<pre>";
echo "== 🧩 Initialisierung gestartet ==\n";

// ---- 1) Schema laden ----
$schemaFile = __DIR__ . '/../db/db_schema.sql';
$schemaSql  = file_get_contents($schemaFile);
if (!$schemaSql) {
  exit("❌ Fehler: db_schema.sql nicht gefunden.\n");
}

$pdo->exec($schemaSql);
echo "✔ Tabellenstruktur geladen.\n";

// ---- 2) Tasks leeren + Seed laden ----
$seedFile = __DIR__ . '/../db/db_seed.sql';
$seedSql  = file_get_contents($seedFile);
if (!$seedSql) {
  exit("❌ Fehler: db_seed.sql nicht gefunden.\n");
}

// Entfernt evtl. Kommentare und Leerzeilen
$seedSqlClean = preg_replace('/--.*$/m', '', $seedSql);
$seedSqlClean = trim($seedSqlClean);

// Mehrere SQL-Befehle einzeln ausführen
foreach (explode(';', $seedSqlClean) as $stmt) {
  $stmt = trim($stmt);
  if ($stmt !== '') {
    $pdo->exec($stmt);
  }
}

echo "✔ Seed-Daten aus db_seed.sql geladen.\n";

// ---- 3) Kontrolle ----
$tasks = $pdo->query("SELECT id, title, html_file FROM tasks ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
echo "== Aktuelle Aufgaben in der DB ==\n";
foreach ($tasks as $t) {
  echo "{$t['id']}. {$t['title']} → {$t['html_file']}\n";
}

echo "\n✅ Fertig!\n";
echo "</pre>";
