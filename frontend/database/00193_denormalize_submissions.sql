ALTER TABLE `Submissions`
  ADD COLUMN `status` enum('new','waiting','compiling','running','ready','uploading') NOT NULL DEFAULT 'new' AFTER `time`,
  ADD COLUMN `verdict` enum('AC','PA','PE','WA','TLE','OLE','MLE','RTE','RFE','CE','JE','VE') NOT NULL AFTER `status`;
