CREATE TABLE `Cron_AggregateFeedback_State` (
  `id` int NOT NULL AUTO_INCREMENT,
  `last_processed_qualitynomination_id` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;