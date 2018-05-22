-- Adding identity_id column on Problem_Viewed and synchronizing user - identity data
ALTER TABLE `Problem_Viewed`
  ADD COLUMN `identity_id` int(11) DEFAULT NULL COMMENT 'Identidad del usuario' AFTER `user_id`,
  DROP FOREIGN KEY `fk_pv_user_id`,
  DROP KEY `user_id`,
  DROP PRIMARY KEY;

UPDATE
  `Problem_Viewed`
INNER JOIN
  `Identities`
ON
  `Identities`.`user_id` = `Problem_Viewed`.`user_id`
SET
  `Problem_Viewed`.`identity_id` = `Identities`.`identity_id`;

-- Deleting user_id column and creating indexes on Problem_Viewed
ALTER TABLE `Problem_Viewed`
  MODIFY COLUMN `identity_id` int(11) NOT NULL COMMENT 'Identidad del usuario',
  ADD KEY `identity_id` (`identity_id`),
  ADD CONSTRAINT `fk_pvi_identity_id` FOREIGN KEY (`identity_id`) REFERENCES `Identities` (`identity_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD PRIMARY KEY (`problem_id`,`identity_id`),
  DROP COLUMN `user_id`;

-- Adding identity_id column on Problemset_Access_Log and synchronizing user - identity data
ALTER TABLE `Problemset_Access_Log`
  ADD COLUMN `identity_id` int(11) DEFAULT NULL COMMENT 'Identidad del usuario' AFTER `user_id`,
  DROP FOREIGN KEY `fk_palu_user_id`,
  DROP KEY `fk_palu_user_id`;

UPDATE
  `Problemset_Access_Log`
INNER JOIN
  `Identities`
ON
  `Identities`.`user_id` = `Problemset_Access_Log`.`user_id`
SET
  `Problemset_Access_Log`.`identity_id` = `Identities`.`identity_id`;

-- Deleting user_id column and creating indexes on Problemset_Access_Log
ALTER TABLE `Problemset_Access_Log`
  MODIFY COLUMN `identity_id` int(11) NOT NULL COMMENT 'Identidad del usuario',
  ADD KEY `identity_id` (`identity_id`),
  ADD CONSTRAINT `fk_pali_identity_id` FOREIGN KEY (`identity_id`) REFERENCES `Identities` (`identity_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  DROP COLUMN `user_id`;

-- Adding identity_id column on Problemset_Problem_Opened and synchronizing user - identity data
ALTER TABLE `Problemset_Problem_Opened`
  ADD COLUMN `identity_id` int(11) DEFAULT NULL COMMENT 'Identidad del usuario' AFTER `user_id`,
  DROP FOREIGN KEY `fk_ppo_user_id`,
  DROP KEY `user_id`,
  DROP PRIMARY KEY;

UPDATE
  `Problemset_Problem_Opened`
INNER JOIN
  `Identities`
ON
  `Identities`.`user_id` = `Problemset_Problem_Opened`.`user_id`
SET
  `Problemset_Problem_Opened`.`identity_id` = `Identities`.`identity_id`;

-- Deleting user_id column and creating indexes on Problemset_Problem_Opened
ALTER TABLE `Problemset_Problem_Opened`
  MODIFY COLUMN `identity_id` int(11) NOT NULL COMMENT 'Identidad del usuario',
  ADD KEY `identity_id` (`identity_id`),
  ADD CONSTRAINT `fk_ppoi_identity_id` FOREIGN KEY (`identity_id`) REFERENCES `Identities` (`identity_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD PRIMARY KEY (`problemset_id`,`problem_id`,`identity_id`),
  DROP COLUMN `user_id`;
