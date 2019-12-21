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
     * Returns the rank of schools based on the sum of the score of each problem solved by the users of each school
     *
     * @return list<array{school_id: int, name: string, country_id: string, score: float}>
     */
    public static function getRankByProblemsScore(
        int $startDate,
        int $finishDate,
        int $offset,
        int $rowcount
    ): array {
        $sql = '
            SELECT
                s.school_id,
                s.name,
                s.country_id,
                SUM(ROUND(100 / LOG(2, distinct_school_problems.accepted+1), 0)) AS score
            FROM
                Schools s
            INNER JOIN
                (
                    SELECT
                        su.school_id,
                        p.accepted,
                        MIN(su.time) AS first_ac_time
                    FROM
                        Submissions su
                    INNER JOIN
                        Runs r ON r.run_id = su.current_run_id
                    INNER JOIN
                        Problems p ON p.problem_id = su.problem_id
                    WHERE
                        r.verdict = "AC"
                        AND p.visibility >= 1
                        AND su.school_id IS NOT NULL
                    GROUP BY
                        su.school_id,
                        su.problem_id
                    HAVING
                        first_ac_time BETWEEN CAST(FROM_UNIXTIME(?) AS DATETIME) AND CAST(FROM_UNIXTIME(?) AS DATETIME)
                ) AS distinct_school_problems
            ON
                distinct_school_problems.school_id = s.school_id
            GROUP BY
                s.school_id
            ORDER BY
                score DESC
            LIMIT ?, ?;';

        $args = [$startDate, $finishDate, $offset, $rowcount];

        /** @var list<array{school_id: int, name: string, country_id: string, score: float}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            $args
        );
    }

    /**
     * @param int $schoolId
     * @param int $monthsNumber
     * @return array{year: int, month: int, count: int}[]
     */
    public static function getMonthlySolvedProblemsCount(
        int $schoolId,
        int $monthsNumber
    ): array {
        $sql = '
        SELECT
            YEAR(su.time) AS year,
            MONTH(su.time) AS month,
            COUNT(DISTINCT su.problem_id) AS `count`
        FROM
            Submissions su
        INNER JOIN
            Schools sc ON sc.school_id = su.school_id
        INNER JOIN
            Runs r ON r.run_id = su.current_run_id
        INNER JOIN
            Problems p ON p.problem_id = su.problem_id
        WHERE
            su.school_id = ? AND su.time >= CURDATE() - INTERVAL ? MONTH
            AND r.verdict = "AC" AND p.visibility >= 1
            AND NOT EXISTS (
                SELECT
                    *
                FROM
                    Submissions sub
                INNER JOIN
                    Runs ru ON ru.run_id = sub.current_run_id
                WHERE
                    sub.problem_id = su.problem_id
                    AND sub.identity_id = su.identity_id
                    AND ru.verdict = "AC"
                    AND sub.time < su.time
            )
        GROUP BY
            YEAR(su.time),
            MONTH(su.time)
        ORDER BY
            YEAR(su.time) ASC,
            MONTH(su.time) ASC;';

        $params = [$schoolId, $monthsNumber];

        /** @var array{year: int, month: int, count: int}[] */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            $params
        );
    }

    /**
     * Gets the schools ordered by rank and score
     *
     * @return array{rank: list<array{school_id: int, country_id: int, rank: int, score: float, name: string}>, totalRows: int}
     */
    public static function getRank(
        int $page,
        int $rowsPerPage
    ): array {
        $offset = ($page - 1) * $rowsPerPage;

        $sqlFrom = '
            FROM
                Schools s
            ORDER BY
                s.rank ASC
        ';

        $sqlCount = '
            SELECT
                COUNT(*)';

        $sql = '
            SELECT
                s.school_id,
                s.name,
                s.rank,
                s.score,
                s.country_id';

        $sqlLimit = ' LIMIT ?, ?';

        /** @var int */
        $totalRows = \OmegaUp\MySQLConnection::getInstance()->GetOne(
            $sqlCount . $sqlFrom,
            []
        ) ?? 0;

        /** @var list<array{school_id: int, country_id: int, rank: int, score: float, name: string}> */
        $rank = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql . $sqlFrom . $sqlLimit,
            [$offset, $rowsPerPage]
        );

        return [
            'totalRows' => $totalRows,
            'rank' => $rank,
        ];
    }

    /**
     * Gets the users from school, and their number of problems created, solved and
     * organized contests.
     *
     * @param int $schoolId
     * @return array{username: string, classname: string, created_problems: int, solved_problems: int, organized_contests: int}[]
     */
    public static function getUsersFromSchool(
        int $schoolId
    ): array {
        $sql = '
        SELECT
            i.username,
            COALESCE (
                (SELECT urc.classname
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
                LIMIT 1)
            , "user-rank-unranked") AS classname,
            (
                SELECT
                    COUNT(DISTINCT Problems.problem_id)
                FROM
                    Users
                INNER JOIN
                    ACLs ON ACLs.owner_id = Users.user_id
                INNER JOIN
                    Problems ON Problems.acl_id = ACLs.acl_id
                WHERE
                    Problems.visibility = ? AND
                    Users.main_identity_id = i.identity_id
            ) AS created_problems,
            (
                SELECT
                    COUNT(DISTINCT Problems.problem_id)
                FROM
                    Problems
                INNER JOIN
                    Submissions ON Submissions.problem_id = Problems.problem_id
                INNER JOIN
                    Runs ON Runs.run_id = Submissions.current_run_id
                WHERE
                    Runs.verdict = "AC"
                    AND Submissions.identity_id = i.identity_id
                    AND Submissions.type = "normal"
            ) AS solved_problems,
            (
                SELECT
                    COUNT(DISTINCT Contests.contest_id)
                FROM
                    Contests
                INNER JOIN
                    ACLs ON ACLs.acl_id = Contests.acl_id
                INNER JOIN
                    Users ON Users.user_id = ACLs.owner_id
                INNER JOIN
                    Problemsets ON Problemsets.problemset_id = Contests.problemset_id
                WHERE
                    Users.main_identity_id = i.identity_id
            ) AS organized_contests
        FROM
            Schools sc
        INNER JOIN
            Identities_Schools isc ON isc.school_id = sc.school_id
        INNER JOIN
            Identities i ON i.current_identity_school_id = isc.identity_school_id
        WHERE
            sc.school_id = ?;';

        /** @var array{username: string, classname: string, created_problems: int, solved_problems: int, organized_contests: int}[] */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [\OmegaUp\Controllers\Problem::VISIBILITY_PUBLIC, $schoolId]
        );
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
                        isc.school_id,
                        COUNT(DISTINCT i.identity_id) AS distinct_identities
                    FROM
                        Submissions s
                    INNER JOIN
                        Identities i ON i.identity_id = s.identity_id
                    LEFT JOIN
                        Identities_Schools isc ON isc.identity_school_id = i.current_identity_school_id
                    WHERE
                        s.time BETWEEN FROM_UNIXTIME(?) AND FROM_UNIXTIME(?)
                    GROUP BY
                        isc.school_id
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
