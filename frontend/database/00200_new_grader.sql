ALTER TABLE `Contests`
    ADD COLUMN `score_mode` enum('partial','all_or_nothing','max_per_group') NOT NULL COMMENT 'Indica el tipo de evaluación para el concurso'
