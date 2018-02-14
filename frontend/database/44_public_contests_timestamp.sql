-- Table Public_Contests
CREATE TABLE `Public_Contests` (
  `public_contest_id` int(11) NOT NULL AUTO_INCREMENT,
  `contest_id` int(11) NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`public_contest_id`),
  KEY `contest_id` (`contest_id`),
  CONSTRAINT `fk_pc_contest_id` FOREIGN KEY (`contest_id`) REFERENCES `Contests` (`contest_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Esta tabla funcionará para poder ordenar los concursos que se vuelven públicos y no se pierdan entre el resto';
