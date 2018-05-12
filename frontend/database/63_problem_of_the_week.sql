CREATE TABLE `Problem_Of_The_Week` (
  `problem_of_the_week_id` int(11) NOT NULL AUTO_INCREMENT,
  `problem_id` int(11) NOT NULL COMMENT 'The id of the problem that was chosen as problem of the week.',
  `time` date NOT NULL DEFAULT '2000-01-01' COMMENT 'Time is not unique because we plan to have two problems of the week per week.',
  `difficulty` enum('easy', 'hard') NOT NULL COMMENT 'At some point we will have two problems of the week per week, an easy one and a hard one.',
  PRIMARY KEY (`problem_of_the_week_id`),
  KEY `problem_id` (`problem_id`),
  CONSTRAINT `fk_problem_id` FOREIGN KEY (`problem_id`) REFERENCES `Problems` (`problem_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='List of problems of the week.';
