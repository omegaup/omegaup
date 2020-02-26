ALTER TABLE Runs
MODIFY COLUMN `verdict` enum('AC','PA','PE','WA','TLE','OLE','MLE','RTE','RFE','CE','JE','VE') NOT NULL;
