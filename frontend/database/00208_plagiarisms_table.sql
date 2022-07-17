CREATE TABLE IF NOT EXISTS `Plagiarisms` (
    `plagiarism_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'El identificador único para cada potencial caso de plagio',
    `contest_id` int(11) NOT NULL,
    `submission_id_1` int(11) NOT NULL COMMENT 'El identificador del envío del primer código plagiado',
    `submission_id_2` int(11) NOT NULL COMMENT 'El identificador del envío del segundo código plagiado',
    `contents` TEXT NOT NULL COMMENT 'Almacena los puntajes de similitud de los envíos y el rango de números de línea de esos puntajes',
    PRIMARY KEY (`plagiarisms_id`),
    CONSTRAINT `fk_pc_contest_id` FOREIGN KEY (`contest_id`) REFERENCES `Contests` (`contest_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
    CONSTRAINT `fk_ps_submission_id_1` FOREIGN KEY (`submission_id_1`) REFERENCES `Submissions` (`submission_id`) ON DELETE NO ACTION ON UPDATE NO ACTION, 
    CONSTRAINT `fk_pg_guid_2` FOREIGN KEY (`guid_2`) REFERENCES `Runs` (`guid`) ON DELETE NO ACTION ON UPDATE NO ACTION
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='La lista de Códigos Plagiados para un concurso';

