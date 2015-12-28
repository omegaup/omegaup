CREATE TABLE `User_Login_Log` (
	`user_id` int(11) NOT NULL,
	`ip` int UNSIGNED NOT NULL,
	`time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Bitácora de inicios de sesión exitosos';

CREATE TABLE `Contest_Access_Log` (
	`contest_id` int(11) NOT NULL,
	`user_id` int(11) NOT NULL,
	`ip` int UNSIGNED NOT NULL,
	`time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	KEY `contest_id` (`contest_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Bitácora de acceso a concursos';

CREATE TABLE `Submission_Log` (
	`contest_id` int(11) NULL DEFAULT NULL,
	`run_id` int(11) NOT NULL,
	`user_id` int(11) NOT NULL,
	`ip` int UNSIGNED NOT NULL,
	`time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`run_id`),
	KEY `contest_id` (`contest_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Bitácora de envíos';

ALTER TABLE `Contest_Access_Log`
  ADD CONSTRAINT `fk_calc_contest_id` FOREIGN KEY (`contest_id`) REFERENCES `Contests` (`contest_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_calu_user_id` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE `User_Login_Log`
  ADD CONSTRAINT `fk_ullu_user_id` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE `Submission_Log`
  ADD CONSTRAINT `fk_slc_contest_id` FOREIGN KEY (`contest_id`) REFERENCES `Contests` (`contest_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_slr_run_id` FOREIGN KEY (`run_id`) REFERENCES `Runs` (`run_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_slu_user_id` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

INSERT INTO Submission_Log (contest_id, run_id, user_id, ip, time) SELECT contest_id, run_id, user_id, INET_ATON(ip) AS ip, `time` FROM Runs;

ALTER TABLE Runs DROP COLUMN ip;
