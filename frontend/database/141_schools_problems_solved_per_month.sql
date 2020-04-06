CREATE TABLE `Schools_Problems_Solved_Per_Month` (
    `school_pspm_id` int(11) NOT NULL AUTO_INCREMENT,
    `school_id` int(11) NOT NULL COMMENT 'Identificador de escuela',
    `time` date NOT NULL,
    `problems_solved` int NOT NULL,
    PRIMARY KEY (`school_pspm_id`),
    KEY `school_id` (`school_id`),
    CONSTRAINT `fk_spspms_school_id` FOREIGN KEY (`school_id`) REFERENCES `Schools` (`school_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
    UNIQUE KEY `school_time` (`school_id`, `time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='La cantidad de problemas que resolvieron los usuarios de una escuela en un mes y año determinados.';