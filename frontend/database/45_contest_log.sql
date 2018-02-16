-- Table Contest_Log

CREATE TABLE `Contest_Log` (
  `public_contest_id` int(11) NOT NULL AUTO_INCREMENT,
  `contest_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `from_visibility` tinyint(1) NOT NULL DEFAULT '0',
  `to_visibility` tinyint(1) NOT NULL DEFAULT '1',
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`public_contest_id`),
  KEY `contest_id` (`contest_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_cl_contest_id` FOREIGN KEY (`contest_id`) REFERENCES `Contests` (`contest_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_cl_user_id` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Esta tabla funcionará para poder ordenar los concursos que se vuelven públicos y no se pierdan entre el resto';
