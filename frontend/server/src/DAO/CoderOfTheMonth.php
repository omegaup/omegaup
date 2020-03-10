<?php

namespace OmegaUp\DAO;

/**
 * CoderOfTheMonth Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\CoderOfTheMonth}.
 *
 * @author alanboy
 * @access public
 * @package docs
 */
class CoderOfTheMonth extends \OmegaUp\DAO\Base\CoderOfTheMonth {
    /**
     * Gets the users that solved the most problems during the provided
     * time period.
     *
     * @return null|list<array{user_id: int, username: string, country_id: string, school_id: int|null, ProblemsSolved: int, score: float, classname: string}>
     */
    public static function calculateCoderOfTheMonth(
        string $startTime,
        string $endTime,
        string $category = 'all'
    ): ?array {
        $genderClause = ($category == 'female') ? " AND i.gender = 'female'" : '';
        $sql = "
          SELECT DISTINCT
            IFNULL(i.user_id, 0) AS user_id,
            i.username,
            IFNULL(i.country_id, 'xx') AS country_id,
            isc.school_id,
            COUNT(ps.problem_id) ProblemsSolved,
            IFNULL(SUM(ROUND(100 / LOG(2, ps.accepted+1) , 0)), 0) AS score,
            IFNULL(
                (
                    SELECT urc.classname FROM
                        User_Rank_Cutoffs urc
                    WHERE
                        urc.score <= (
                                SELECT
                                    ur.score
                                FROM
                                    User_Rank ur
                                WHERE
                                    ur.user_id = i.user_id
                            )
                    ORDER BY
                        urc.percentile ASC
                    LIMIT
                        1
                ),
                'user-rank-unranked'
            ) AS classname
          FROM
            (
              SELECT DISTINCT
                s.identity_id, s.problem_id
              FROM
                Submissions s
              INNER JOIN
                Runs r
              ON
                r.run_id = s.current_run_id
              WHERE
                r.verdict = 'AC' AND s.type= 'normal' AND
                s.time >= ? AND s.time <= ?
            ) AS up
          INNER JOIN
            Problems ps ON ps.problem_id = up.problem_id and ps.visibility >= 1
          INNER JOIN
            Identities i ON i.identity_id = up.identity_id
          LEFT JOIN
            Identities_Schools isc ON isc.identity_school_id = i.current_identity_school_id
          LEFT JOIN
            (
              SELECT
                user_id,
                MAX(time) latest_time,
                selected_by
              FROM
                Coder_Of_The_Month
              WHERE
                category = ?
              GROUP BY
                user_id,
                selected_by
            ) AS cm on i.user_id = cm.user_id
          WHERE
            (cm.user_id IS NULL OR DATE_ADD(cm.latest_time, INTERVAL 1 YEAR) < ?) AND
            i.user_id IS NOT NULL
            {$genderClause}
          GROUP BY
            up.identity_id
          ORDER BY
            score DESC,
            ProblemsSolved DESC
          LIMIT 100
        ";

        $val = [$startTime, $endTime, $category, $endTime];

        /** @var list<array{ProblemsSolved: int, classname: string, country_id: string, school_id: int|null, score: float, user_id: int, username: string}> */
        $results = \OmegaUp\MySQLConnection::getInstance()->getAll($sql, $val);
        if (empty($results)) {
            return null;
        }
        return $results;
    }

