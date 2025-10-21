<?php
// app/init_db.php — create tables + seed demo data (run once)
require __DIR__.'/db.php';

$pdo->exec("
CREATE TABLE IF NOT EXISTS users (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  student_number TEXT UNIQUE,
  name TEXT NOT NULL,
  role TEXT NOT NULL CHECK (role IN ('student','teacher')),
  password_hash TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS tasks (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  title TEXT NOT NULL,
  description TEXT,
  html_file TEXT  -- optional: link to your existing lesson page in /html
);

CREATE TABLE IF NOT EXISTS student_tasks (
  user_id INTEGER NOT NULL,
  task_id INTEGER NOT NULL,
  status TEXT NOT NULL CHECK (status IN ('nicht_bearbeitet','korrekt','nicht_korrekt')) DEFAULT 'nicht_bearbeitet',
  updated_at TEXT NOT NULL DEFAULT (datetime('now')),
  PRIMARY KEY (user_id, task_id),
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (task_id) REFERENCES tasks(id)
);
");

// Users (one teacher + two demo students)
$insUser = $pdo->prepare("INSERT OR IGNORE INTO users (student_number,name,role,password_hash) VALUES (?,?,?,?)");
$insUser->execute([null, 'Frau Müller', 'teacher', password_hash('lehrerpasswort', PASSWORD_DEFAULT)]);
$insUser->execute(['S1001', 'Alex', 'student', password_hash('schueler1', PASSWORD_DEFAULT)]);
$insUser->execute(['S1002', 'Sam',  'student', password_hash('schueler2', PASSWORD_DEFAULT)]);

// Tasks mapped to your current html files (adjust paths/names to match your /html folder)
$pdo->exec("INSERT OR IGNORE INTO tasks (id,title,description,html_file) VALUES
 (1,'EV3 · Two Beeps & Name','Pybricks Step 1','03_01LegoEV3.html'),
 (2,'EV3 · Next Step','Continue with EV3','03_02LegoEV3.html'),
 (3,'Python Overview','Intro page','02_python_overview.html')
");

echo '<!doctype html><meta charset=\"utf-8\"><link rel=\"stylesheet\" href=\"../CSS/addons_login.css\"><div class=\"form\"><h2>DB initialisiert ✔</h2><p>Login unten testen:</p><ul><li>Lehrkraft: <code>Frau Müller</code> / <code>lehrerpasswort</code></li><li>Schüler: <code>S1001</code> / <code>schueler1</code></li><li>Schüler: <code>S1002</code> / <code>schueler2</code></li></ul><p><a class=\"btn primary\" href=\"login.html\">Zum Login</a></p></div>';
