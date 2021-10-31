-- API_Tokens
CREATE TABLE `API_Tokens` (
  `apitoken_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Momento de creación del token',
  `name` varchar(100) NOT NULL COMMENT 'Nombre que el usuario le asigna al token',
  `token` char(40) NOT NULL COMMENT 'Contenido del token',
  `last_used` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Momento de último uso del token, redondeado a la última hora',
  `use_count` int(11) NOT NULL DEFAULT 0 COMMENT 'Número de usos desde la última hora',
  PRIMARY KEY (`apitoken_id`),
  UNIQUE KEY `token` (`token`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_atu_user_id` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Tokens para el API';
