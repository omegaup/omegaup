-- Users Badges
ALTER TABLE `Users_Badges`
  DROP KEY `badge_id`,
  DROP KEY `user_id`,
  DROP KEY `last_problem_id`,
  DROP FOREIGN KEY `fk_ub_badge_id`,
  DROP FOREIGN KEY `fk_ub_last_problem_id`,
  DROP FOREIGN KEY `fk_ub_user_id`;

DROP TABLE `Users_Badges`;

ALTER TABLE `Problems_Badges`
  DROP KEY `badge_id`,
  DROP KEY `problem_id`,
  DROP FOREIGN KEY `fk_pb_badge_id`,
  DROP FOREIGN KEY `fk_pb_problem_id`;

DROP TABLE `Problems_Badges`;

DROP TABLE `Badges`;

CREATE TABLE `Users_Badges` (
  `user_badge_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT 'Identificador de usuario',
  `badge_alias` varchar(32) NOT NULL COMMENT 'Identificador de badge',
  `assignation_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_badge_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_ubu_user_id` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Badges de Usuario';

-- Notifications
CREATE TABLE `Notifications` (
  `notification_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT 'Identificador de usuario',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `read` tinyint(1) NOT NULL DEFAULT FALSE,
  `contents` TEXT NOT NULL COMMENT 'JSON con el contenido de la notificación',
  PRIMARY KEY (`notification_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_nu_user_id` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Notificaciones';