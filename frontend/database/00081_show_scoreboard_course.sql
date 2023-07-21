-- Courses
ALTER TABLE `Courses`
    ADD COLUMN `show_scoreboard` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Los estudiantes pueden visualizar el scoreboard de un curso.';
