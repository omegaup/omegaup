-- Drop User_Rank table
DROP TABLE `User_Rank`;

-- Create User_Rank table wih timestamp, it's because we have faced some issues
-- with the script when we create the column timestamp in the table
CREATE TABLE `User_Rank` (
  `user_id` int NOT NULL,
  `ranking` int DEFAULT NULL,
  `problems_solved_count` int NOT NULL DEFAULT '0',
  `score` double NOT NULL DEFAULT '0',
  `username` varchar(50) NOT NULL,
  `name` varchar(256) DEFAULT NULL,
  `country_id` char(3) DEFAULT NULL,
  `state_id` char(3) DEFAULT NULL,
  `school_id` int DEFAULT NULL,
  `author_score` double NOT NULL DEFAULT '0',
  `author_ranking` int DEFAULT NULL,
  `classname` varchar(50) DEFAULT NULL COMMENT 'Almacena la clase precalculada para no tener que determinarla en tiempo de ejecucion.',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Almacena la hora y fecha en que se actualiza el rank de usuario',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`),
  KEY `rank` (`ranking`),
  KEY `fk_ur_state_id` (`country_id`,`state_id`),
  KEY `fk_ur_school_id` (`school_id`),
  CONSTRAINT `fk_ur_country_id` FOREIGN KEY (`country_id`) REFERENCES `Countries` (`country_id`),
  CONSTRAINT `fk_ur_school_id` FOREIGN KEY (`school_id`) REFERENCES `Schools` (`school_id`),
  CONSTRAINT `fk_ur_state_id` FOREIGN KEY (`country_id`, `state_id`) REFERENCES `States` (`country_id`, `state_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Guarda el ranking de usuarios por problemas resueltos.';

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
