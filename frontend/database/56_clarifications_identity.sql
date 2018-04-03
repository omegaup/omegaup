/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;

-- Updating author_id to point identities instead of users
UPDATE
  `Clarifications`
INNER JOIN
  `Identities`
ON
  `Identities`.`user_id` = `Clarifications`.`author_id`
SET
  `author_id` = `Identities`.`identity_id`;

-- Replacing constraints
ALTER TABLE `Clarifications`
  DROP FOREIGN KEY `fk_cu_author_id`,
  ADD CONSTRAINT `fk_ci_author_id` FOREIGN KEY (`author_id`) REFERENCES `Identities` (`identity_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
