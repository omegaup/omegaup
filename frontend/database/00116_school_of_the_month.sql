CREATE TABLE `School_Of_The_Month` (
  `school_of_the_month_id` int(11) NOT NULL AUTO_INCREMENT,
  `school_id` int(11) NOT NULL,
  `time` date NOT NULL DEFAULT '2000-01-01',
  `rank` int(11) NOT NULL COMMENT 'El lugar que tuvo la escuela en el mes.',
  `selected_by` int(11) DEFAULT NULL COMMENT 'Identidad que seleccionó a la escuela.',
  PRIMARY KEY (`school_of_the_month_id`),
  KEY `school_of_the_month_id` (`school_of_the_month_id`),
  KEY `school_id` (`school_id`),
  KEY `selected_by` (`selected_by`),
  CONSTRAINT `fk_sotms_school_id` FOREIGN KEY (`school_id`) REFERENCES `Schools` (`school_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_sotmi_identity_id` FOREIGN KEY (`selected_by`) REFERENCES `Identities` (`identity_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Escuelas del Mes';