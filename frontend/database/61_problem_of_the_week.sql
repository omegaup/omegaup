CREATE TABLE IF NOT EXISTS `Problem_Of_The_Week` (
  `problem_of_the_week_id` int(11) NOT NULL AUTO_INCREMENT,
  `problem_id` int(11) NOT NULL COMMENT 'El id del problema que fue seleccionado como problema de la semana.',
  `time` date NOT NULL DEFAULT '2000-01-01' COMMENT 'Fecha no es UNIQUE por si hay más de 1 problema de la semana.',
  `difficulty` enum('easy', 'hard') NOT NULL COMMENT 'At some point we will have two problems of the week per week, an easy one and a hard one.',
  PRIMARY KEY (`problem_of_the_week_id`),
  KEY `problem_id` (`problem_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='La cola lista de problemas de la semana.';
