CREATE TABLE `User_Rank_Cutoffs` (
  `score` double NOT NULL,
  `percentile` double NOT NULL,
  `classname` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Guarda los valores del ranking para los cuales hay un cambio de color.';

