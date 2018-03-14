-- Rename table

RENAME TABLE
  `Groups_Scoreboards_Contests` TO `Groups_Scoreboards_Problemsets`;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;

-- Adding problemset_id column on Groups_Scoreboards_Problemsets and synchronizing problemset - contest data
ALTER TABLE `Groups_Scoreboards_Problemsets`
  ADD COLUMN `problemset_id` int(11) NOT NULL COMMENT 'Conjunto de problemas del scoreboard' AFTER `contest_id`,
  ADD KEY `problemset_id` (`problemset_id`),
  DROP FOREIGN KEY `fk_gsc_contest_id`,
  DROP KEY `contest_id`,
  DROP PRIMARY KEY;

UPDATE
  `Groups_Scoreboards_Problemsets`
INNER JOIN
  `Problemsets`
ON
  Problemsets.problemset_id = Groups_Scoreboards_Problemsets.problemset_id
SET
  `Groups_Scoreboards_Problemsets`.`problemset_id` = Problemsets.problemset_id;

-- Deleting contest_id column and creating indexes
ALTER TABLE `Groups_Scoreboards_Problemsets`
  ADD CONSTRAINT `fk_gsp_problemset_id` FOREIGN KEY (`problemset_id`) REFERENCES `Problemsets` (`problemset_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD PRIMARY KEY (`group_scoreboard_id`, `problemset_id`),
  DROP COLUMN `contest_id`;

/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;