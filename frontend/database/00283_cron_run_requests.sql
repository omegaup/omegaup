CREATE TABLE `Cron_Run_Requests` (
  `request_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL COMMENT 'Nombre del script cuya reejecución se solicita',
  `requested_by` int DEFAULT NULL COMMENT 'El administrador que la solicitó, NULL si su cuenta ya no existe',
  `status` enum('pending','picked','done','failed') NOT NULL DEFAULT 'pending' COMMENT 'pending mientras espera al despachador, picked mientras corre',
  `requested_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `picked_at` datetime DEFAULT NULL COMMENT 'Cuando el despachador tomó la solicitud',
  `finished_at` datetime DEFAULT NULL COMMENT 'Cuando terminó la ejecución, haya salido bien o mal',
  `run_id` int DEFAULT NULL COMMENT 'La ejecución que produjo, NULL si no llegó a correr o si el historial ya se purgó',
  `error_text` text COMMENT 'El final de stderr cuando la ejecución falló',
  PRIMARY KEY (`request_id`),
  KEY `idx_cron_run_requests_status` (`status`),
  KEY `idx_cron_run_requests_name` (`name`),
  CONSTRAINT `fk_crr_requested_by` FOREIGN KEY (`requested_by`) REFERENCES `Users` (`user_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_crr_run_id` FOREIGN KEY (`run_id`) REFERENCES `Cron_Runs` (`run_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Solicitudes de reejecución manual de trabajos cron';
