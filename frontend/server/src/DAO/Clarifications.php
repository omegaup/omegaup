<?php

namespace OmegaUp\DAO;

/**
 * Clarifications Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\Clarifications}.
 *
 * @psalm-type Clarification=array{answer: null|string, assignment_alias?: null|string, author: string, author_classname: string, clarification_id: int, contest_alias?: null|string, message: string, problem_alias: string, public: bool, receiver: null|string, receiver_classname?: string, time: \OmegaUp\Timestamp}
 */
class Clarifications extends \OmegaUp\DAO\Base\Clarifications {
    /**
     * @return array{clarifications: list<Clarification>, totalRows: int}
     */
    final public static function getProblemsetClarifications(
        ?\OmegaUp\DAO\VO\Contests $contest,
        ?\OmegaUp\DAO\VO\Courses $course,
        bool $isAdmin,
        \OmegaUp\DAO\VO\Identities $currentIdentity,
        ?int $offset,
        int $rowcount
    ): array {
        if (!is_null($contest)) {
            $sqlProblemsets = '
            SELECT
                problemset_id,
                "" AS assignment_alias
            FROM
                Problemsets
            WHERE
                contest_id = ?
            ';
            $params = [$contest->contest_id];
        } elseif (!is_null($course)) {
            $sqlProblemsets = '
            SELECT
                problemset_id,
                alias AS assignment_alias
            FROM
                Assignments
            WHERE
                course_id = ?
            ';
            $params = [$course->course_id];
        } else {
            // This shouldn't happen!
            return [
                'totalRows' => 0,
                'clarifications' => [],
            ];
        }

        $sqlFrom = "
            FROM
                ($sqlProblemsets) ps
            INNER JOIN
                Clarifications cl ON cl.problemset_id = ps.problemset_id
            INNER JOIN
                Identities i ON i.identity_id = cl.author_id
            LEFT JOIN
                Identities r ON r.identity_id = cl.receiver_id
            INNER JOIN
                Problems p ON p.problem_id = cl.problem_id
            ";

        if (!$isAdmin) {
            $sqlFrom .= '
                AND (
                    cl.public = 1
                    OR cl.author_id = ?
                    OR cl.receiver_id = ?
                )';
            $params[] = $currentIdentity->identity_id;
            $params[] = $currentIdentity->identity_id;
        }

        $sqlOrderBy = '
            ORDER BY
                cl.answer IS NULL DESC,
                cl.clarification_id DESC
        ';

        $sqlCount = '
            SELECT
                COUNT(*)
        ';

        $sql = '
            SELECT
                cl.clarification_id,
                ps.assignment_alias AS assignment_alias,
                p.alias AS problem_alias,
                i.username AS author,
                r.username AS receiver,
                cl.message,
                cl.answer,
                cl.`time`,
                cl.public,
                IFNULL(
                    (
                        SELECT
                            ur.classname
                        FROM
                            User_Rank ur
                        WHERE
                            ur.user_id = i.user_id
                    ),
                    "user-rank-unranked"
                ) AS author_classname,
                IFNULL(
                    (
                        SELECT
                            ur.classname
                        FROM
                            User_Rank ur
                        WHERE
                            ur.user_id = r.user_id
                    ),
                    "user-rank-unranked"
                ) AS receiver_classname
        ';

        $query = $sql . $sqlFrom;
        $countQuery = $sqlCount . $sqlFrom;

        $sqlLimit = '';
        if (!is_null($offset)) {
            $sqlLimit = 'LIMIT ?, ?';
            $params[] = max(0, $offset - 1) * $rowcount;
            $params[] = $rowcount;
        }

        $query .= $sqlOrderBy . $sqlLimit;

        /** @var list<array{answer: null|string, assignment_alias: string, author: string, author_classname: string, clarification_id: int, message: string, problem_alias: string, public: bool, receiver: null|string, receiver_classname: string, time: \OmegaUp\Timestamp}> */
        $clarifications = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $query,
            $params
        );

        // If we didn't get an offset, we know the total number of rows
        // already, no need to query the database for it.
        $totalRows = count($clarifications);
        if (!is_null($offset) && $offset != 0) {
            if ($totalRows != $rowcount) {
                // If we did get an offset, but the number of rows we got is
                // less than what we allowed, we've already reached the end
                // of the list so just bump up the count to account for the
                // starting position.
                $totalRows += ($offset - 1) * $rowcount;
            } else {
                // If we exhausted the maximum number of rows to fetch it's
                // possible there are more rows than we know about at this
                // point, thus we need to actually query the database.
                /** @var int */
                $totalRows = \OmegaUp\MySQLConnection::getInstance()->GetOne(
                    $countQuery,
                    $params
                ) ?? 0;
            }
        }

