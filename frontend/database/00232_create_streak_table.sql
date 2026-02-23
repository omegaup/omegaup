-- Create Users_Streaks table
CREATE TABLE IF NOT EXISTS `Users_Streaks` (
  `user_id` INT NOT NULL COMMENT 'Identificador de usuario',
  `streak_count` INT NOT NULL DEFAULT 0 COMMENT 'Total de días de racha',
  `submission_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Último envío de problema',
  `easy_solved` INT NOT NULL DEFAULT 0 COMMENT 'Total de problemas fáciles resueltos',
  `medium_solved` INT NOT NULL DEFAULT 0 COMMENT 'Total de problemas regulares resueltos',
  `hard_solved` INT NOT NULL DEFAULT 0 COMMENT 'Total de problemas difíciles resueltos',
  PRIMARY KEY (`user_id`),
  CONSTRAINT `fk_us_user_id` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE CASCADE,
  INDEX `idx_submission_time` (`submission_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Guarda la racha mantenida por los usuarios.';

-- Create Challenges table
CREATE TABLE IF NOT EXISTS `Challenges` (
  `challenge_id` INT NOT NULL AUTO_INCREMENT COMMENT 'Identificador único del desafío',
  `challenge_name` VARCHAR(255) NOT NULL COMMENT 'Nombre del desafío',
  `difficulty` ENUM('Easy', 'Medium', 'Hard') NOT NULL COMMENT 'Nivel de dificultad del desafío',
  `start_time` DATETIME NOT NULL COMMENT 'Hora de inicio del desafío',
  `end_time` DATETIME NOT NULL COMMENT 'Hora de finalización del desafío',
  `completed_users` INT NOT NULL DEFAULT 0 COMMENT 'Número de usuarios que completaron el desafío',
  PRIMARY KEY (`challenge_id`),
  INDEX `idx_difficulty` (`difficulty`),
  INDEX `idx_start_time` (`start_time`),
  INDEX `idx_end_time` (`end_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Almacena información sobre desafíos de programación.';

-- Create User_Challenges table
CREATE TABLE IF NOT EXISTS `User_Challenges` (
  `user_id` INT NOT NULL COMMENT 'Identificador del usuario',
  `challenge_id` INT NOT NULL COMMENT 'Identificador del desafío',
  `completed` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Bandera que indica si el usuario completó el desafío',
  `last_update` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Última actualización de la tabla',
  PRIMARY KEY (`user_id`, `challenge_id`),
  CONSTRAINT `fk_uc_user_id` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_uc_challenge_id` FOREIGN KEY (`challenge_id`) REFERENCES `Challenges` (`challenge_id`) ON DELETE CASCADE,
  INDEX `idx_completed` (`completed`),
  INDEX `idx_last_update` (`last_update`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Registra los desafíos completados por cada usuario.';

-- Create User_Freeze table
CREATE TABLE IF NOT EXISTS `User_Freezes` (
  `user_id` INT NOT NULL COMMENT 'Identificador del usuario',
  `freeze_available` INT NOT NULL DEFAULT 0 COMMENT 'Cantidad de pausa de racha disponibles para el usuario',
  `freeze_used` INT NOT NULL DEFAULT 0 COMMENT 'Cantidad de pausa de racha usados por el usuario',
  PRIMARY KEY (`user_id`),
  CONSTRAINT `fk_freeze_user_id` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Registra los pausa de racha disponibles y usados por cada usuario.';

