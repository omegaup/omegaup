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
     * @return null|array<int, array{user_id: int, username: string, country_id: string, school_id: int, ProblemsSolved: int, score: float, classname: string}>
     */
    public static function calculateCoderOfTheMonth(
        string $startTime,
        string $endTime
    ): ?array {
        $sql = "
          SELECT DISTINCT
            i.user_id,
            i.username,
            IFNULL(i.country_id, 'xx') AS country_id,
            isc.school_id,
            COUNT(ps.problem_id) ProblemsSolved,
            SUM(ROUND(100 / LOG(2, ps.accepted+1) , 0)) score,
            (SELECT urc.classname FROM
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
                1) classname
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
              GROUP BY
                user_id,
                selected_by
            ) AS cm on i.user_id = cm.user_id
          WHERE
            (cm.user_id IS NULL
            OR DATE_ADD(cm.latest_time, INTERVAL 1 YEAR) < ?)
          GROUP BY
            up.identity_id
          ORDER BY
            score DESC,
            ProblemsSolved DESC
          LIMIT 100
        ";

        $val = [$startTime, $endTime, $endTime];

        /** @var array<int, array{user_id: int, username: string, country_id: string, school_id: int, ProblemsSolved: int, score: float, classname: string}> */
        $results = \OmegaUp\MySQLConnection::getInstance()->getAll($sql, $val);
        if (empty($results)) {
            return null;
        }
        return $results;
    }

    /**
     * Get all first coders of the month
     * @return array{time: string, username: string, country_id: string, email: string}[]
     */
    final public static function getCodersOfTheMonth(): array {
        $sql = '
            SELECT
                cm.time,
                i.username,
                IFNULL(i.country_id, "xx") AS country_id,
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
                cm.selected_by IS NOT NULL
                OR (
                    cm.rank = 1 AND
                    NOT EXISTS (
                        SELECT
                            *
                        FROM
                            Coder_Of_The_Month
                        WHERE
                            time = cm.time AND selected_by IS NOT NULL
                    )
                )
            ORDER BY
                cm.time DESC';

        /** @var array{time: string, username: string, country_id: string, email: string}[] */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll($sql);
    }

    /**
     * Gets all coders of the month from a certain school
     * @param int $schoolId
     * @return array{time: string, username: string, classname: string}[]
     */
    final public static function getCodersOfTheMonthFromSchool(
        int $schoolId
    ): array {
        $sql = '
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
            "user-rank-unranked"
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
          (cm.rank = 1 OR cm.selected_by IS NOT NULL) AND
          cm.school_id = ?
        ORDER BY
          cm.time DESC
      ';

      /** @var array{time: string, username: string, classname: string}[] */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$schoolId]
        );
    }

    /**
     * Get all coder of the months based on month
     * @return array{time: string, username: string, rank: int, country_id: string, email: string}[]
     */
    final public static function getMonthlyList(string $firstDay): array {
        $date = date('Y-m-01', strtotime($firstDay));
        $sql = '
          SELECT
            cm.time,
            cm.rank,
            i.username,
            IFNULL(i.country_id, "xx") AS country_id,
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
            cm.time = ?
          ORDER BY
            cm.time DESC,
            cm.rank ASC
          LIMIT 100
        ';
        /** @var array{time: string, username: string, rank: int, country_id: string, email: string}[] */
        return \OmegaUp\MySQLConnection::getInstance()->getAll($sql, [$date]);
    }

    /**
     * Get true whether user is the last Coder of the month
     *
     * @static
     * @return Array
     */
    final public static function isLastCoderOfTheMonth($username) {
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
            cm.rank = 1
          ORDER BY
            cm.time DESC
          LIMIT 1
        ';

        $rs = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, []);
        if (empty($rs)) {
            return false;
        }
        return $username == $rs['username'];
    }

    final public static function getByTimeAndSelected(
        $time,
        $autoselected = false
    ) {
        $clause = $autoselected ? 'IS NULL' : 'IS NOT NULL';
        $sql = 'SELECT
                    *
                FROM
                    Coder_Of_The_Month
                WHERE
                    `time` = ?
                AND
                    `selected_by` ' . $clause . ';';
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, [$time]);

        $coders = [];
        foreach ($rs as $row) {
            array_push($coders, new \OmegaUp\DAO\VO\CoderOfTheMonth($row));
        }
        return $coders;
    }

    /**
     * @param string $time
     * @return \OmegaUp\DAO\VO\CoderOfTheMonth[]
     */
    final public static function getByTime(string $time): array {
        $sql = 'SELECT
                    *
                FROM
                    Coder_Of_The_Month
                WHERE
                    `time` = ?;';

        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, [$time]);

        $coders = [];
        foreach ($rs as $row) {
            array_push($coders, new \OmegaUp\DAO\VO\CoderOfTheMonth($row));
        }
        return $coders;
    }

    /**
     * @return null|array<int, array{user_id: int, username: string, country_id: string, school_id: int, ProblemsSolved: int, score: float, classname: string}>
     */
    public static function calculateCoderOfMonthByGivenDate(
        string $date
    ): ?array {
        $date = new \DateTimeImmutable($date);
        $firstDayOfLastMonth = $date->modify('first day of last month');
        $startTime = $firstDayOfLastMonth->format('Y-m-d');
        $firstDayOfCurrentMonth = $date->modify('first day of this month');
        $endTime = $firstDayOfCurrentMonth->format('Y-m-d');
        return self::calculateCoderOfTheMonth($startTime, $endTime);
    }
}