        return [
            'totalRows' => $totalRows,
            'clarifications' => $clarifications,
        ];
    }

    /**
     * @return list<Clarification>
     */
    final public static function getProblemInProblemsetClarifications(
        \OmegaUp\DAO\VO\Problems $problem,
        int $problemsetId,
        bool $admin,
        \OmegaUp\DAO\VO\Identities $currentIdentity,
        ?int $offset,
        int $rowcount
    ) {
        $sql = '
            SELECT
                c.clarification_id,
                p.alias AS problem_alias,
                i.username AS author,
                r.username AS receiver,
                c.message,
                c.answer,
                c.`time`,
                c.public,
                IFNULL(
                    (
                        SELECT
                            ur.classname
                        FROM
                            User_Rank ur
                        WHERE
                            ur.user_id = i.user_id
                    ),
                    "user-rank-unranked"
                ) AS author_classname,
                IFNULL(
                    (
                        SELECT
                            ur.classname
                        FROM
                            User_Rank ur
                        WHERE
                            ur.user_id = r.user_id
                    ),
                    "user-rank-unranked"
                ) AS receiver_classname
            FROM
                Clarifications c
            INNER JOIN
                Identities i ON i.identity_id = c.author_id
            LEFT JOIN
                Identities r ON r.identity_id = c.receiver_id
            INNER JOIN
                Problems p ON p.problem_id = c.problem_id
            WHERE
                c.problemset_id = ?
                AND p.problem_id = ?
        ';

        $params = [
            $problemsetId,
            $problem->problem_id
        ];

        if (!$admin) {
            $sql .= '
                AND (
                    c.public = 1 OR c.author_id = ? OR c.receiver_id = ?
                )
            ';
            $params[] = $currentIdentity->identity_id;
            $params[] = $currentIdentity->identity_id;
        }

        $sql .= '
            ORDER BY c.answer IS NULL DESC,
            c.clarification_id DESC
        ';

        if (!is_null($offset)) {
            $sql .= 'LIMIT ?, ?';
            $params[] = max(0, $offset);
            $params[] = $rowcount;
        }

        /** @var list<array{answer: null|string, author: string, author_classname: string, clarification_id: int, message: string, problem_alias: string, public: bool, receiver: null|string, receiver_classname: string, time: \OmegaUp\Timestamp}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            $params
        );
    }

    /**
     * @return list<Clarification>
     */
    final public static function GetProblemClarifications(
        int $problemId,
        bool $admin,
        int $identityId,
        ?int $offset,
        int $rowcount
    ) {
        $sql = '';
        if ($admin) {
            $sql = '
                SELECT
                    c.clarification_id,
                    p.alias AS problem_alias,
                    con.alias AS contest_alias,
                    i.username AS author,
                    r.username `receiver`,
                    c.message,
                    c.answer,
                    c.`time`,
                    c.public,
                    IFNULL(
                        (
                            SELECT
                                ur.classname
                            FROM
                                User_Rank ur
                            WHERE
                                ur.user_id = i.user_id
                        ),
                        "user-rank-unranked"
                    ) AS author_classname,
                    IFNULL(
                        (
                            SELECT
                                ur.classname
                            FROM
                                User_Rank ur
                            WHERE
                                ur.user_id = r.user_id
                        ),
                        "user-rank-unranked"
                    ) AS receiver_classname
                FROM
                    Clarifications c
                INNER JOIN
                    Identities i ON i.identity_id = c.author_id
                LEFT JOIN
                    Identities r ON r.identity_id = c.receiver_id
            ';
        } else {
            $sql = '
                SELECT
                    c.clarification_id,
                    p.alias AS problem_alias,
                    con.alias AS contest_alias,
                    "" AS author,
                    CAST(NULL AS CHAR) AS receiver,
                    c.message,
                    c.answer,
                    c.`time`,
                    c.public,
                    "user-rank-unranked" AS author_classname,
                    "user-rank-unranked" AS receiver_classname
                FROM Clarifications c
            ';
        }
        $sql .= '
            INNER JOIN
              `Problems` p ON p.problem_id = c.problem_id
            LEFT JOIN
                Contests con ON con.problemset_id = c.problemset_id
            WHERE
                c.problem_id = ?
        ';
        $val = [$problemId];

        if (!$admin) {
            $sql .= 'AND (c.public = 1 OR c.author_id = ?) ';
            $val[] = $identityId;
        }

        $sql .= 'ORDER BY c.answer IS NULL DESC, c.clarification_id DESC ';
        if (!is_null($offset)) {
            $sql .= 'LIMIT ?, ?';
            $val[] = max(0, $offset);
            $val[] = $rowcount;
        }

        $result = [];
        /** @var array{answer: null|string, author: string, author_classname: string, clarification_id: int, contest_alias: null|string, message: string, problem_alias: string, public: bool, receiver: null|string, receiver_classname: string, time: \OmegaUp\Timestamp} $row */
        foreach (
            \OmegaUp\MySQLConnection::getInstance()->GetAll(
                $sql,
                $val
            ) as $row
        ) {
            if (!$admin) {
                $row['receiver'] = null;
            }
            $result[] = $row;
        }

        return $result;
    }
}
