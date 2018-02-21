-- Table Identity

CREATE TABLE IF NOT EXISTS `Identities` (
  `identity_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL UNIQUE,
  `password` varchar(100) DEFAULT NULL,
  `name` varchar(256) DEFAULT NULL,
  `main_user_id` int(11) DEFAULT NULL,
  `language_id` int(11) DEFAULT NULL,
  `country_id` char(3) DEFAULT NULL,
  `state_id` char(3) DEFAULT NULL,
  `school_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`identity_id`),
  KEY `country_id` (`country_id`),
  KEY `state_id` (`state_id`),
  KEY `school_id` (`school_id`),
  KEY `user_id` (`main_user_id`),
  CONSTRAINT `fk_i_country_id` FOREIGN KEY (`country_id`) REFERENCES `Countries` (`country_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_i_state_id` FOREIGN KEY (`country_id`, `state_id`) REFERENCES `States` (`country_id`, `state_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_i_school_id` FOREIGN KEY (`school_id`) REFERENCES `Schools` (`school_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_i_user_id` FOREIGN KEY (`main_user_id`) REFERENCES `Users` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Identidades registradas.' AUTO_INCREMENT=1 ;

-- Syncronize with Users table

INSERT INTO `Identities` (`identity_id`,`username`,`password`,`name`,`main_user_id`,`language_id`,`country_id`,`state_id`,`school_id`)
  SELECT `user_id`,CONCAT('default:', `username`),`password`,`name`,`user_id`,`language_id`,`country_id`,`state_id`,`school_id` FROM `Users`;