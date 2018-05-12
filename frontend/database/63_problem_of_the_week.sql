CREATE TABLE `Problem_Of_The_Week` (
  `problem_of_the_week_id` int(11) NOT NULL AUTO_INCREMENT,
  `problem_id` int(11) NOT NULL COMMENT 'El id del problema escogido como problema de la semana.',
  `time` date NOT NULL DEFAULT '2000-01-01' COMMENT 'El inicio de la semana de la cual este problema fue elegido como el mejor de la semana.',
  `difficulty` enum('easy', 'hard') NOT NULL COMMENT 'En algún momento tendremos un problema fácil y uno difícil.',
  PRIMARY KEY (`problem_of_the_week_id`),
  KEY `problem_id` (`problem_id`),
  CONSTRAINT `fk_problem_id` FOREIGN KEY (`problem_id`) REFERENCES `Problems` (`problem_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Lista de problemas de la semana.';

CREATE UNIQUE INDEX idx_time_difficulty ON Problem_Of_The_Week (`time`, `difficulty`);
