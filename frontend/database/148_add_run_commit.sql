-- Alter Runs table

ALTER TABLE
  `Runs`
ADD COLUMN
  `commit` char(40) NOT NULL DEFAULT 'published'
    AFTER `version`;

-- Updating commit field
UPDATE
  `Runs`
INNER JOIN
  `Submissions` ON `Runs`.`submission_id` = `Submissions`.`submission_id`
INNER JOIN
  `Problems` ON `Submissions`.`problem_id` = `Problems`.`problem_id`
SET
  `Runs`.`commit` = `Problems`.`commit`;

-- Alter table Runs, removing default value in commit column

ALTER TABLE
  `Runs`
MODIFY COLUMN
  `commit` char(40) NOT NULL
    COMMENT 'El hash SHA1 del commit en la rama master del problema con el que se realizó el envío.';
