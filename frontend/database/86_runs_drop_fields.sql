ALTER TABLE `Runs`
  DROP FOREIGN KEY `fk_r_identity_id`,
  DROP FOREIGN KEY `fk_r_problem_id`,
  DROP FOREIGN KEY `fk_r_problemset_id`,
  MODIFY COLUMN `version` char(40) NOT NULL COMMENT 'El hash SHA1 del árbol de la rama private.',
  DROP COLUMN `identity_id`,
  DROP COLUMN `problem_id`,
  DROP COLUMN `problemset_id`,
  DROP COLUMN `guid`,
  DROP COLUMN `language`,
  DROP COLUMN `submit_delay`,
  DROP COLUMN `type`;

ALTER TABLE `Submissions`
  DROP COLUMN `penalty`;

ALTER TABLE `Problems`
  MODIFY COLUMN `current_version` char(40) NOT NULL COMMENT 'El hash SHA1 del árbol de la rama private.';

ALTER TABLE `Problemset_Problems`
  MODIFY COLUMN `version` char(40) NOT NULL COMMENT 'El hash SHA1 del árbol de la rama private.';
