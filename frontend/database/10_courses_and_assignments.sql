CREATE TABLE `Courses` (
  `course_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` tinytext NOT NULL,
  `alias` varchar(32) NOT NULL,
  `group_id` int(11) NOT NULL,
  `acl_id` int(11) NOT NULL,
  `start_time` timestamp NOT NULL DEFAULT '2000-01-01 06:00:00' COMMENT 'Hora de inicio de este curso',
  `finish_time` timestamp NOT NULL DEFAULT '2000-01-01 06:00:00' COMMENT 'Hora de finalizacion de este curso',
  PRIMARY KEY (`course_id`),
  UNIQUE KEY `course_alias` (`alias`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Un curso/clase que un maestro da.';

CREATE TABLE `Assignments` (
  `assignment_id` int(11) NOT NULL AUTO_INCREMENT,
  `course_id` int(11) NOT NULL,
  `problemset_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` tinytext NOT NULL,
  `alias` varchar(32) NOT NULL,
  `publish_time_delay` int(11),
  `assignment_type` enum('homework', 'test') NOT NULL,
  `start_time` timestamp NOT NULL DEFAULT '2000-01-01 06:00:00' ,
  `finish_time` timestamp NOT NULL DEFAULT '2000-01-01 06:00:00',
  PRIMARY KEY (`assignment_id`),
  UNIQUE KEY `assignment_alias` (`course_id`, `alias`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Un alumno resuelve assignments durante su curso, por ahora pueden ser examenes o tareas';

ALTER TABLE `Assignments`
  ADD CONSTRAINT `fk_ac_course_id` FOREIGN KEY (`course_id`) REFERENCES `Courses` (`course_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_ap_problemset_id` FOREIGN KEY (`problemset_id`) REFERENCES `Problemsets` (`problemset_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE `Courses`
  ADD CONSTRAINT `fk_ca_acl_id` FOREIGN KEY (`acl_id`) REFERENCES `ACLs` (`acl_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_cg_student_group_id` FOREIGN KEY (`group_id`) REFERENCES `Groups` (`group_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
