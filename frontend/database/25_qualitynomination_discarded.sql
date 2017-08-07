-- QualityNomination_Discarded
CREATE TABLE IF NOT EXISTS `QualityNomination_Discarded` (
  `user_id` int(11) NOT NULL COMMENT 'El usuario que rechazó hacer recomendación',
  `problem_id` int(11) NOT NULL COMMENT 'El problema que fue rechazado para ser recomendado por el usuario',
  PRIMARY KEY (`user_id`, `problem_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='La lista de problemas rechazados para ser recomendados por cada usuario';

ALTER TABLE `QualityNomination_Discarded`
  ADD CONSTRAINT `fk_qnd_problem_id` FOREIGN KEY (`problem_id`) REFERENCES `Problems` (`problem_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_qnd_user_id` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

