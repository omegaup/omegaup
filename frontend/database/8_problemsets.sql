CREATE TABLE `Problemsets` (
  `problemset_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'El identificador único para cada conjunto de problemas',
  `access_mode` enum('private', 'public', 'registration') NOT NULL DEFAULT 'public' COMMENT 'La modalidad de acceso a este conjunto de problemas',
  `languages` set('c','cpp','java','py','rb','pl','cs','pas','kp','kj','cat','hs','cpp11') DEFAULT NULL COMMENT 'Un filtro (opcional) de qué lenguajes se pueden usar para resolver los problemas',
  PRIMARY KEY (`problemset_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Conjunto de problemas.';

CREATE TABLE `Problemset_Problems` (
  `problemset_id` int(11) NOT NULL,
  `problem_id` int(11) NOT NULL,
  `points` double NOT NULL DEFAULT '1',
  `order` INT NOT NULL DEFAULT  '1' COMMENT 'Define el orden de aparición de los problemas en un concurso',
  PRIMARY KEY (`problemset_id`,`problem_id`),
  KEY `problemset_id` (`problemset_id`),
  KEY `problem_id` (`problem_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Los problemas de cada conjunto';

CREATE TABLE `ACLs` (
  `acl_id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL COMMENT 'El usuario que creó el objeto y que tiene un rol de administrador implícito',
  PRIMARY KEY (`acl_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Lista de control de acceso.';

ALTER TABLE `Problemset_Problems`
  ADD CONSTRAINT `fk_ppp_problemset_id` FOREIGN KEY (`problemset_id`) REFERENCES `Problemsets` (`problemset_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_ppp_problem_id` FOREIGN KEY (`problem_id`) REFERENCES `Problems` (`problem_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE `ACLs`
  ADD CONSTRAINT `fk_au_owner_id` FOREIGN KEY (`owner_id`) REFERENCES `Users` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
