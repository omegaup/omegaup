DELIMITER $$
ALTER EVENT `Update_Run_Counts`
ON SCHEDULE EVERY 1 DAY STARTS NOW()
DO BEGIN
   REPLACE INTO
       `Run_Counts` (`date`, `total`, `ac_count`)
   SELECT
       CURDATE() AS `date`,
       COUNT(*) AS `total`,
       IFNULL(SUM(IF(`r`.`verdict` = 'AC', 1, 0)), 0) AS `ac_count`
   FROM
       `Submissions` AS `s`
   INNER JOIN
       `Runs` AS `r` ON `r`.`run_id` = `s`.`current_run_id`
   WHERE
       `s`.`time` <= CURDATE();
END$$
DELIMITER ;
