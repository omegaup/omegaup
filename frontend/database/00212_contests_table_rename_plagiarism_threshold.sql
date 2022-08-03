ALTER TABLE `Contests`
    ADD COLUMN `check_plagiarism` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Indica si se debe correr el detector de plagios.';
