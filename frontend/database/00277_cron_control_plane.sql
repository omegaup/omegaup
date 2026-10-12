CREATE TABLE `Cron_Jobs` (
  `job_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `description` text,
  `schedule` varchar(64) DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`job_id`),
  UNIQUE KEY `unique_cron_job_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Registro de los trabajos programados (cron) del sistema';

CREATE TABLE `Cron_Runs` (
  `run_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL COMMENT 'Nombre del script (parser.prog). Denormalizado a propósito: el historial se registra aunque el trabajo no esté en Cron_Jobs y sobrevive a renombres o borrados del registro',
  `hostname` varchar(255) DEFAULT NULL,
  `status` enum('running','success','failure') NOT NULL DEFAULT 'running',
  `started_at` datetime NOT NULL,
  `finished_at` datetime DEFAULT NULL,
  `duration_seconds` double DEFAULT NULL,
  `rows_affected` int DEFAULT NULL,
  `phases` json DEFAULT NULL,
  `error_text` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`run_id`),
  KEY `idx_cron_runs_name_started` (`name`, `started_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Historial de ejecuciones de los trabajos cron';

INSERT INTO `Cron_Jobs` (`name`, `description`, `schedule`) VALUES
  ('update_ranks.py', 'Recomputes user, author and school rankings and monthly candidates', '19 8 * * *'),
  ('assign_badges.py', 'Awards badges and creates the corresponding notifications', '23 9 * * *'),
  ('aggregate_feedback.py', 'Aggregates quality and difficulty feedback and problem of the week', '18 10 * * *');
