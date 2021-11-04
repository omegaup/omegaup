CREATE TABLE `Teams_Group_Roles` (
  `team_group_id` int NOT NULL,
  `role_id` int NOT NULL,
  `acl_id` int NOT NULL,
  PRIMARY KEY (`team_group_id`,`role_id`,`acl_id`),
  KEY `team_group_id` (`team_group_id`),
  KEY `role_id` (`role_id`),
  KEY `acl_id` (`acl_id`),
  CONSTRAINT `fk_tgra_acl_id` FOREIGN KEY (`acl_id`) REFERENCES `ACLs` (`acl_id`),
  CONSTRAINT `fk_tgrg_team_group_id` FOREIGN KEY (`team_group_id`) REFERENCES `Team_Groups` (`team_group_id`),
  CONSTRAINT `fk_tgrr_role_id` FOREIGN KEY (`role_id`) REFERENCES `Roles` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Establece los roles que se pueden dar a los grupos de equipos.';