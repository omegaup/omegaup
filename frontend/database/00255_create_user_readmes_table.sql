-- User_Readmes
CREATE TABLE `User_Readmes` (
  `readme_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador único del README',
  `user_id` int(11) NOT NULL COMMENT 'Usuario dueño del README',
  `content` text NOT NULL COMMENT 'Contenido del README en Markdown',
  `is_visible` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Indica si el README es visible (1 = sí, 0 = no)',
  `last_edit_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Última vez que se editó el README',
  `report_count` int(11) NOT NULL DEFAULT 0 COMMENT 'Número de reportes recibidos',
  `is_disabled` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Indica si el README está deshabilitado por exceso de reportes (1 = sí, 0 = no)',
  PRIMARY KEY (`readme_id`),
  UNIQUE KEY `user_id` (`user_id`),
  CONSTRAINT `fk_ure_user_id` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='READMEs de perfil de usuarios';
