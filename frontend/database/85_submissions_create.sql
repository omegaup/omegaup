-- Submissions
CREATE TABLE `Submissions` (
  `submission_id` int(11) NOT NULL AUTO_INCREMENT,
  `current_run_id` int(11) NULL COMMENT 'La evaluación actual del envío',
  `identity_id` int(11) NOT NULL COMMENT 'Identidad del usuario',
  `problem_id` int(11) NOT NULL,
  `problemset_id` int(11) DEFAULT NULL,
  `guid` char(32) NOT NULL,
  `language` enum('c','cpp','java','py','rb','pl','cs','pas','kp','kj','cat','hs','cpp11','lua') NOT NULL,
  `penalty` int(11) NOT NULL DEFAULT '0',
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `submit_delay` int(11) NOT NULL DEFAULT '0',
  `type` enum('normal','test','disqualified') DEFAULT 'normal',
  PRIMARY KEY (`submission_id`),
  UNIQUE KEY `submissions_guid` (`guid`),
  KEY `problem_id` (`problem_id`),
  KEY `problemset_id` (`problemset_id`),
  KEY `identity_id` (`identity_id`),
  CONSTRAINT `fk_s_current_run_id` FOREIGN KEY (`current_run_id`) REFERENCES `Runs` (`run_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_s_identity_id` FOREIGN KEY (`identity_id`) REFERENCES `Identities` (`identity_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_s_problem_id` FOREIGN KEY (`problem_id`) REFERENCES `Problems` (`problem_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_s_problemset_id` FOREIGN KEY (`problemset_id`) REFERENCES `Problemsets` (`problemset_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Envíos';

INSERT IGNORE INTO `Submissions`
	(
		`submission_id`, `current_run_id`, `identity_id`, `problem_id`, `problemset_id`,
		`guid`, `language`, `time`, `submit_delay`, `type`
	)
SELECT
	`run_id` AS `submission_id`, `run_id` AS `current_run_id`, `identity_id`,
	`problem_id`, `problemset_id`, `guid`, `language`, `time`, `submit_delay`,
	`type`
FROM
	`Runs`;

-- Problems
ALTER TABLE `Problems`
	ADD COLUMN `current_version` char(40) NULL COMMENT 'La versión actual del problema.' AFTER `alias`;

-- Problemset_Problems
ALTER TABLE `Problemset_Problems`
	ADD COLUMN `version` char(40) NULL COMMENT 'La versión del problema.' AFTER `problem_id`;

-- Runs
ALTER TABLE `Runs`
	ADD COLUMN `submission_id` int(11) NOT NULL COMMENT 'El envío' AFTER `run_id`,
	ADD COLUMN `version` char(40) NULL COMMENT 'La versión del problema.' AFTER `submission_id`;

ALTER TABLE `Runs`
	DROP KEY `runs_alias`;

UPDATE `Runs` SET `submission_id` = `run_id`;

ALTER TABLE `Runs`
	ADD CONSTRAINT `fk_r_submission_id` FOREIGN KEY (`submission_id`) REFERENCES `Submissions` (`submission_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
	ADD CONSTRAINT UNIQUE KEY `runs_versions` (`submission_id`, `version`);

-- Submission_Log
ALTER TABLE `Submission_Log`
	DROP FOREIGN KEY `fk_slr_run_id`;

ALTER TABLE `Submission_Log`
	CHANGE COLUMN `run_id` `submission_id` int(11) NOT NULL,
  ADD CONSTRAINT `fk_slr_submission_id` FOREIGN KEY (`submission_id`) REFERENCES `Submissions` (`submission_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
