-- Any ACL with ID less than 65536 is meant to be reserved for certain parts of
-- the system.
ALTER TABLE `ACLs`
  AUTO_INCREMENT = 65536,
  CHANGE COLUMN `acl_id` `acl_id` int(11) NOT NULL AUTO_INCREMENT;

-- ACL ID 1 represents the whole system.
INSERT IGNORE INTO
  Users (user_id, username, name)
VALUES
  (1, "omegaup", "omegaUp admin");
INSERT INTO ACLs (`acl_id`, `owner_id`) VALUES (1, 1);

-- Contests
ALTER TABLE `Contests`
  ADD COLUMN `acl_id` int(11) NULL AFTER `contest_id`;

DELIMITER $$
CREATE PROCEDURE `Contests_ACLs_Migrate`()
BEGIN
  DECLARE done INT DEFAULT FALSE;
  DECLARE acl, contest, owner INT(11);
  DECLARE cur CURSOR FOR SELECT `contest_id`, `director_id` FROM `Contests`;

  DECLARE EXIT HANDLER FOR SQLEXCEPTION ROLLBACK;
  DECLARE EXIT HANDLER FOR SQLWARNING ROLLBACK;
  DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

  START TRANSACTION;
  OPEN cur;

  read_loop: LOOP
    FETCH cur INTO contest, owner;
    IF done THEN
      LEAVE read_loop;
    END IF;

    INSERT INTO `ACLs` (`owner_id`) VALUES (owner);
    SET acl = LAST_INSERT_ID();
    UPDATE `Contests` SET `acl_id` = acl WHERE `contest_id` = contest;
    UPDATE
      `User_Roles`
    SET
      `contest_id` = acl,
      `role_id` = 1
    WHERE
      `contest_id` = contest AND `role_id` = 2;
    UPDATE
      `Group_Roles`
    SET
      `contest_id` = acl,
      `role_id` = 1
    WHERE
      `contest_id` = contest AND `role_id` = 2;
  END LOOP;

  CLOSE cur;
  COMMIT;
END$$
DELIMITER ;

CALL `Contests_ACLs_Migrate`();

DROP PROCEDURE `Contests_ACLs_Migrate`;

ALTER TABLE `Contests`
  CHANGE COLUMN `acl_id` `acl_id` int(11) NOT NULL,
  ADD KEY `acl_id` (`acl_id`),
  ADD CONSTRAINT `fk_coa_acl_id` FOREIGN KEY (`acl_id`) REFERENCES `ACLs` (`acl_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  DROP FOREIGN KEY `fk_cu_director_id`,
  DROP INDEX `idx_contest_director_id`,
  DROP INDEX `idx_contest_public_director_id`,
  DROP COLUMN `director_id`;

-- Problems
ALTER TABLE `Problems`
  ADD COLUMN `acl_id` int(11) NULL AFTER `problem_id`;

DELIMITER $$
CREATE PROCEDURE `Problems_ACLs_Migrate`()
BEGIN
  DECLARE done INT DEFAULT FALSE;
  DECLARE acl, problem, owner INT(11);
  DECLARE cur CURSOR FOR SELECT `problem_id`, `author_id` FROM `Problems`;

  DECLARE EXIT HANDLER FOR SQLEXCEPTION ROLLBACK;
  DECLARE EXIT HANDLER FOR SQLWARNING ROLLBACK;
  DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

  START TRANSACTION;
  OPEN cur;

  read_loop: LOOP
    FETCH cur INTO problem, owner;
    IF done THEN
      LEAVE read_loop;
    END IF;

    INSERT INTO `ACLs` (`owner_id`) VALUES (owner);
    SET acl = LAST_INSERT_ID();
    UPDATE `Problems` SET `acl_id` = acl WHERE `problem_id` = problem;
    UPDATE
      `User_Roles`
    SET
      `contest_id` = acl,
      `role_id` = 1
    WHERE
      `contest_id` = problem AND `role_id` = 3;
    UPDATE
      `Group_Roles`
    SET
      `contest_id` = acl,
      `role_id` = 1
    WHERE
      `contest_id` = problem AND `role_id` = 3;
  END LOOP;

  CLOSE cur;
  COMMIT;
END$$
DELIMITER ;

CALL `Problems_ACLs_Migrate`();

DROP PROCEDURE `Problems_ACLs_Migrate`;

ALTER TABLE `Problems`
  CHANGE COLUMN `acl_id` `acl_id` int(11) NOT NULL,
  ADD KEY `acl_id` (`acl_id`),
  ADD CONSTRAINT `fk_pa_acl_id` FOREIGN KEY (`acl_id`) REFERENCES `ACLs` (`acl_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  DROP FOREIGN KEY `author_id`,
  DROP COLUMN `author_id`;

-- System ACLs.
UPDATE
  `User_Roles`
SET
  `contest_id` = 1
WHERE
  `contest_id` = 0 AND `role_id` IN (1, 4);

UPDATE
  `Group_Roles`
SET
  `contest_id` = 1
WHERE
  `contest_id` = 0 AND `role_id` IN (1, 4);

-- Roles update.
ALTER TABLE `User_Roles`
  DROP KEY `contest_id`,
  ADD KEY `acl_id` (`acl_id`),
  CHANGE COLUMN `contest_id` `acl_id` INT(11) NOT NULL,
  ADD CONSTRAINT `fk_ura_acl_id` FOREIGN KEY (`acl_id`) REFERENCES `ACLs` (`acl_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE `Group_Roles`
  DROP KEY `contest_id`,
  ADD KEY `acl_id` (`acl_id`),
  CHANGE COLUMN `contest_id` `acl_id` INT(11) NOT NULL,
  ADD CONSTRAINT `fk_gra_acl_id` FOREIGN KEY (`acl_id`) REFERENCES `ACLs` (`acl_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

-- vim: set expandtab:ts=2:sw=2
