CREATE TABLE `System_Settings` (
  `setting_id` int NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(255) NOT NULL,
  `setting_value` text,
  `setting_description` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`setting_id`),
  UNIQUE KEY `unique_setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Sistema de configuración global del sitio';

INSERT INTO `System_Settings` (`setting_key`, `setting_value`, `setting_description`)
VALUES ('ephemeral_grader_enabled', '1', 'Enable/disable EphemeralGrader IDE on the platform');
