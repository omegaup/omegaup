-- Update previous contests
UPDATE
  Contests
SET
  submissions_gap = 60
WHERE
  submissions_gap < 60;

-- Constest: update submission_gap default from 1 second to 60 seconds
ALTER TABLE `Contests`
  MODIFY COLUMN `submissions_gap` int(11) NOT NULL DEFAULT '60' COMMENT 'Tiempo mínimo en segundos que debe de esperar un usuario despues de realizar un envío para hacer otro';