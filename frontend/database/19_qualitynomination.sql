-- Problems
DROP INDEX `idx_problems_public` ON Problems;

ALTER TABLE `Problems`
  CHANGE COLUMN `public` `visibility` tinyint(1) NOT NULL DEFAULT '1' COMMENT '-1 banned, 0 private, 1 public, 2 recommended';

CREATE INDEX idx_problems_visibility ON Problems (`visibility`);

-- QualityNominations
CREATE TABLE IF NOT EXISTS `QualityNominations` (
  `qualitynomination_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT 'El usuario que nominó el problema',
  `problem_id` int(11) NOT NULL COMMENT 'El problema que fue nominado',
  `nomination` enum('promotion', 'demotion') NOT NULL DEFAULT 'promotion' COMMENT 'Si se está nominando el problema a promoción o democión',
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de creacion de esta nominación',
  `status` enum('open', 'approved', 'denied') NOT NULL DEFAULT 'open' COMMENT 'El estado de la nominación',
	PRIMARY KEY (`qualitynomination_id`),
  KEY `user_id` (`user_id`),
  KEY `problem_id` (`problem_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='La cola de nominación a promoción / democión de problemas';

ALTER TABLE `QualityNominations`
  ADD CONSTRAINT `fk_qn_user_id` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_qn_problem_id` FOREIGN KEY (`problem_id`) REFERENCES `Problems` (`problem_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

-- QualityNomination_Reviewers
CREATE TABLE IF NOT EXISTS `QualityNomination_Reviewers` (
  `qualitynomination_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT 'El revisor al que fue asignado esta nominación',
	PRIMARY KEY (`qualitynomination_id`, `user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='La lista de revisores para cada nominación';

ALTER TABLE `QualityNomination_Reviewers`
  ADD CONSTRAINT `fk_qnr_qualitynomination_id` FOREIGN KEY (`qualitynomination_id`) REFERENCES `QualityNominations` (`qualitynomination_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_qnr_user_id` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

-- QualityNomination_Comments
CREATE TABLE IF NOT EXISTS `QualityNomination_Comments` (
  `qualitynomination_comment_id` int(11) NOT NULL AUTO_INCREMENT,
  `qualitynomination_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL COMMENT 'El usuario que emitió el comentario',
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de creacion de esta nominación',
  `vote` tinyint(1) NOT NULL COMMENT 'El voto emitido en este comentario. En el rango de [-2, +2]',
  `contents` TEXT NOT NULL COMMENT 'El contenido de la nominación',
	PRIMARY KEY (`qualitynomination_comment_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Comentarios para una nominación';

ALTER TABLE `QualityNomination_Comments`
  ADD CONSTRAINT `fk_qnc_qualitynomination_id` FOREIGN KEY (`qualitynomination_id`) REFERENCES `QualityNominations` (`qualitynomination_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_qnc_user_id` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
