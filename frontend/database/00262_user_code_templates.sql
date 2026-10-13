-- User_Code_Templates
CREATE TABLE IF NOT EXISTS `User_Code_Templates` (
  `template_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL COMMENT 'Identificador del usuario',
  `language` varchar(70) NOT NULL COMMENT 'Lenguaje de programación del template',
  `template_name` varchar(100) NOT NULL COMMENT 'Nombre del template definido por el usuario',
  `code` mediumtext NOT NULL COMMENT 'Código del template',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de creación del template',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Fecha de última actualización',
  PRIMARY KEY (`template_id`),
  UNIQUE KEY `user_language_name` (`user_id`, `language`, `template_name`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_language` (`language`),
  CONSTRAINT `fk_uct_user_id` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Templates de código personalizados por usuario y lenguaje';
