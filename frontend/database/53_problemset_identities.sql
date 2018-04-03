-- Rename tables

RENAME TABLE
  `Problemset_Users` TO `Problemset_Identities`,
  `Problemset_User_Request` TO `Problemset_Identity_Request`,
  `Problemset_User_Request_History` TO `Problemset_Identity_Request_History`;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;

-- Creating column on Problemset_Identities and synchronizing user - identity data
ALTER TABLE `Problemset_Identities`
  ADD COLUMN `identity_id` int(11) DEFAULT NULL COMMENT 'Identidad del usuario' AFTER `user_id`,
  ADD KEY `identity_id` (`identity_id`),
  DROP FOREIGN KEY `fk_puu_user_id`,
  DROP KEY `user_id`,
  DROP PRIMARY KEY;

UPDATE
  `Problemset_Identities`
INNER JOIN
  `Identities`
ON
  Identities.user_id = Problemset_Identities.user_id
SET
  `Problemset_Identities`.`identity_id` = Identities.identity_id;

-- Deleting user_id column and adding constraints
ALTER TABLE `Problemset_Identities`
  MODIFY COLUMN `identity_id` int(11) NOT NULL COMMENT 'Identidad del usuario',
  ADD CONSTRAINT `fk_pii_identity_id` FOREIGN KEY (`identity_id`) REFERENCES `Identities` (`identity_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD PRIMARY KEY (`identity_id`, `problemset_id`),
  DROP COLUMN `user_id`;

-- Creating column on Problemset_Identity_Request and synchronizing user - identity data
ALTER TABLE `Problemset_Identity_Request`
  ADD COLUMN `identity_id` int(11) DEFAULT NULL COMMENT 'Identidad del usuario' AFTER `user_id`,
  ADD KEY `identity_id` (`identity_id`),
  DROP FOREIGN KEY `fk_puru_user_id`,
  DROP PRIMARY KEY;

UPDATE
  `Problemset_Identity_Request`
INNER JOIN
  `Identities`
ON
  Identities.user_id = Problemset_Identity_Request.user_id
SET
  `Problemset_Identity_Request`.`identity_id` = Identities.identity_id;

-- Deleting user_id column and adding constraints
ALTER TABLE `Problemset_Identity_Request`
  MODIFY COLUMN `identity_id` int(11) NOT NULL COMMENT 'Identidad del usuario',
  ADD CONSTRAINT `fk_piri_identity_id` FOREIGN KEY (`identity_id`) REFERENCES `Identities` (`identity_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD PRIMARY KEY (`identity_id`, `problemset_id`),
  DROP COLUMN `user_id`;

-- Creating indexes on Problemset_Identity_Request_History and synchronizing user - identity data
ALTER TABLE `Problemset_Identity_Request_History`
  ADD COLUMN `identity_id` int(11) DEFAULT NULL COMMENT 'Identidad del usuario' AFTER `user_id`,
  DROP FOREIGN KEY `fk_purhu_user_id`,
  DROP KEY `user_problemset_hist`;

UPDATE
  `Problemset_Identity_Request_History`
INNER JOIN
  `Identities`
ON
  Identities.user_id = Problemset_Identity_Request_History.user_id
SET
  `Problemset_Identity_Request_History`.`identity_id` = Identities.identity_id;

-- Deleting user_id column
ALTER TABLE `Problemset_Identity_Request_History`
  MODIFY COLUMN `identity_id` int(11) NOT NULL COMMENT 'Identidad del usuario',
  ADD KEY `identity_problemset_hist` (`identity_id`, `problemset_id`),
  ADD CONSTRAINT `fk_pirhi_identity_id` FOREIGN KEY (`identity_id`) REFERENCES `Identities` (`identity_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  DROP COLUMN `user_id`;

/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;