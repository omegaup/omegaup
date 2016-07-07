

CREATE TABLE IF NOT EXISTS `Interviews` (
  `contest_id` int(11) NOT NULL,
  PRIMARY KEY (`contest_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Lista de id_concuros que se usan para entrevista';

INSERT INTO  `Roles` (`role_id` ,`name` ,`description`) VALUES (4 ,  'INTERVIEWER',  'User can create interviews');
