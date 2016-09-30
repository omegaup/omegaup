CREATE TABLE `Courses` (
  `course_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` tinytext NOT NULL,
  `alias` varchar(32) NOT NULL,
  `id_owner` int(11) NOT NULL,
  `id_admingroup` int(11),
  `start_time` timestamp NOT NULL DEFAULT '2000-01-01 06:00:00' COMMENT 'Hora de inicio de este curso',
  `finish_time` timestamp NOT NULL DEFAULT '2000-01-01 06:00:00' COMMENT 'Hora de finalizacion de este curso',
  PRIMARY KEY (`course_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Un curso/clase que un maestro da.';

CREATE TABLE `Assignments` (
  `assignement_id` int(11) NOT NULL AUTO_INCREMENT,
  `id_course` int(11) NOT NULL,
  `id_problemset` int(11),
  `name` varchar(100) NOT NULL,
  `description` tinytext NOT NULL,
  `alias` varchar(32) NOT NULL,
  `PublishTimeDelay` int(11),
  `start_time` timestamp NOT NULL DEFAULT '2000-01-01 06:00:00' COMMENT 'Hora de inicio de este curso',
  `finish_time` timestamp NOT NULL DEFAULT '2000-01-01 06:00:00' COMMENT 'Hora de finalizacion de este curso',
  PRIMARY KEY (`assignement_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Un alumno resuelve assignments durante su curso, por ahora pueden ser examenes o tareas';

ALTER TABLE `Assignments`
  ADD CONSTRAINT `fk_ac_course_id` FOREIGN KEY (`id_course`) REFERENCES `Courses` (`course_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE `Courses`
  ADD CONSTRAINT `fk_cu_owner_id` FOREIGN KEY (`id_owner`) REFERENCES `Users` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_cg_group_id` FOREIGN KEY (`id_admingroup`) REFERENCES `Groups` (`group_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

