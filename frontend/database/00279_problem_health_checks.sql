CREATE TABLE `Problem_Health_Checks` (
  `check_id` int NOT NULL AUTO_INCREMENT,
  `problem_id` int NOT NULL,
  `check_type` enum('judge_errors','no_languages','never_solved','deprecated_public') NOT NULL COMMENT 'El tipo de revisión que detectó el problema',
  `severity` enum('warning','error') NOT NULL DEFAULT 'warning',
  `detail` varchar(255) DEFAULT NULL COMMENT 'Explicación legible de lo que se detectó',
  `first_detected_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'La primera vez que se detectó, se conserva entre ejecuciones',
  `last_seen_at` datetime NOT NULL COMMENT 'La última ejecución en la que se seguía detectando',
  `resolved_at` datetime DEFAULT NULL COMMENT 'Cuando dejó de detectarse, NULL si sigue vigente',
  PRIMARY KEY (`check_id`),
  UNIQUE KEY `unique_problem_check` (`problem_id`,`check_type`),
  KEY `idx_problem_health_open` (`resolved_at`,`severity`),
  CONSTRAINT `fk_phc_problem_id` FOREIGN KEY (`problem_id`) REFERENCES `Problems` (`problem_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Hallazgos de las revisiones automáticas de salud de los problemas';

INSERT INTO `Cron_Jobs` (`name`, `description`, `schedule`) VALUES
  ('problem_health_check.py', 'Detects problems that silently stopped working and records the findings', '35 5 * * *');
