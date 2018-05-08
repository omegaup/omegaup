-- Adding identity_id column on Auth_Tokens table

ALTER TABLE `Auth_Tokens`
  ADD COLUMN `identity_id` int(11) DEFAULT NULL COMMENT 'Identidad del usuario' AFTER `user_id`,
  DROP FOREIGN KEY `fk_atu_user_id`,
  DROP KEY `user_id`;

-- Updating Auth_Tokens table

UPDATE
  `Auth_Tokens`
INNER JOIN
  `Identities`
ON
  Identities.user_id = Auth_Tokens.user_id
SET
  `Auth_Tokens`.`identity_id` = Identities.identity_id;

-- Setting identity_id as NOT NULL and adding contraints

ALTER TABLE `Auth_Tokens`
  MODIFY COLUMN `user_id` int(11) DEFAULT NULL,
  MODIFY COLUMN `identity_id` int(11) NOT NULL COMMENT 'Identidad del usuario',
  ADD KEY `identity_id` (`identity_id`),
  ADD CONSTRAINT `fk_ati_identity_id` FOREIGN KEY (`identity_id`) REFERENCES `Identities` (`identity_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

-- Renaming User_Login_Log to Identity_Login_Log

RENAME TABLE
  `User_Login_Log` TO `Identity_Login_Log`;

-- Replacing user_id by identity_id

ALTER TABLE `Identity_Login_Log`
  ADD COLUMN `identity_id` int(11) DEFAULT NULL COMMENT 'Identidad del usuario' AFTER `user_id`,
  DROP FOREIGN KEY `fk_ullu_user_id`,
  DROP KEY `user_id`;

-- Updating Identity_Login_Log table

UPDATE
  `Identity_Login_Log`
INNER JOIN
  `Identities`
ON
  Identities.user_id = Identity_Login_Log.user_id
SET
  `Identity_Login_Log`.`identity_id` = Identities.identity_id;

-- Setting identity_id as NOT NULL, adding contraints, and removing user information

ALTER TABLE `Identity_Login_Log`
  DROP COLUMN `user_id`,
  MODIFY COLUMN `identity_id` int(11) NOT NULL COMMENT 'Identidad del usuario',
  ADD KEY `identity_id` (`identity_id`),
  ADD CONSTRAINT `fk_illi_identity_id` FOREIGN KEY (`identity_id`) REFERENCES `Identities` (`identity_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
  