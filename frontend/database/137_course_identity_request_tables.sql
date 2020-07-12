-- Create tables
CREATE TABLE `Course_Identity_Request` (
  `identity_id` int(11) NOT NULL COMMENT 'Identidad del usuario',
  `course_id` int(11) NOT NULL COMMENT 'Curso al cual se necesita un request para ingresar',
  `request_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Hora en la que se realizó el request',
  `last_update` timestamp NULL DEFAULT NULL COMMENT 'Última fecha de actualización del request',
  `accepted` tinyint(1) DEFAULT NULL COMMENT 'Indica si la respuesta del request fue aceptada',
  PRIMARY KEY (`identity_id`,`course_id`),
  KEY `course_id` (`course_id`),
  KEY `identity_id` (`identity_id`),
  CONSTRAINT `fk_ciri_identity_id` FOREIGN KEY (`identity_id`) REFERENCES `Identities` (`identity_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_circ_course_id` FOREIGN KEY (`course_id`) REFERENCES `Courses` (`course_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Usado cuando un curso se registra con admission_mode = registration';

CREATE TABLE `Course_Identity_Request_History` (
  `history_id` int(11) NOT NULL AUTO_INCREMENT,
  `identity_id` int(11) NOT NULL COMMENT 'Identidad del usuario',
  `course_id` int(11) NOT NULL COMMENT 'Curso al cual se necesita un request para ingresar',
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Hora en la que se realizó el request',
  `accepted` tinyint(4) NOT NULL COMMENT 'Indica si la respuesta del request fue aceptada',
  `admin_id` int(11) NOT NULL COMMENT 'Identidad que usuario aceptó / rechazo el request',
  PRIMARY KEY (`history_id`),
  KEY `course_id` (`course_id`),
  KEY `identity_course_hist` (`identity_id`,`course_id`),
  CONSTRAINT `fk_cirhi_identity_id` FOREIGN KEY (`identity_id`) REFERENCES `Identities` (`identity_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_cirhc_curse_id` FOREIGN KEY (`course_id`) REFERENCES `Courses` (`course_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Tabla para almacenar la respuesta de cada una de las peticiones hechas al curso con admission_mode = registration';

-- Update Groups_Identities, adding is_invited column
ALTER TABLE
  `Groups_Identities`
ADD COLUMN
  `is_invited` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Indica si la identidad ingresará al curso por invitación o le fue compartido el link del curso abierto con registro';
