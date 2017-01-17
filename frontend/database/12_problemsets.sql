-- Problemsets
INSERT IGNORE INTO `Problemsets` (`problemset_id`)
	SELECT `contest_id` AS `problemset_id` FROM `Contests`;

-- Problemset_Problems
TRUNCATE TABLE `Problemset_Problems`;

INSERT INTO `Problemset_Problems` (`problemset_id`, `problem_id`, `points`, `order`)
	SELECT
		`contest_id` AS `problemset_id`,
		`problem_id`,
		`points`,
		`order`
	FROM `Contest_Problems`;

ALTER TABLE `Problemset_Problems`
	MODIFY COLUMN `order` INT NOT NULL DEFAULT '1' COMMENT 'Define el orden de aparición de los problemas en una lista de problemas';

ALTER TABLE `Contest_Problems`
	DROP KEY `contest_id`,
	DROP KEY `problem_id`,
	DROP FOREIGN KEY `fk_cpc_contest_id`,
	DROP FOREIGN KEY `fk_cpp_problem_id`;

DROP TABLE `Contest_Problems`;

-- Clarifications
ALTER TABLE `Clarifications`
	DROP FOREIGN KEY `fk_cp_contest_id`,
	DROP KEY `contest_id`;

ALTER TABLE `Clarifications`
	CHANGE COLUMN `contest_id` `problemset_id` int(11) NOT NULL;

