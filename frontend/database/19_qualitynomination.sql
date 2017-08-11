-- Problems
DROP INDEX `idx_problems_public` ON Problems;

ALTER TABLE `Problems`
  CHANGE COLUMN `public` `visibility` tinyint(1) NOT NULL DEFAULT '1' COMMENT '-1 banned, 0 private, 1 public, 2 recommended';

CREATE INDEX idx_problems_visibility ON Problems (`visibility`);

-- QualityNominations
CREATE TABLE IF NOT EXISTS `QualityNominations` (
  `qualitynomination_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT 'El usuario que nominó el problema',
  `problem_id` int(11) NOT NULL COMMENT 'El problema que fue nominado',
  `nomination` enum('promotion', 'demotion', 'dismissal') NOT NULL DEFAULT 'promotion' COMMENT 'Si se está nominando el problema a promoción, democión o dismissed',
  `contents` TEXT NOT NULL COMMENT 'Un blob json con el contenido de la nominación',
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de creacion de esta nominación',
  `status` enum('open', 'approved', 'denied') NOT NULL DEFAULT 'open' COMMENT 'El estado de la nominación',
  PRIMARY KEY (`qualitynomination_id`),
  KEY `user_id` (`user_id`),
  KEY `problem_id` (`problem_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='La cola de nominación a promoción / democión de problemas';

ALTER TABLE `QualityNominations`
  ADD CONSTRAINT `fk_qn_user_id` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_qn_problem_id` FOREIGN KEY (`problem_id`) REFERENCES `Problems` (`problem_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

-- QualityNomination_Reviewers
CREATE TABLE IF NOT EXISTS `QualityNomination_Reviewers` (
  `qualitynomination_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT 'El revisor al que fue asignado esta nominación',
  PRIMARY KEY (`qualitynomination_id`, `user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='La lista de revisores para cada nominación';

ALTER TABLE `QualityNomination_Reviewers`
  ADD CONSTRAINT `fk_qnr_qualitynomination_id` FOREIGN KEY (`qualitynomination_id`) REFERENCES `QualityNominations` (`qualitynomination_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_qnr_user_id` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

-- QualityNomination_Comments
CREATE TABLE IF NOT EXISTS `QualityNomination_Comments` (
  `qualitynomination_comment_id` int(11) NOT NULL AUTO_INCREMENT,
  `qualitynomination_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL COMMENT 'El usuario que emitió el comentario',
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de creacion de este comentario',
  `vote` tinyint(1) NOT NULL COMMENT 'El voto emitido en este comentario. En el rango de [-2, +2]',
  `contents` TEXT NOT NULL COMMENT 'El contenido de el comentario',
  PRIMARY KEY (`qualitynomination_comment_id`),
  KEY `user_id` (`user_id`),
  KEY `qualitynomination_id` (`qualitynomination_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Comentarios para una nominación';

ALTER TABLE `QualityNomination_Comments`
  ADD CONSTRAINT `fk_qnc_qualitynomination_id` FOREIGN KEY (`qualitynomination_id`) REFERENCES `QualityNominations` (`qualitynomination_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_qnc_user_id` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

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
