CREATE TABLE `Recommendation_Model_Runs` (
  `model_run_id` int NOT NULL AUTO_INCREMENT,
  `cron_run_id` int DEFAULT NULL COMMENT 'La ejecución de cron que entrenó este modelo, NULL si se corrió a mano',
  `map_score` double NOT NULL COMMENT 'MAP@k sobre los usuarios de prueba, la medida con la que se compara contra el último modelo publicado',
  `dataset_size` int NOT NULL COMMENT 'Cuántos envíos aceptados se usaron para entrenar',
  `num_followups` int NOT NULL COMMENT 'Parámetro de entrenamiento: cuántos problemas siguientes se consideran por usuario',
  `followup_decay` double NOT NULL COMMENT 'Parámetro de entrenamiento: qué tan rápido pierde peso cada problema siguiente',
  `train_fraction` double NOT NULL COMMENT 'Parámetro de entrenamiento: qué fracción de los usuarios se usa para entrenar',
  `rng_seed` int DEFAULT NULL COMMENT 'Parámetro de entrenamiento: la semilla con la que se partieron los usuarios entre entrenamiento y prueba, NULL si no se fijó ninguna',
  `output_path` varchar(255) DEFAULT NULL COMMENT 'Dónde quedó el modelo entrenado',
  `published` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Si este modelo reemplazó al que estaba en uso',
  `skip_reason` varchar(255) DEFAULT NULL COMMENT 'Por qué no se publicó, NULL si sí se publicó',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`model_run_id`),
  KEY `idx_rec_model_runs_published_created` (`published`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Historial de entrenamientos del modelo de recomendación de problemas';

INSERT INTO `Cron_Jobs` (`name`, `description`, `schedule`) VALUES
  ('build_problem_rec_model.py', 'Trains the problem recommendation model from accepted submissions', '0 4 * * 0');
