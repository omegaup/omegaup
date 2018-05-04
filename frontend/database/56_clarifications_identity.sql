-- Dropping the foreign key
ALTER TABLE `Clarifications`
  DROP FOREIGN KEY `fk_cu_author_id`;

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
  ADD CONSTRAINT `fk_ci_author_id` FOREIGN KEY (`author_id`) REFERENCES `Identities` (`identity_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
