<?php

namespace OmegaUp\DAO;

/**
 * Schools Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\Schools}.
 * @access public
 * @package docs
 */
class Schools extends \OmegaUp\DAO\Base\Schools {
    /**
     * Finds schools that contains 'name'
     *
     * @param string $name
     * @return list<\OmegaUp\DAO\VO\Schools>
     */
    public static function findByName($name) {
        // Note: We've added an index on the name column (idx_schools_name), but using
        // LIKE '%term%' prevents MySQL from utilizing this index effectively.
        // We maintain this pattern for compatibility with existing tests and functionality,
        // though it requires a full table scan. The index will still benefit other queries.
        $sql = '
            SELECT
                ' .  \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\Schools::FIELD_NAMES,
            's'
        ) . '
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
     * @return list<array{month: int, problems_solved: int, year: int}>
     */
    public static function getMonthlySolvedProblemsCount(
        int $schoolId
    ): array {
        $sql = '
        SELECT
            IFNULL(MONTH(s.time), 0) AS month,
            s.problems_solved,
            IFNULL(YEAR(s.time), 0) AS year
        FROM
            Schools_Problems_Solved_Per_Month AS s
        WHERE
            s.school_id = ?
        ORDER BY
            year ASC,
            month ASC;';

        /** @var list<array{month: int, problems_solved: int, year: int}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [ $schoolId ]
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
        $sqlFrom = '
            FROM
                Schools s
            WHERE
                s.score != 0
        ';

        $sqlOrder = '
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
            $sql . $sqlFrom . $sqlOrder . $sqlLimit,
            [
                max(0, $page - 1) * $rowsPerPage,
                $rowsPerPage,
            ]
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
            IFNULL(members.username, "") AS `username`,
            IFNULL(ur.classname, "user-rank-unranked") AS classname,
            IFNULL(cp.created_count, 0) AS created_problems,
            IFNULL(sp.solved_count, 0) AS solved_problems,
            IFNULL(oc.organized_count, 0) AS organized_contests
        FROM
            (
                SELECT
                    i.identity_id,
                    i.username,
                    i.user_id
                FROM
                    Schools sc
                INNER JOIN
                    Identities_Schools isc ON isc.school_id = sc.school_id
                INNER JOIN
                    Identities i ON i.current_identity_school_id = isc.identity_school_id
                WHERE
                    sc.school_id = ?
            ) AS members
        LEFT JOIN
            User_Rank ur ON ur.user_id = members.user_id
        LEFT JOIN
            (
                SELECT
                    u.main_identity_id AS identity_id,
                    COUNT(DISTINCT p.problem_id) AS created_count
                FROM
                    Users u
                INNER JOIN
                    ACLs a ON a.owner_id = u.user_id
                INNER JOIN
                    Problems p ON p.acl_id = a.acl_id
                WHERE
                    p.visibility = ?
                GROUP BY
                    u.main_identity_id
            ) AS cp ON cp.identity_id = members.identity_id
        LEFT JOIN
            (
                SELECT
                    s.identity_id,
                    COUNT(DISTINCT p.problem_id) AS solved_count
                FROM
                    Problems p
                INNER JOIN
                    Submissions s ON s.problem_id = p.problem_id
                INNER JOIN
                    Runs r ON r.run_id = s.current_run_id
                WHERE
                    r.verdict = "AC"
                    AND s.type = "normal"
                GROUP BY
                    s.identity_id
            ) AS sp ON sp.identity_id = members.identity_id
        LEFT JOIN
            (
                SELECT
                    u.main_identity_id AS identity_id,
                    COUNT(DISTINCT c.contest_id) AS organized_count
                FROM
                    Contests c
                INNER JOIN
                    ACLs a ON a.acl_id = c.acl_id
                INNER JOIN
                    Users u ON u.user_id = a.owner_id
                INNER JOIN
                    Problemsets ps ON ps.problemset_id = c.problemset_id
                GROUP BY
                    u.main_identity_id
            ) AS oc ON oc.identity_id = members.identity_id
        ORDER BY
            members.username;';

        /** @var list<array{classname: string, created_problems: int, organized_contests: int, solved_problems: int, username: string}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$schoolId, \OmegaUp\ProblemParams::VISIBILITY_PUBLIC]
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
