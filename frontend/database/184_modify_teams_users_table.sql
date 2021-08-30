-- Modify Team_Users table
ALTER TABLE `Team_Users`
  DROP PRIMARY KEY,
  DROP KEY `user_id`,
  DROP FOREIGN KEY `fk_tuu_user_id`,
  DROP COLUMN `user_id`,
  ADD COLUMN `identity_id` int NOT NULL COMMENT 'Id de la identidad que pertenece al equipo',
  ADD PRIMARY KEY (`team_id`, `identity_id`),
  ADD KEY `identity_id` (`identity_id`),
  ADD CONSTRAINT `fk_tui_identity_id` FOREIGN KEY (`identity_id`) REFERENCES `Identities` (`identity_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
