CREATE TABLE IF NOT EXISTS `Run_Counts` (
	`date` DATE NOT NULL,
	`total` int(11) NOT NULL DEFAULT 0,
	`ac_count` int(11) NOT NULL DEFAULT 0,
	PRIMARY KEY (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Guarda la cantidad de runs que se han realizado hasta la fecha.';

--
-- Rellena Run_Counts
--
DELIMITER $$
CREATE PROCEDURE Run_Counts_Backfill()
BEGIN
	TRUNCATE TABLE Run_Counts;
	SET @currDate = DATE_ADD(NOW(), INTERVAL -90 DAY);
	WHILE @currDate < NOW() DO
	    INSERT INTO
	        Run_Counts (date, total, ac_count)
      SELECT
          @currDate,
          COUNT(*) AS total,
          SUM(IF(verdict='AC', 1, 0))
      FROM
          Runs
      WHERE
          time <= @currDate;

      SET @currDate = DATE_ADD(@currDate, INTERVAL 1 DAY);
  END WHILE;
END$$
DELIMITER ;

GRANT EXECUTE ON PROCEDURE Run_Counts_Backfill TO 'omegaup'@'localhost';

--
-- Actualiza los run counts cada dia
--
DELIMITER $$
CREATE EVENT `Update_Run_Counts`
ON SCHEDULE EVERY 1 DAY STARTS NOW()
DO BEGIN
   INSERT INTO
       Run_Counts (date, total, ac_count)
	SELECT
       CURDATE(),
       COUNT(*) AS total,
       SUM(IF(verdict='AC', 1, 0))
   FROM
       Runs
   WHERE
       time <= CURDATE();
END$$
DELIMITER ;

CALL Run_Counts_Backfill();
DROP PROCEDURE Run_Counts_Backfill();
