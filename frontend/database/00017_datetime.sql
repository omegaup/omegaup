ALTER TABLE `Problemset_Users`
  MODIFY COLUMN `access_time` datetime NULL DEFAULT NULL COMMENT 'Hora a la que entró el usuario al concurso';

DELIMITER $$
CREATE PROCEDURE `Problemset_Users_Migrate`()
BEGIN
  DECLARE sentinel VARCHAR(20);

  SET sentinel = '0000-00-00 00:00:00';

  -- MySQL 5.7+ considers directly assigning `sentinel` to a `DATETIME` column
  -- to be a fatal error. Guard against it by checking if it's a valid value
  -- with the DAY() function.
  IF DAY(sentinel) IS NOT NULL THEN
    UPDATE `Problemset_Users` SET `access_time` = NULL WHERE `access_time` = sentinel;
  END IF;
END$$
DELIMITER ;

CALL `Problemset_Users_Migrate`();
DROP PROCEDURE `Problemset_Users_Migrate`;

ALTER TABLE `Problemset_User_Request`
  MODIFY COLUMN `last_update` timestamp NULL DEFAULT NULL;