ALTER TABLE `Clarifications`
	ADD KEY `problemset_id` (`problemset_id`),
	ADD CONSTRAINT `fk_cp_problemset_id` FOREIGN KEY (`problemset_id`) REFERENCES `Problemsets` (`problemset_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

-- Contests
ALTER TABLE `Contests`
	ADD COLUMN `problemset_id` int(11) NOT NULL COMMENT 'La lista de problemas de este concurso' AFTER `contest_id`;

UPDATE `Contests` SET `problemset_id` = `contest_id`;

ALTER TABLE `Contests`
	ADD CONSTRAINT `fk_cop_problemset_id` FOREIGN KEY (`problemset_id`) REFERENCES `Problemsets` (`problemset_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

-- Interviews
ALTER TABLE `Interviews`
	DROP PRIMARY KEY;

ALTER TABLE `Interviews`
	ADD COLUMN `interview_id` int(11) NOT NULL AUTO_INCREMENT FIRST,
	ADD PRIMARY KEY (`interview_id`),
	CHANGE COLUMN `contest_id` `problemset_id` int(11) NOT NULL,
	ADD COLUMN `acl_id` int(11) NOT NULL COMMENT 'La lista de control de acceso del problema',
  ADD COLUMN `alias` varchar(32) NOT NULL COMMENT 'El alias de la entrevista',
	ADD COLUMN `title` varchar(256) NOT NULL COMMENT 'El titulo de la entrevista.',
	ADD COLUMN `description` tinytext NOT NULL COMMENT 'Una breve descripcion de la entrevista.',
	ADD COLUMN `window_length` int(11) NOT NULL COMMENT 'Indica el tiempo que tiene el usuario para envíar soluciones.';

ALTER TABLE `Interviews`
	ADD KEY `problemset_id` (`problemset_id`),
	ADD KEY `acl_id` (`acl_id`);

-- Problemset_Access_Log
ALTER TABLE `Contest_Access_Log`
	DROP KEY `contest_id`,
	DROP FOREIGN KEY `fk_calc_contest_id`,
	DROP FOREIGN KEY `fk_calu_user_id`;

ALTER TABLE `Contest_Access_Log`
	CHANGE COLUMN `contest_id` `problemset_id` int(11) NOT NULL;

ALTER TABLE `Contest_Access_Log`
	ADD KEY `problemset_id` (`problemset_id`),
	ADD CONSTRAINT `fk_palc_problemset_id` FOREIGN KEY (`problemset_id`) REFERENCES `Problemsets` (`problemset_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
	ADD CONSTRAINT `fk_palu_user_id` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
	RENAME TO `Problemset_Access_Log`,
	COMMENT='Bitácora de acceso a listas de problemas';

-- Problemset_Problem_Opened
ALTER TABLE `Contest_Problem_Opened`
	DROP PRIMARY KEY,
	DROP KEY `contest_id`,
	DROP FOREIGN KEY `fk_cpo_contest_id`,
	DROP FOREIGN KEY `fk_cpo_problem_id`,
	DROP FOREIGN KEY `fk_cpo_user_id`;

ALTER TABLE `Contest_Problem_Opened`
	CHANGE COLUMN `contest_id` `problemset_id` int(11) NOT NULL;

ALTER TABLE `Contest_Problem_Opened`
	ADD PRIMARY KEY (`problemset_id`,`problem_id`,`user_id`),
	ADD KEY `problemset_id` (`problemset_id`),
	ADD CONSTRAINT `fk_ppo_problemset_id` FOREIGN KEY (`problemset_id`) REFERENCES `Problemsets` (`problemset_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
	ADD CONSTRAINT `fk_ppo_problem_id` FOREIGN KEY (`problem_id`) REFERENCES `Problems` (`problem_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
	ADD CONSTRAINT `fk_ppo_user_id` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
	RENAME TO `Problemset_Problem_Opened`,
	COMMENT='Registro de primer acceso a problemas de un conjunto.';

-- Problemset_Users
ALTER TABLE `Contests_Users`
	DROP FOREIGN KEY `fk_cuc_contest_id`,
	DROP FOREIGN KEY `fk_cuu_user_id`,
	DROP PRIMARY KEY,
	DROP KEY `contest_id`;

ALTER TABLE `Contests_Users`
	CHANGE COLUMN `contest_id` `problemset_id` int(11) NOT NULL;

ALTER TABLE `Contests_Users`
	ADD PRIMARY KEY (`user_id`,`problemset_id`),
	ADD KEY `problemset_id` (`problemset_id`),
	ADD CONSTRAINT `fk_puc_problemset_id` FOREIGN KEY (`problemset_id`) REFERENCES `Problemsets` (`problemset_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
	ADD CONSTRAINT `fk_puu_user_id` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
	RENAME TO `Problemset_Users`,
	COMMENT='Concursantes que pueden interactuar con una lista de problemas.';

-- Problemset_User_Request
ALTER TABLE `Contest_User_Request`
	DROP PRIMARY KEY;

ALTER TABLE `Contest_User_Request`
	CHANGE COLUMN `contest_id` `problemset_id` int(11) NOT NULL;

ALTER TABLE `Contest_User_Request`
	ADD PRIMARY KEY (`user_id`, `problemset_id`),
	RENAME TO `Problemset_User_Request`;

-- Problemset_User_Request_History
ALTER TABLE `Contest_User_Request_History`
	DROP KEY `user_contest_hist`;

ALTER TABLE `Contest_User_Request_History`
	CHANGE COLUMN `contest_id` `problemset_id` int(11) NOT NULL;

ALTER TABLE `Contest_User_Request_History`
	ADD KEY `user_problemset_hist` (`user_id`, `problemset_id`),
	RENAME TO `Problemset_User_Request_History`;

-- Runs
ALTER TABLE `Runs`
	DROP FOREIGN KEY `fk_r_contest_id`,
	DROP KEY `contest_id`;

ALTER TABLE `Runs`
	CHANGE COLUMN `contest_id` `problemset_id` int(11) DEFAULT NULL;

ALTER TABLE `Runs`
	ADD KEY `problemset_id` (`problemset_id`),
	ADD CONSTRAINT `fk_r_problemset_id` FOREIGN KEY (`problemset_id`) REFERENCES `Problemsets` (`problemset_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

-- Submission_Log
ALTER TABLE `Submission_Log`
	DROP FOREIGN KEY `fk_slc_contest_id`,
	DROP KEY `contest_id`;

ALTER TABLE `Submission_Log`
	CHANGE COLUMN `contest_id` `problemset_id` int(11) DEFAULT NULL;

ALTER TABLE `Submission_Log`
	ADD KEY `problemset_id` (`problemset_id`),
	ADD CONSTRAINT `fk_slp_problemset_id` FOREIGN KEY (`problemset_id`) REFERENCES `Problemsets` (`problemset_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
