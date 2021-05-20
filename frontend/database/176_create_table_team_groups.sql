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
