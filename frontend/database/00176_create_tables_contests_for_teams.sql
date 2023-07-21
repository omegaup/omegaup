-- Create Team_Groups table
CREATE TABLE IF NOT EXISTS `Team_Groups` (
  `team_group_id` int(11) AUTO_INCREMENT NOT NULL,
  `acl_id` int NOT NULL,
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `alias` varchar(50) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`team_group_id`),
  UNIQUE KEY `team_group_alias` (`alias`),
  KEY `acl_id` (`acl_id`),
  CONSTRAINT `fk_tg_acl_id` FOREIGN KEY (`acl_id`) REFERENCES `ACLs` (`acl_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

-- Create Teams table
CREATE TABLE IF NOT EXISTS `Teams` (
  `team_id` int(11) AUTO_INCREMENT NOT NULL,
  `identity_id` int NOT NULL COMMENT 'La identidad asociada al equipo',
  PRIMARY KEY (`team_id`),
  KEY `identity_id` (`identity_id`),
  CONSTRAINT `fk_ti_identity_id` FOREIGN KEY (`identity_id`) REFERENCES `Identities` (`identity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;

-- Create Team_Users table
CREATE TABLE IF NOT EXISTS `Team_Users` (
  `team_id` int(11) NOT NULL,
  `user_id` int NOT NULL,
  PRIMARY KEY (`team_id`, `user_id`),
  KEY `team_id` (`team_id`),
  CONSTRAINT `fk_tut_team_id` FOREIGN KEY (`team_id`) REFERENCES `Teams` (`team_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_tuu_user_id` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Modify Contests table, added contest_for_teams column
ALTER TABLE `Contests`
    ADD COLUMN `contest_for_teams` tinyint(1) DEFAULT 0
        COMMENT 'Bandera que indica si el concurso es para equipos.';

