ALTER TABLE `Assignments`
  ADD COLUMN `acl_id` int(11) NULL AFTER `problemset_id`;

ALTER TABLE `Assignments`
  ADD KEY `acl_id` (`acl_id`),
  ADD CONSTRAINT `fk_aa_acl_id` FOREIGN KEY (`acl_id`) REFERENCES `ACLs` (`acl_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE `Problemsets`
  ADD COLUMN `acl_id` int(11) NULL AFTER `problemset_id`;

ALTER TABLE `Problemsets`
  ADD KEY `acl_id` (`acl_id`),
  ADD CONSTRAINT `fk_psa_acl_id` FOREIGN KEY (`acl_id`) REFERENCES `ACLs` (`acl_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

-- Course => Assignments
DELIMITER $$
CREATE PROCEDURE `Denormalize_Course_ACLs`()
BEGIN
  DECLARE done INT DEFAULT FALSE;
  DECLARE acl, course INT(11);
  DECLARE cur CURSOR FOR SELECT `course_id`, `acl_id` FROM `Courses`;

  DECLARE EXIT HANDLER FOR SQLEXCEPTION ROLLBACK;
  DECLARE EXIT HANDLER FOR SQLWARNING ROLLBACK;
  DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

  START TRANSACTION;
  OPEN cur;

  read_loop: LOOP
    FETCH cur INTO course, acl;
    IF done THEN
      LEAVE read_loop;
    END IF;

    UPDATE `Assignments` SET `acl_id` = acl WHERE `course_id` = course;
  END LOOP read_loop;

  CLOSE cur;
  COMMIT;
END$$
DELIMITER ;

CALL `Denormalize_Course_ACLs`();

DROP PROCEDURE `Denormalize_Course_ACLs`;

-- Problemset Containers => Problemsets
DELIMITER $$
CREATE PROCEDURE `Denormalize_Problemset_ACLs`()
BEGIN
  DECLARE done INT DEFAULT FALSE;
  DECLARE acl, problemset INT(11);
  DECLARE assignment_cur CURSOR FOR SELECT `problemset_id`, `acl_id` FROM `Assignments`;
  DECLARE contest_cur CURSOR FOR SELECT `problemset_id`, `acl_id` FROM `Contests`;
  DECLARE interview_cur CURSOR FOR SELECT `problemset_id`, `acl_id` FROM `Interviews`;

  DECLARE EXIT HANDLER FOR SQLEXCEPTION ROLLBACK;
  DECLARE EXIT HANDLER FOR SQLWARNING ROLLBACK;
  DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

  START TRANSACTION;

  OPEN assignment_cur;
  assignment_read_loop: LOOP
    FETCH assignment_cur INTO problemset, acl;
    IF done THEN
      LEAVE assignment_read_loop;
    END IF;

    UPDATE `Problemsets` SET `acl_id` = acl WHERE `problemset_id` = problemset;
  END LOOP assignment_read_loop;
  CLOSE assignment_cur;

  OPEN contest_cur;
  contest_read_loop: LOOP
    FETCH contest_cur INTO problemset, acl;
    IF done THEN
      LEAVE contest_read_loop;
    END IF;

    UPDATE `Problemsets` SET `acl_id` = acl WHERE `problemset_id` = problemset;
  END LOOP contest_read_loop;
  CLOSE contest_cur;

  OPEN interview_cur;
  interview_read_loop: LOOP
    FETCH interview_cur INTO problemset, acl;
    IF done THEN
      LEAVE interview_read_loop;
    END IF;

    UPDATE `Problemsets` SET `acl_id` = acl WHERE `problemset_id` = problemset;
  END LOOP interview_read_loop;
  CLOSE interview_cur;

  COMMIT;
END$$
DELIMITER ;

CALL `Denormalize_Problemset_ACLs`();

DROP PROCEDURE `Denormalize_Problemset_ACLs`;
