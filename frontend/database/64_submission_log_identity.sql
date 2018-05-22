-- Adding identity_id column on Submission_Log table

ALTER TABLE `Submission_Log`
  ADD COLUMN `identity_id` int(11) DEFAULT NULL COMMENT 'Identidad del usuario' AFTER `user_id`,
  DROP FOREIGN KEY `fk_slu_user_id`,
  DROP KEY `fk_slu_user_id`;

-- Updating Submission_Log table

UPDATE
  `Submission_Log`
INNER JOIN
  `Identities`
ON
  Identities.user_id = Submission_Log.user_id
SET
  `Submission_Log`.`identity_id` = Identities.identity_id;

-- Setting identity_id as NOT NULL and adding contraints

ALTER TABLE `Submission_Log`
  MODIFY COLUMN `user_id` int(11) DEFAULT NULL,
  MODIFY COLUMN `identity_id` int(11) NOT NULL COMMENT 'Identidad del usuario',
  ADD KEY `identity_id` (`identity_id`),
  ADD CONSTRAINT `fk_sli_identity_id` FOREIGN KEY (`identity_id`) REFERENCES `Identities` (`identity_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
