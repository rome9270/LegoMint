-- Übersicht der Aufgaben
DELETE FROM tasks;
DELETE FROM sqlite_sequence WHERE name='tasks';

INSERT INTO tasks (title, html_file) VALUES
('EV3 - beep & name', '03_01LegoEV3_I.html'),
('EV3 - drive a square', '03_02LegoEV3_I.html'),
('EV3 - drive a circle', '03_03LegoEV3_I.html'),
('EV3 - drive a figure 8', '03_04LegoEV3_I.html'),
('EV3 - stop in front of a wall', '03_05LegoEV3_I.html'),
('EV3 - find the door', '03_06LegoEV3_I.html'),
('EV3 - eight', '03_07LegoEV3_I.html'),
('EV3 - eight', '03_08LegoEV3_I.html'),
('EV3 - eight', '03_09LegoEV3_I.html'),
('EV3 - line follower', '03_10LegoEV3_I.html'),
('EV3 - pick up line follower', '04_01LegoEV3_I.html'),
('EV3 - eight','04_02LegoEV3_I.html');