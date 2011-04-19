INSERT INTO Contests(title, description, start_time, finish_time, window_length, director_id, rerun_id, public, token, scoreboard, partial_score, submissions_gap, feedback, penalty, time_start) VALUES ('ConTest', 'A test contest', '2000-01-01 00:00:00', '2000-01-01 06:00:00', NULL, 1, 0, 1, 'test', 80, 1, 0, 'yes', 20, 'contest');

INSERT INTO Problems(`problem_id`, `public`, `author_id`, `title`, `alias`, `validator`, `server`, `remote_id`, `time_limit`, `memory_limit`, `visits`, `submissions`, `accepted`, `difficulty`, `creation_date`, `source`, `order`) VALUES (1, 1, 1, 'Hello, World!', 'HELLO', 'token-caseless', NULL, NULL, 3000, 64, 0, 0, 0, 0, '2000-01-01 00:00:00', 'own', 'normal');

INSERT INTO Contest_Problems(contest_id, problem_id, points) VALUES(1, 1, 100);

INSERT INTO Contest_Problem_Opened(contest_id, problem_id, user_id, open_time) VALUES(1, 1, 1, '2000-01-01 00:01:00');
