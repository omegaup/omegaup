CREATE TABLE `Recommendation_Model_Runs` (
  `model_run_id` int NOT NULL AUTO_INCREMENT,
  `cron_run_id` int DEFAULT NULL,
  `map_score` double NOT NULL,
  `dataset_size` int NOT NULL,
  `num_followups` int NOT NULL,
  `followup_decay` double NOT NULL,
  `train_fraction` double NOT NULL,
  `output_path` varchar(255) DEFAULT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `skip_reason` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`model_run_id`),
  KEY `idx_rec_model_runs_published_created` (`published`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Historial de entrenamientos del modelo de recomendación de problemas';

INSERT INTO `Cron_Jobs` (`name`, `description`, `schedule`) VALUES
  ('build_problem_rec_model.py', 'Trains the problem recommendation model from accepted submissions', '0 4 * * 0');
