ALTER TABLE `Teams`
    ADD COLUMN `team_group_id` int NOT NULL COMMENT 'Id del grupo de equipos' AFTER `team_id`,
    ADD KEY `team_group_id` (`team_group_id`),
    ADD CONSTRAINT `fk_ttg_team_group_id` FOREIGN KEY (`team_group_id`) REFERENCES `Team_Groups` (`team_group_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
