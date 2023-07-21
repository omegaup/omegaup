-- Table Course_Clone_Log
CREATE TABLE `Course_Clone_Log` (
  `course_clone_log_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador del intento de clonar curso',
  `ip` VARCHAR(40) NOT NULL COMMENT 'Dirección IP desde la cual se intentó clonar el curso.',
  `course_id` int(11) NOT NULL COMMENT 'ID del curso original',
  `new_course_id` int(11) DEFAULT NULL COMMENT 'ID del curso nuevo, null si no se pudo colonar el curso',
  `token_payload`  varchar(50) NOT NULL COMMENT 'Claims del token usado para intentar clonar, independientemente de si fue exitoso o no.',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha y hora en la que el usuario intenta clonar el curso',
  `user_id` int(11) NOT NULL COMMENT 'ID del usuario que intentó clonar.',
  `result` enum('unknown','success','token_expired','token_corrupted','token_invalid') NOT NULL DEFAULT 'success' COMMENT 'Resultado obtenido del intento de clonación de curso',
  PRIMARY KEY (`course_clone_log_id`),
  KEY `user_id` (`user_id`),
  KEY `course_id` (`course_id`),
  KEY `new_course_id` (`new_course_id`),
  CONSTRAINT `fk_ccl_user_id` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_ccl_course_id` FOREIGN KEY (`course_id`) REFERENCES `Courses` (`course_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_ccl_new_course_id` FOREIGN KEY (`new_course_id`) REFERENCES `Courses` (`course_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Bitácora de registro para cursos clonados';
