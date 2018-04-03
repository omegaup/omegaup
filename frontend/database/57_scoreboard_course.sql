-- Problemsets
ALTER TABLE `Problemsets`
  ADD COLUMN `scoreboard_url` varchar(30) DEFAULT NULL
    COMMENT 'Token para la url del scoreboard en problemsets',
  ADD COLUMN `scoreboard_url_admin` varchar(30) DEFAULT NULL
    COMMENT 'Token para la url del scoreboard de admin en problemsets';

-- Filling scoreboard_url and scoreboard_url_admin fields in Problemsets with randmom strings
UPDATE `Problemsets`
SET
  `scoreboard_url` = SUBSTRING(MD5(RAND()) FROM 1 FOR 30),
  `scoreboard_url_admin` = SUBSTRING(MD5(RAND()) FROM 1 FOR 30);

-- Updating scoreboard_url and scoreboard_url_admin fields with existing values in Contests table
UPDATE `Problemsets`
INNER JOIN
  `Contests`
ON
  Contests.problemset_id = Problemsets.problemset_id
SET
  `Problemsets`.`scoreboard_url` = `Contests`.`scoreboard_url`,
  `Problemsets`.`scoreboard_url_admin` = `Contests`.`scoreboard_url_admin`;

-- Dropping scoreboard_url and scoreboard_url_admin fields in Contests table
ALTER TABLE `Contests`
  DROP COLUMN `scoreboard_url`,
  DROP COLUMN `scoreboard_url_admin`;
