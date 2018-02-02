-- Table QualityNomination_Log
CREATE TABLE `QualityNomination_Log` (
  `qualitynomination_log_id` int(11) NOT NULL AUTO_INCREMENT,
  `qualitynomination_id` int(11) NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int(11) NOT NULL,
  `from_status` enum('open','approved','denied') NOT NULL DEFAULT 'open',
  `to_status` enum('open','approved','denied') NOT NULL DEFAULT 'open',
  `rationale` text,
  PRIMARY KEY (`qualitynomination_log_id`),
  KEY `user_id` (`user_id`),
  KEY `qualitynomination_id` (`qualitynomination_id`),
  CONSTRAINT `fk_qnl_qualitynomination_id` FOREIGN KEY (`qualitynomination_id`) REFERENCES `QualityNominations` (`qualitynomination_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_qnl_user_id` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Bitácora de cambios a nominaciones';
