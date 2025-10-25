-- Übersicht der Aufgaben
DELETE FROM tasks;
DELETE FROM sqlite_sequence WHERE name='tasks';

INSERT INTO tasks (title, description, html_file) VALUES
('EV3 - beep & name', 'Pybricks Step 1', '03_01LegoEV3.html'),
('EV3 - square', 'Pybricks Step 2', '03_02LegoEV3_I.html'),
('EV3 - circle', 'Pybricks Step 3', '03_03LegoEV3_I.html'),
('EV3 - eight', 'Pybricks Step 4', '03_04LegoEV3_I.html'),
('EV3 - ultrasonic stop in front of a wall', 'Stoppe 150 mm vor der Wand', '03_05LegoEV3_I.html'),
('EV3 - find the door', 'Ausgang finden mit Gyro', '03_06LegoEV3_I.html');
