-- User_Rank
ALTER TABLE `User_Rank`
  ADD COLUMN `state_id` char(3) DEFAULT NULL,
  ADD COLUMN `school_id` int(11) DEFAULT NULL,
  ADD CONSTRAINT `fk_ur_country_id` FOREIGN KEY (`country_id`) REFERENCES `Countries` (`country_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_ur_state_id` FOREIGN KEY (`country_id`, `state_id`) REFERENCES `States` (`country_id`, `state_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_ur_school_id` FOREIGN KEY (`school_id`) REFERENCES `Schools` (`school_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

-- Refresh_User_Rank
DROP EVENT `Refresh_User_Rank_Event`;
DROP PROCEDURE `Refresh_User_Rank`;

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
        User_Rank (user_id, rank, problems_solved_count, score, username, name, country_id, state_id, school_id)
    SELECT
        user_id,
        rank,
        problems_solved_count,
        score,
        username,
        name,
        country_id,
        state_id,
        school_id
    FROM
    (
        SELECT
            problems_solved_count,
            username,
            score,
            name,
            country_id,
            state_id,
            school_id,
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
                state_id,
                school_id,
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
                Problems ps ON ps.problem_id = up.problem_id AND ps.visibility > 0
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

DELIMITER $$
CREATE EVENT `Refresh_User_Rank_Event`
ON SCHEDULE EVERY 1 HOUR STARTS NOW()
DO BEGIN
    CALL Refresh_User_Rank();
END$$
DELIMITER ;