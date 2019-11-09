<?php

namespace OmegaUp\DAO;

/**
 * Schools Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\Schools}.
 *
 * @author alanboy
 * @access public
 * @package docs
 */
class Schools extends \OmegaUp\DAO\Base\Schools {
    /**
     * Finds schools that cotains 'name'
     *
     * @param string $name
     * @return list<\OmegaUp\DAO\VO\Schools>
     */
    public static function findByName($name) {
        $sql = '
            SELECT
                s.*
            FROM
                Schools s
            WHERE
                s.name LIKE CONCAT(\'%\', ?, \'%\')
            LIMIT 10';
        $args = [$name];

        $result = [];
        foreach (
            \OmegaUp\MySQLConnection::getInstance()->GetAll(
                $sql,
                $args
            ) as $row
        ) {
            $result[] = new \OmegaUp\DAO\VO\Schools($row);
        }
        return $result;
    }

    /**
     * Returns rank of schools by # of distinct users with at least one AC and # of distinct problems solved.
     *
     * @param  int $startTime
     * @param  int $finishTime
     * @param  int $offset
     * @param  int $rowcount
     * @return array
     */
    public static function getRankByUsersAndProblemsWithAC(
        int $startDate,
        int $finishDate,
        int $offset,
        int $rowcount
    ): array {
        $sql = '
            SELECT
              s.name,
              s.country_id,
              COUNT(DISTINCT i.identity_id) as distinct_users,
              COUNT(DISTINCT p.problem_id) AS distinct_problems
            FROM
              Identities i
            INNER JOIN
              Submissions su ON su.identity_id = i.identity_id
            INNER JOIN
              Runs r ON r.run_id = su.current_run_id
            INNER JOIN
              Schools s ON i.school_id = s.school_id
            INNER JOIN
              Problems p ON p.problem_id = su.problem_id
            WHERE
              r.verdict = "AC" AND p.visibility >= 1 AND
              su.time BETWEEN CAST(FROM_UNIXTIME(?) AS DATETIME) AND CAST(FROM_UNIXTIME(?) AS DATETIME)
            GROUP BY
              s.school_id
            ORDER BY
              distinct_users DESC,
              distinct_problems DESC
            LIMIT ?, ?;';

        $args = [$startDate, $finishDate, $offset, $rowcount];

        $result = [];
        foreach (
            \OmegaUp\MySQLConnection::getInstance()->GetAll(
                $sql,
                $args
            ) as $row
        ) {
            $result[] = [
                'name' => $row['name'],
                'country_id' => $row['country_id'],
                'distinct_users' => $row['distinct_users'],
                'distinct_problems' => $row['distinct_problems'],
            ];
        }

        return $result;
    }

    public static function countActiveSchools(
        int $startTimestamp,
        int $endTimestamp
    ): int {
        $sql = '
            SELECT
                COUNT(DISTINCT si.school_id)
            FROM
                (
                    SELECT
                        i.school_id,
                        COUNT(DISTINCT i.identity_id) AS distinct_identities
                    FROM
                        Submissions s
                    INNER JOIN
                        Identities i ON i.identity_id = s.identity_id
                    WHERE
                        s.time BETWEEN FROM_UNIXTIME(?) AND FROM_UNIXTIME(?)
                    GROUP BY
                        i.school_id
                    HAVING
                        distinct_identities >= 5
                ) AS si;
';
        /** @var int */
        return \OmegaUp\MySQLConnection::getInstance()->GetOne(
            $sql,
            [$startTimestamp, $endTimestamp]
        );
    }
}
