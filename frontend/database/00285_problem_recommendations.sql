CREATE TABLE `Problem_Recommendations` (
  `solved_problem_id` int NOT NULL COMMENT 'El problema resuelto a partir del cual se genera la recomendación',
  `recommended_problem_id` int NOT NULL COMMENT 'El problema recomendado para resolver después',
  `score` double NOT NULL COMMENT 'Peso relativo de esta recomendación frente a las demás del mismo problema resuelto',
  PRIMARY KEY (`solved_problem_id`,`recommended_problem_id`),
  KEY `idx_problem_recommendations_score` (`solved_problem_id`,`score`),
  KEY `recommended_problem_id` (`recommended_problem_id`),
  CONSTRAINT `fk_prec_recommended_problem_id` FOREIGN KEY (`recommended_problem_id`) REFERENCES `Problems` (`problem_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_prec_solved_problem_id` FOREIGN KEY (`solved_problem_id`) REFERENCES `Problems` (`problem_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Las mejores recomendaciones por problema resuelto, sincronizadas desde el último modelo de recomendación publicado';
