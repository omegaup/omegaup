-- Migration: Create Contest_Problem_Change_Log table
-- for efficient storage and retrieval of contest problem change events.

CREATE TABLE `Contest_Problem_Change_Log` (
  `change_id` int NOT NULL AUTO_INCREMENT,
  `contest_id` int NOT NULL COMMENT 'Concurso donde ocurrió el cambio de problema',
  `problem_id` int NOT NULL COMMENT 'Problema que fue cambiado',
  `user_id` int NOT NULL COMMENT 'Usuario que realizó el cambio (auditoría)',
  `change_type` enum('added','modified','removed') NOT NULL COMMENT 'Tipo de cambio',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`change_id`),
  KEY `idx_contest_timestamp` (`contest_id`, `timestamp`),
  KEY `fk_cpcl_problem_id` (`problem_id`),
  KEY `fk_cpcl_user_id` (`user_id`),
  CONSTRAINT `fk_cpcl_contest_id` FOREIGN KEY (`contest_id`) REFERENCES `Contests` (`contest_id`),
  CONSTRAINT `fk_cpcl_problem_id` FOREIGN KEY (`problem_id`) REFERENCES `Problems` (`problem_id`),
  CONSTRAINT `fk_cpcl_user_id` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
COMMENT='Registro de cambios de problemas en concursos para auditoría e historial.';