    /**
     * Get all first coders of the month
     * @return list<array{time: string, username: string, country_id: string, email: string|null}>
     */
    final public static function getCodersOfTheMonth(string $category = 'all'): array {
        $sql = "
            SELECT
                cm.time,
                i.username,
                IFNULL(i.country_id, 'xx') AS country_id,
                e.email
            FROM
                Coder_Of_The_Month cm
            INNER JOIN
                Users u ON u.user_id = cm.user_id
            INNER JOIN
                Identities i ON i.identity_id = u.main_identity_id
            LEFT JOIN
                Emails e ON e.user_id = u.user_id
            WHERE
                (cm.selected_by IS NOT NULL
                OR (
                    cm.`rank` = 1 AND
                    NOT EXISTS (
                        SELECT
                            *
                        FROM
                            Coder_Of_The_Month
                        WHERE
                            time = cm.time AND selected_by IS NOT NULL
                    )
                ))
                AND cm.category = ?
            ORDER BY
                cm.time DESC;
        ";

        /** @var list<array{country_id: string, email: null|string, time: string, username: string}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$category]
        );
    }

    /**
     * Gets all coders of the month from a certain school
     *
     * @return list<array{time: string, username: string, classname: string}>
     */
    final public static function getCodersOfTheMonthFromSchool(
        int $schoolId,
        string $category = 'all'
    ): array {
        $sql = "
            SELECT
              cm.time,
              i.username,
              IFNULL(
                (
                  SELECT urc.classname
                  FROM User_Rank_Cutoffs urc
                  WHERE
                      urc.score <= (
                          SELECT
                              ur.score
                          FROM
                              User_Rank ur
                          WHERE
                              ur.user_id = i.user_id
                      )
                  ORDER BY
                      urc.percentile ASC
                  LIMIT 1
                ),
                'user-rank-unranked'
              ) AS classname
            FROM
              Coder_Of_The_Month cm
            INNER JOIN
              Users u ON u.user_id = cm.user_id
            INNER JOIN
              Identities i ON i.identity_id = u.main_identity_id
            LEFT JOIN
              Emails e ON e.user_id = u.user_id
            WHERE
              (cm.`rank` = 1 OR cm.selected_by IS NOT NULL) AND
              cm.school_id = ? AND
              cm.category = ?
            ORDER BY
              cm.time DESC;
        ";

        /** @var list<array{classname: string, time: string, username: string}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$schoolId, $category]
        );
    }

    /**
     * Get all coder of the months based on month
     * @return list<array{country_id: string, email: null|string, rank: int, time: string, user_id: int, username: string}>
     */
    final public static function getMonthlyList(
        string $firstDay,
        string $category = 'all'
    ): array {
        $date = date('Y-m-01', strtotime($firstDay));
        $sql = "
          SELECT
            cm.time,
            cm.`rank`,
            i.username,
            IFNULL(i.country_id, 'xx') AS country_id,
            e.email,
            u.user_id
          FROM
            Coder_Of_The_Month cm
          INNER JOIN
            Users u ON u.user_id = cm.user_id
          INNER JOIN
            Identities i ON u.main_identity_id = i.identity_id
          LEFT JOIN
            Emails e ON e.email_id = u.main_email_id
          WHERE
            cm.time = ? AND
            cm.category = ?
          ORDER BY
            cm.time DESC,
            cm.`rank` ASC
          LIMIT 100
        ";
        /** @var list<array{country_id: string, email: null|string, rank: int, time: string, user_id: int, username: string}> */
        return \OmegaUp\MySQLConnection::getInstance()->getAll(
            $sql,
            [$date, $category]
        );
    }

    /**
     * Get true whether user is the last Coder of the month
     */
    final public static function isLastCoderOfTheMonth(
        string $username,
        string $category = 'all'
    ): bool {
        $sql = '
          SELECT
            i.username
          FROM
            Coder_Of_The_Month cm
          INNER JOIN
            Users u ON u.user_id = cm.user_id
          INNER JOIN
            Identities i ON u.main_identity_id = i.identity_id
          WHERE
            cm.`rank` = 1 AND
            cm.category = ?
          ORDER BY
            cm.time DESC
          LIMIT 1
        ';

        /** @var array{username: string}|null */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetRow(
            $sql,
            [$category]
        );
        if (empty($rs)) {
            return false;
        }
        return $username == $rs['username'];
    }

    /**
     * @return list<\OmegaUp\DAO\VO\CoderOfTheMonth>
     */
    final public static function getByTimeAndSelected(
        string $time,
        bool $autoselected = false,
        string $category = 'all'
    ): array {
        $clause = $autoselected ? 'IS NULL' : 'IS NOT NULL';
        $sql = "SELECT
                    *
                FROM
                    Coder_Of_The_Month
                WHERE
                    `time` = ? AND
                    category = ?
                AND
                    `selected_by` {$clause};";
        /** @var list<array{coder_of_the_month_id: int, description: null|string, interview_url: null|string, rank: int, school_id: int|null, selected_by: int|null, time: string, user_id: int}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$time,$category]
        );

        $coders = [];
        foreach ($rs as $row) {
            $coders[] = new \OmegaUp\DAO\VO\CoderOfTheMonth($row);
        }
        return $coders;
    }

    /**
     * @param string $time
     * @return \OmegaUp\DAO\VO\CoderOfTheMonth[]
     */
    final public static function getByTime(
        string $time,
        string $category = 'all'
    ): array {
        $sql = 'SELECT
                    *
                FROM
                    Coder_Of_The_Month
                WHERE
                    `time` = ? AND
                    category = ?;';

        /** @var list<array{category: string, coder_of_the_month_id: int, description: null|string, interview_url: null|string, problems_solved: int, rank: int, school_id: int|null, score: float, selected_by: int|null, time: string, user_id: int}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$time, $category]
        );

        $coders = [];
        foreach ($rs as $row) {
            $coders[] = new \OmegaUp\DAO\VO\CoderOfTheMonth($row);
        }
        return $coders;
    }

    /**
     * @return null|list<array{user_id: int, username: string, country_id: string, school_id: int|null, ProblemsSolved: int, score: float, classname: string}>
     */
    public static function calculateCoderOfMonthByGivenDate(
        string $date,
        string $category = 'all'
    ): ?array {
        $date = new \DateTimeImmutable($date);
        $firstDayOfLastMonth = $date->modify('first day of last month');
        $startTime = $firstDayOfLastMonth->format('Y-m-d');
        $firstDayOfCurrentMonth = $date->modify('first day of this month');
        $endTime = $firstDayOfCurrentMonth->format('Y-m-d');
        return self::calculateCoderOfTheMonth(
            $startTime,
            $endTime,
            $category
        );
    }
}
