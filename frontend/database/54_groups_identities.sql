-- Rename table

RENAME TABLE
  `Groups_Users` TO `Groups_Identities`;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;

-- Adding identity_id column on Groups_Identities and synchronizing user - identity data
ALTER TABLE `Groups_Identities`
  ADD COLUMN `identity_id` int(11) DEFAULT NULL COMMENT 'Identidad del usuario' AFTER `user_id`,
  ADD KEY `identity_id` (`identity_id`),
  DROP FOREIGN KEY `fk_gu_user_id`,
  DROP KEY `user_id`,
  DROP PRIMARY KEY;

UPDATE
  `Groups_Identities`
INNER JOIN
  `Identities`
ON
  Identities.user_id = Groups_Identities.user_id
SET
  `Groups_Identities`.`identity_id` = Identities.identity_id;

-- Deleting user_id column and creating indexes
ALTER TABLE `Groups_Identities`
  MODIFY COLUMN `identity_id` int(11) NOT NULL COMMENT 'Identidad del usuario',
  ADD CONSTRAINT `fk_gii_identity_id` FOREIGN KEY (`identity_id`) REFERENCES `Identities` (`identity_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD PRIMARY KEY (`identity_id`, `group_id`),
  DROP COLUMN `user_id`;

/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
