CREATE TABLE `Cron_Run_Requests` (
  `request_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `requested_by` int DEFAULT NULL,
  `status` enum('pending','picked','done','failed') NOT NULL DEFAULT 'pending',
  `requested_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `picked_at` datetime DEFAULT NULL,
  `finished_at` datetime DEFAULT NULL,
  `run_id` int DEFAULT NULL,
  `error_text` text,
  PRIMARY KEY (`request_id`),
  KEY `idx_cron_run_requests_status` (`status`),
  KEY `idx_cron_run_requests_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Solicitudes de reejecución manual de trabajos cron';
