-- ==========================================
-- 📘 Datenbankschema für LegoMint
-- ==========================================

PRAGMA foreign_keys = ON;

-- =============================
-- 👤 USERS (Lehrer + Schüler)
-- =============================
CREATE TABLE IF NOT EXISTS users (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  student_number TEXT UNIQUE,               -- z.B. S1001 (optional bei Lehrern)
  name TEXT NOT NULL,                       -- Login- / Anzeigename
  role TEXT NOT NULL CHECK(role IN('student','teacher','admin')),
  password_hash TEXT NOT NULL               -- Hash von password_hash()
);

-- =============================
-- 🧩 TASKS (Aufgaben-Pool)
-- =============================
CREATE TABLE IF NOT EXISTS tasks (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  title TEXT NOT NULL,                      -- Anzeigename (z.B. "EV3 - drive a square")
  html_file TEXT NOT NULL,                  -- relativer Pfad in /html/
  category TEXT NOT NULL CHECK(category IN('ev3','python')),  -- Aufgaben-Typ
  level TEXT NOT NULL CHECK(level IN('basic','advanced'))     -- Kursstufe
);

-- =============================
-- 📊 STUDENT_TASKS (Fortschritt)
-- =============================
CREATE TABLE IF NOT EXISTS student_tasks (
  user_id INTEGER NOT NULL,
  task_id INTEGER NOT NULL,
  status TEXT NOT NULL DEFAULT 'nicht_bearbeitet'
         CHECK(status IN('nicht_bearbeitet','korrekt','nicht_korrekt')),

  -- Fortschritt / Aktionen
  sim_ok INTEGER NOT NULL DEFAULT 0,          -- Simulation korrekt ausgeführt?
  sim_at TEXT,                                -- Zeitpunkt der Simulation
  download_ok INTEGER NOT NULL DEFAULT 0,     -- Datei gespeichert?
  download_at TEXT,                           -- Zeitpunkt des Downloads

  -- Zeitstempel letzte Änderung
  updated_at TEXT NOT NULL DEFAULT (datetime('now')),

  PRIMARY KEY (user_id, task_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE
);
