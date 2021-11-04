-- Groups
ALTER TABLE `Groups`
  ADD COLUMN `acl_id` int(11) NULL AFTER `group_id`;

DELIMITER $$
CREATE PROCEDURE `Groups_ACLs_Migrate`()
BEGIN
  DECLARE done INT DEFAULT FALSE;
  DECLARE aclid, groupid, ownerid INT(11);
  DECLARE cur CURSOR FOR SELECT `group_id`, `owner_id` FROM `Groups`;

  DECLARE EXIT HANDLER FOR SQLEXCEPTION ROLLBACK;
  DECLARE EXIT HANDLER FOR SQLWARNING ROLLBACK;
  DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

  START TRANSACTION;
  OPEN cur;

  read_loop: LOOP
    FETCH cur INTO groupid, ownerid;
    IF done THEN
      LEAVE read_loop;
    END IF;

    INSERT INTO `ACLs` (`owner_id`) VALUES (ownerid);
    SET aclid = LAST_INSERT_ID();
    UPDATE `Groups` SET `acl_id` = aclid WHERE `group_id` = groupid;
  END LOOP read_loop;

  CLOSE cur;
  COMMIT;
END$$
DELIMITER ;

CALL `Groups_ACLs_Migrate`();

DROP PROCEDURE `Groups_ACLs_Migrate`;

ALTER TABLE `Groups`
  CHANGE COLUMN `acl_id` `acl_id` int(11) NOT NULL,
  ADD KEY `acl_id` (`acl_id`),
  ADD CONSTRAINT `fk_g_acl_id` FOREIGN KEY (`acl_id`) REFERENCES `ACLs` (`acl_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE `Groups`
  DROP FOREIGN KEY `fk_g_user_id`,
  DROP INDEX `owner_id`,
  DROP COLUMN `owner_id`;
