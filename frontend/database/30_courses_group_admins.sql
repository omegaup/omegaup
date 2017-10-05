-- Courses
ALTER TABLE `Courses`
  ADD COLUMN `admin_group_id` int(11) NULL AFTER `group_id`;

DELIMITER $$
CREATE PROCEDURE `Courses_Group_Admins`()
BEGIN
  DECLARE done INT DEFAULT FALSE;
  DECLARE aclid, groupid, courseid INT(11);
  DECLARE coursealias, groupalias VARCHAR(32);
  DECLARE cur CURSOR FOR SELECT `course_id`, `acl_id`, `alias` FROM `Courses`;

  DECLARE EXIT HANDLER FOR SQLEXCEPTION ROLLBACK;
  DECLARE EXIT HANDLER FOR SQLWARNING ROLLBACK;
  DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

  START TRANSACTION;
  OPEN cur;

  read_loop: LOOP
    FETCH cur INTO courseid, aclid, coursealias;
    IF done THEN
      LEAVE read_loop;
    END IF;

    SET groupalias = CONCAT('admins-', coursealias);
    INSERT INTO `Groups` (`acl_id`, `alias`, `name`, `description`) VALUES (aclid, groupalias, groupalias, groupalias);
    SET groupid = LAST_INSERT_ID();
    INSERT INTO `Group_Roles` (`group_id`, `role_id`, `acl_id`) VALUES (groupid, 1, aclid); # 1 = ADMIN_ROLE
    UPDATE `Courses` SET `admin_group_id` = groupid WHERE `course_id` = courseid;
  END LOOP read_loop;

  CLOSE cur;
  COMMIT;
END$$
DELIMITER ;

CALL `Courses_Group_Admins`();

DROP PROCEDURE `Courses_Group_Admins`;
