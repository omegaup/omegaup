-- AI Editorial Jobs table
CREATE TABLE `AI_Editorial_Jobs` (
    `job_id` varchar(36) NOT NULL COMMENT 'UUID identificador único del trabajo',
    `problem_id` int NOT NULL COMMENT 'Identificador del problema',
    `user_id` int NOT NULL COMMENT 'Usuario que solicitó la generación',
    `status` enum('queued','processing','completed','failed','approved','rejected') NOT NULL DEFAULT 'queued' COMMENT 'Estado actual del trabajo',
    `error_message` text COMMENT 'Mensaje de error en caso de fallo',
    `is_retriable` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Indica si el error permite reintentos (1 = sí, 0 = no)',
    `attempts` int NOT NULL DEFAULT 0 COMMENT 'Número de intentos realizados',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Hora de creación del trabajo',
    `md_en` mediumtext NULL COMMENT 'Editorial generado en inglés',
    `md_es` mediumtext NULL COMMENT 'Editorial generado en español',
    `md_pt` mediumtext NULL COMMENT 'Editorial generado en portugués',
    `validation_verdict` varchar(10) NULL COMMENT 'Veredicto de validación del código generado',
    PRIMARY KEY (`job_id`),
    KEY `idx_problem_id` (`problem_id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_status` (`status`),
    KEY `idx_created_at` (`created_at`),
    CONSTRAINT `fk_aej_problem_id` FOREIGN KEY (`problem_id`) REFERENCES `Problems` (`problem_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
    CONSTRAINT `fk_aej_user_id` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Trabajos de generación de editoriales con IA'; 