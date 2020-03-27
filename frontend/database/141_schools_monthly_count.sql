CREATE TABLE `Schools_Monthly_Count` (
    `school_monthly_count_id` int(11) NOT NULL AUTO_INCREMENT,
    `school_id` int(11) NOT NULL COMMENT 'Identificador de escuela',
    `year` smallint NOT NULL,
    `month` smallint NOT NULL,
    `count` int NOT NULL,
    PRIMARY KEY (`school_monthly_count_id`),
    KEY `school_id` (`school_id`),
    CONSTRAINT `fk_smsps_school_id` FOREIGN KEY (`school_id`) REFERENCES `Schools` (`school_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
    UNIQUE KEY `school_month_year` (`school_id`, `month`, `year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='La cantidad de problemas que resolvieron los usuarios de una escuela en un mes y año determinados.';