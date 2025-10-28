-- ======================================
-- 🧩 Seed-Daten für Tabelle: tasks
-- ======================================

DELETE FROM tasks;
DELETE FROM sqlite_sequence WHERE name='tasks';

-- =============================
-- LEGO EV3 · BASIC COURSE
-- =============================
INSERT INTO tasks (title, html_file, category, level) VALUES
('EV3 Step 1: Two Beeps & Name',         '03_01LegoEV3_I.html',  'ev3', 'basic'),
('EV3 - drive a square',                 '03_02LegoEV3_I.html',  'ev3', 'basic'),
('EV3 - drive a circle',                 '03_03LegoEV3_I.html',  'ev3', 'basic'),
('EV3 - drive a figure 8',               '03_04LegoEV3_I.html',  'ev3', 'basic'),
('EV3 - stop in front of a wall',        '03_05LegoEV3_I.html',  'ev3', 'basic'),
('EV3 - find the door (touch)',          '03_06LegoEV3_I.html',  'ev3', 'basic'),

-- =============================
-- LEGO EV3 · ADVANCED COURSE
-- =============================
('EV3 - find the door (touch & gyro)',   '03_07LegoEV3_I.html',  'ev3', 'basic'),
('EV3 - drive a square with gyro',       '03_08LegoEV3_I.html',  'ev3', 'basic'),
('EV3 - line follower',                  '03_09LegoEV3_I.html',  'ev3', 'basic'),
('EV3 - line follower & touch',          '03_10LegoEV3_I.html',  'ev3', 'basic'),
('EV3 - warehouse mission',              '04_01LegoEV3_I.html',  'ev3', 'advanced'),
('EV3 - warehouse mission & person safety', '04_02LegoEV3_I.html','ev3','advanced'),

-- =============================
-- PYTHON · BASIC COURSE
-- =============================
('Python · Step 1: Hello World',         'python/03_1python.html', 'python', 'basic'),
('Python · Step 2: Variables & print()', 'python/03_2python.html', 'python', 'basic'),

-- =============================
-- PYTHON · ADVANCED COURSE
-- =============================
('Python · Step 3: If Statements',       'python/03_3if.html',     'python', 'advanced'),
('Python · Step 4: Loops',               'python/03_4loops.html',  'python', 'advanced');
