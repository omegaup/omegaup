-- Courses
ALTER TABLE `Courses`
  ADD COLUMN `problemset_id` int(11) NULL AFTER `course_id`;

DELIMITER $$
CREATE PROCEDURE `Aggregate_Problemset_Course`()
BEGIN
  DECLARE done INT DEFAULT FALSE;
  DECLARE problemsetid, aclid, courseid INT(11);
  DECLARE cur CURSOR FOR SELECT `acl_id`, `course_id` FROM `Courses`;

  DECLARE EXIT HANDLER FOR SQLEXCEPTION ROLLBACK;
  DECLARE EXIT HANDLER FOR SQLWARNING ROLLBACK;
  DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

  START TRANSACTION;
  OPEN cur;

  read_loop: LOOP
    FETCH cur INTO aclid, courseid;
    IF done THEN
      LEAVE read_loop;
    END IF;

    INSERT INTO `Problemsets` (`acl_id`) VALUES (aclid);
    SET problemsetid = LAST_INSERT_ID();
    UPDATE `Courses` SET `problemset_id` = problemsetid WHERE `course_id` = courseid;
  END LOOP read_loop;

  CLOSE cur;
  COMMIT;
END$$
DELIMITER ;

CALL `Aggregate_Problemset_Course`();

DROP PROCEDURE `Aggregate_Problemset_Course`;

ALTER TABLE `Courses`
  CHANGE COLUMN `problemset_id` `problemset_id` int(11) NOT NULL,
  ADD KEY `fk_cup_problemset_id` (`problemset_id`),
  ADD CONSTRAINT `fk_cup_problemset_id` FOREIGN KEY (`problemset_id`) REFERENCES `Problemsets` (`problemset_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

