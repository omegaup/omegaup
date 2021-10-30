-- Certificates
CREATE TABLE `Certificates` (
  `certificate_id` int(11) NOT NULL AUTO_INCREMENT,
  `identity_id` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha y hora del otorgamiento del diploma',
  `certificate_type` enum('course','contest','coder_of_the_month','coder_of_the_month_female') NOT NULL COMMENT 'Tipo de diploma',
  `course_id` int(11) DEFAULT NULL,
  `contest_id` int(11) DEFAULT NULL,
  `verification_code` varchar(10) NOT NULL UNIQUE COMMENT 'Código de verificación del diploma',
  PRIMARY KEY (`certificate_id`),
  KEY `identity_id` (`identity_id`),
  KEY `course_id` (`course_id`),
  KEY `contest_id` (`contest_id`),
  CONSTRAINT `fk_ci_identity_id` FOREIGN KEY (`identity_id`) REFERENCES `Identities` (`identity_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_cc_course_id` FOREIGN KEY (`course_id`) REFERENCES `Courses` (`course_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_cc_contest_id` FOREIGN KEY (`contest_id`) REFERENCES `Contests` (`contest_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Diplomas';
