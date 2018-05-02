-- Adding identity_id column on Runs and synchronizing user - identity data
ALTER TABLE `Runs`
  ADD COLUMN `identity_id` int(11) DEFAULT NULL COMMENT 'Identidad del usuario' AFTER `user_id`,
  DROP FOREIGN KEY `fk_r_user_id`,
  DROP KEY `user_id`;

UPDATE
  `Runs`
INNER JOIN
  `Identities`
ON
  `Identities`.`user_id` = `Runs`.`user_id`
SET
  `Runs`.`identity_id` = `Identities`.`identity_id`;

-- Deleting user_id column and creating indexes
ALTER TABLE `Runs`
  MODIFY COLUMN `identity_id` int(11) NOT NULL COMMENT 'Identidad del usuario',
  ADD KEY `identity_id` (`identity_id`),
  ADD CONSTRAINT `fk_r_identity_id` FOREIGN KEY (`identity_id`) REFERENCES `Identities` (`identity_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  DROP COLUMN `user_id`;
