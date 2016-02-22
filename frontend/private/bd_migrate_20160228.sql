REATE TABLE IF NOT EXISTS `User_Rank` (
  `user_id` int(11) NOT NULL,
  `rank` int(11) NOT NULL,
  `problems_solved_count` int(11) NOT NULL DEFAULT 0,
  `score` double NOT NULL DEFAULT 0,
  `username` varchar(50) NOT NULL UNIQUE,
  `name` varchar(256) DEFAULT NULL,
  `country_id` char(3) DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  KEY (`rank`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Guarda el ranking de usuarios por problemas resueltos.';

CREATE INDEX idx_problems_public ON Problems (`public`);

--
-- Recalcula el ranking de todos los usuarios por Problemas resueltos.
--
DELIMITER $$
CREATE PROCEDURE `Refresh_User_Rank`()
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION ROLLBACK;
    DECLARE EXIT HANDLER FOR NOT FOUND ROLLBACK;
    DECLARE EXIT HANDLER FOR SQLWARNING ROLLBACK;

    START TRANSACTION;
    TRUNCATE TABLE `User_Rank`;

    SET @prev_value = NULL;
    SET @rank_count = 0;
    SET @prev_value_ties = NULL;
    SET @prev_ties_count = 0;
    SET @ties_count = 0;

    INSERT INTO
        User_Rank (user_id, rank, problems_solved_count, score, username, name, country_id)
    SELECT
        user_id,
        rank,
        problems_solved_count,
        score,
        username,
        name,
        country_id
    FROM
    (
        SELECT
            problems_solved_count,
            username,
            score,
            name,
            country_id,
            user_id,
            @prev_ties_count := @ties_count as previous_ties_count,
        CASE
            WHEN @prev_value_ties = score THEN @ties_count := @ties_count + 1
            WHEN @prev_value_ties := score THEN @ties_count := 0
        END AS ties_count,
        CASE
            WHEN @prev_value = score THEN @rank_count
            WHEN @prev_value := score THEN @rank_count := @rank_count + 1 + @prev_ties_count
        END AS rank
        FROM
        (
            SELECT
                username,
                name,
                country_id,
                up.user_id,
                COUNT(ps.problem_id) problems_solved_count,
                SUM(ROUND(100 / LOG(2, ps.accepted+1) , 0)) score
            FROM
            (
                SELECT DISTINCT
                  r.user_id,
                  r.problem_id
                FROM
                  Runs r
                WHERE
                  r.verdict = 'AC' AND r.test = 0
            ) AS up
            INNER JOIN
                Problems ps ON ps.problem_id = up.problem_id AND ps.public = 1
            INNER JOIN
                Users u ON u.user_id = up.user_id
            GROUP BY
                user_id
            ORDER BY
                score DESC
        ) AS UsersProblemsSolved
    ) AS Rank;
    COMMIT;
END$$
DELIMITER ;

GRANT EXECUTE ON PROCEDURE Refresh_User_Rank TO 'omegaup'@'localhost';

--
-- Refresca User_Rank periódicamente
--
DELIMITER $$
CREATE EVENT `Refresh_User_Rank_Event`
ON SCHEDULE EVERY 1 HOUR STARTS NOW()
DO BEGIN
    CALL Refresh_User_Rank();
END$$
DELIMITER ;

COMMIT;

