-- User_Rank table
ALTER TABLE `User_Rank`
ADD COLUMN IF NOT EXISTS `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Almacena la hora y fecha en que se actualiza el rank de usuario';

-- Add School_Rank table
CREATE TABLE `School_Rank` (
  `school_id` int NOT NULL,
  `ranking` int DEFAULT NULL,
  `score` double NOT NULL DEFAULT '0',
  `country_id` char(3) DEFAULT NULL,
  `state_id` char(3) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Almacena la hora y fecha en que se actualizó el rank de la escuela',
  PRIMARY KEY (`school_id`),
  KEY `rank` (`ranking`),
  KEY `fk_sr_state_id` (`country_id`,`state_id`),
  CONSTRAINT `fk_sr_country_id` FOREIGN KEY (`country_id`) REFERENCES `Countries` (`country_id`),
  CONSTRAINT `fk_sr_state_id` FOREIGN KEY (`country_id`, `state_id`) REFERENCES `States` (`country_id`, `state_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Guarda el ranking de escuelas de acuerdo a su puntaje.';
