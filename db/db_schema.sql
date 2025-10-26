PRAGMA foreign_keys = ON;

-- Nutzer (Lehrer + Schüler)
CREATE TABLE IF NOT EXISTS users(
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  student_number TEXT UNIQUE,              -- z.B. S1001 (bei Lehrer optional)
  name TEXT NOT NULL,                      -- Login-/Anzeigename
  role TEXT NOT NULL CHECK(role IN('student','teacher')),
  password_hash TEXT NOT NULL              -- von password_hash() erzeugt
);

-- Aufgaben
CREATE TABLE IF NOT EXISTS tasks(
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  title TEXT NOT NULL,
  html_file TEXT                           -- Datei in /html (z.B. 03_06LegoEV3.html)
);

-- Status je Schüler + Aufgabe
CREATE TABLE IF NOT EXISTS student_tasks(
  user_id INTEGER NOT NULL,
  task_id INTEGER NOT NULL,
  status  TEXT NOT NULL DEFAULT 'nicht_bearbeitet'
          CHECK(status IN('nicht_bearbeitet','korrekt','nicht_korrekt')),
  updated_at TEXT NOT NULL DEFAULT (datetime('now')), -- UTC
  sim_ok INTEGER NOT NULL DEFAULT 0,                  -- Simulation ok?
  sim_at  TEXT,
  download_ok INTEGER NOT NULL DEFAULT 0,             -- Datei gespeichert?
  download_at TEXT,
  PRIMARY KEY(user_id,task_id),
  FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY(task_id) REFERENCES tasks(id) ON DELETE CASCADE
);
