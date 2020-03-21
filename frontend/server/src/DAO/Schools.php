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
        /** @var array{country_id: null|string, name: string, ranking: int|null, school_id: int, score: float, state_id: null|string} $row */
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
     * @param int $schoolId
     * @param int $monthsNumber
     * @return array{year: int, month: int, count: int}[]
     */
    public static function getMonthlySolvedProblemsCount(
        int $schoolId,
        int $monthsNumber
    ): array {
        // TODO(https://github.com/omegaup/omegaup/issues/3438): Remove this.
        return [];
        $sql = '
        SELECT
            IFNULL(YEAR(su.time), 0) AS year,
            IFNULL(MONTH(su.time), 0) AS month,
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
            IFNULL(YEAR(su.time), 0),
            IFNULL(MONTH(su.time), 0)
        ORDER BY
            year ASC,
            month ASC;';

        $params = [$schoolId, $monthsNumber];

        /** @var list<array{count: int, month: int, year: int}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            $params
        );
    }

    /**
     * Gets the schools ordered by rank and score
     *
     * @return array{rank: list<array{country_id: string|null, name: string, ranking: int|null, school_id: int, score: float}>, totalRows: int}
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
                s.`ranking` IS NULL, s.`ranking` ASC
        ';

        $sqlCount = '
            SELECT
                COUNT(*)';

        $sql = '
            SELECT
                s.school_id,
                s.name,
                s.`ranking`,
                s.score,
                s.country_id';

        $sqlLimit = ' LIMIT ?, ?';

        /** @var int */
        $totalRows = \OmegaUp\MySQLConnection::getInstance()->GetOne(
            $sqlCount . $sqlFrom,
            []
        ) ?? 0;

        /** @var list<array{country_id: null|string, name: string, ranking: int|null, school_id: int, score: float}> */
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
     * @return list<array{classname: string, created_problems: int, organized_contests: int, solved_problems: int, username: string}>
     */
    public static function getUsersFromSchool(
        int $schoolId
    ): array {
        $sql = '
        SELECT
            IFNULL(i.username, "") AS `username`,
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
            ) AS classname,
            IFNULL(
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
                ),
                0
            ) AS created_problems,
            IFNULL(
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
                ),
                0
            ) AS solved_problems,
            IFNULL(
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
                ),
                0
            ) AS organized_contests
        FROM
            Schools sc
        INNER JOIN
            Identities_Schools isc ON isc.school_id = sc.school_id
        INNER JOIN
            Identities i ON i.current_identity_school_id = isc.identity_school_id
        WHERE
            sc.school_id = ?;';

        /** @var list<array{classname: string, created_problems: int, organized_contests: int, solved_problems: int, username: string}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [\OmegaUp\ProblemParams::VISIBILITY_PUBLIC, $schoolId]
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
