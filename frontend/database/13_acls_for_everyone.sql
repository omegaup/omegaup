-- Add columns
ALTER TABLE `Assignments`
  ADD COLUMN `acl_id` int(11) NULL AFTER `problemset_id`;
ALTER TABLE `Problemsets`
  ADD COLUMN `acl_id` int(11) NULL AFTER `problemset_id`;

-- Populate columns

-- Course => Assignments
UPDATE
  `Assignments` AS `a`
INNER JOIN
  `Courses` AS `c` ON `a`.`course_id` = `c`.`course_id`
SET
  `a`.`acl_id` = `c`.`acl_id`;

-- Problemset Containers => Problemsets
START TRANSACTION;

UPDATE
  `Problemsets` AS `ps`
INNER JOIN
  `Assignments` AS `a` ON `ps`.`problemset_id` = `a`.`problemset_id`
SET
  `ps`.`acl_id` = `a`.`acl_id`;

UPDATE
  `Problemsets` AS `ps`
INNER JOIN
  `Contests` AS `c` ON `ps`.`problemset_id` = `c`.`problemset_id`
SET
  `ps`.`acl_id` = `c`.`acl_id`;

UPDATE
  `Problemsets` AS `ps`
INNER JOIN
  `Interviews` AS `i` ON `ps`.`problemset_id` = `i`.`problemset_id`
SET
  `ps`.`acl_id` = `i`.`acl_id`;

COMMIT;

-- Set up constraints
ALTER TABLE `Assignments`
  MODIFY COLUMN `acl_id` int(11) NOT NULL COMMENT 'La lista de control de acceso compartida con el curso';
ALTER TABLE `Problemsets`
  MODIFY COLUMN `acl_id` int(11) NOT NULL COMMENT 'La lista de control de acceso compartida con su container';

ALTER TABLE `Assignments`
  ADD KEY `acl_id` (`acl_id`),
  ADD CONSTRAINT `fk_aa_acl_id` FOREIGN KEY (`acl_id`) REFERENCES `ACLs` (`acl_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE `Problemsets`
  ADD KEY `acl_id` (`acl_id`),
  ADD CONSTRAINT `fk_psa_acl_id` FOREIGN KEY (`acl_id`) REFERENCES `ACLs` (`acl_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
