CREATE TABLE `Problemset_Problem_Hints` (
  `hint_id` int NOT NULL AUTO_INCREMENT COMMENT 'Identificador único de la pista',
  `problemset_id` int NOT NULL,
  `problem_id` int NOT NULL,
  `position` int NOT NULL DEFAULT '0' COMMENT 'Orden de aparición de la pista dentro del problema',
  `contents` text NOT NULL COMMENT 'Contenido de la pista en Markdown',
  `penalty_percent` int DEFAULT NULL COMMENT 'Porcentaje de penalización al revelar la pista. NULL significa que la pista es gratuita',
  PRIMARY KEY (`hint_id`),
  KEY `idx_problemset_problem_hints` (`problemset_id`,`problem_id`,`position`),
  CONSTRAINT `fk_pph_problemset_problem` FOREIGN KEY (`problemset_id`, `problem_id`) REFERENCES `Problemset_Problems` (`problemset_id`, `problem_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Pistas escritas por docentes para los problemas de cada conjunto';
