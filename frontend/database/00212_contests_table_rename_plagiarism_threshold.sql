ALTER TABLE `Contests`
    CHANGE COLUMN `plagiarism_threshold` `check_plagiarism` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Indica si se debe correr el detector de plagios.';