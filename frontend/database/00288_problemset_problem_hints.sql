CREATE TABLE `Problemset_Problem_Hints` (
  `hint_id` int NOT NULL AUTO_INCREMENT COMMENT 'Identificador único de la pista',
  `problemset_id` int NOT NULL,
  `problem_id` int NOT NULL,
  `position` int NOT NULL DEFAULT '0' COMMENT 'Orden de aparición de la pista dentro del problema',
  `contents` text NOT NULL COMMENT 'Contenido de la pista en Markdown',
  `penalty_percent` int DEFAULT NULL COMMENT 'Porcentaje de penalización al revelar la pista. NULL significa que la pista es gratuita',
  PRIMARY KEY (`hint_id`),
  UNIQUE KEY `uq_problemset_problem_hint_position` (`problemset_id`,`problem_id`,`position`),
  CONSTRAINT `fk_pph_problemset_problem` FOREIGN KEY (`problemset_id`, `problem_id`) REFERENCES `Problemset_Problems` (`problemset_id`, `problem_id`) ON DELETE CASCADE,
  CONSTRAINT `chk_pph_penalty_percent` CHECK (`penalty_percent` IS NULL OR (`penalty_percent` BETWEEN 0 AND 100))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Pistas escritas por docentes para los problemas de cada conjunto';

CREATE TABLE `Problemset_Problem_Hint_Reveals` (
  `reveal_id` int NOT NULL AUTO_INCREMENT COMMENT 'Identificador único del registro de pista revelada',
  `hint_id` int NOT NULL,
  `identity_id` int NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Momento en que el estudiante reveló la pista',
  `penalty_percent` int DEFAULT NULL COMMENT 'Penalización aplicada al momento de revelar la pista. NULL si la pista era gratuita',
  PRIMARY KEY (`reveal_id`),
  UNIQUE KEY `uq_problemset_problem_hint_reveal` (`hint_id`,`identity_id`),
  KEY `idx_problemset_problem_hint_reveal_identity` (`identity_id`),
  CONSTRAINT `fk_pphr_hint` FOREIGN KEY (`hint_id`) REFERENCES `Problemset_Problem_Hints` (`hint_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pphr_identity` FOREIGN KEY (`identity_id`) REFERENCES `Identities` (`identity_id`) ON DELETE CASCADE,
  CONSTRAINT `chk_pphr_penalty_percent` CHECK (`penalty_percent` IS NULL OR (`penalty_percent` BETWEEN 0 AND 100))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Pistas reveladas por cada estudiante';
