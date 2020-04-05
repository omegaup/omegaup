-- Table Identity

CREATE TABLE IF NOT EXISTS `Identities` (
  `identity_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL UNIQUE,
  `password` varchar(100) DEFAULT NULL,
  `name` varchar(256) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `language_id` int(11) DEFAULT NULL,
  `country_id` char(3) DEFAULT NULL,
  `state_id` char(3) DEFAULT NULL,
  `school_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`identity_id`),
  KEY `country_id` (`country_id`),
  KEY `state_id` (`state_id`),
  KEY `school_id` (`school_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_ic_country_id` FOREIGN KEY (`country_id`) REFERENCES `Countries` (`country_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_is_state_id` FOREIGN KEY (`country_id`, `state_id`) REFERENCES `States` (`country_id`, `state_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_is_school_id` FOREIGN KEY (`school_id`) REFERENCES `Schools` (`school_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_iu_user_id` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Identidades registradas.' AUTO_INCREMENT=1 ;

-- Table Users

ALTER TABLE `Users`
  ADD COLUMN `main_identity_id` int(11) DEFAULT NULL COMMENT 'Identidad principal del usuario' AFTER `main_email_id`,
  ADD KEY `fk_main_identity_id` (`main_identity_id`),
  ADD CONSTRAINT `fk_main_identity_id` FOREIGN KEY (`main_identity_id`) REFERENCES `Identities` (`identity_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

-- Syncronize with Users table
START TRANSACTION;

INSERT INTO `Identities` (
  `identity_id`,
  `username`,
  `password`,
  `name`,
  `user_id`,
  `language_id`,
  `country_id`,
  `state_id`,
  `school_id`
)
SELECT
  `user_id`,
  `username`,
  `password`,
  `name`,
  `user_id`,
  `language_id`,
  `country_id`,
  `state_id`,
  `school_id`
FROM
  `Users`;

UPDATE
  `Users`
JOIN
  `Identities`
ON
  Users.username = Identities.username
SET
  `main_identity_id` = Identities.identity_id;

-- Everything is ok
COMMIT;
