CREATE TABLE `Runs_Groups` (
  case_run_id int NOT NULL AUTO_INCREMENT,
  run_id int NOT NULL,
  group_name char(40) NOT NULL,
  score double NOT NULL DEFAULT '0',
  verdict enum('AC','PA','PE','WA','TLE','OLE','MLE','RTE','RFE','CE','JE','VE') NOT NULL,
  PRIMARY KEY (case_run_id),
  UNIQUE KEY (run_id, group_name),
  FOREIGN KEY (run_id) REFERENCES Runs (run_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Guarda los grupos de runs.';