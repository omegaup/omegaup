<?php

namespace OmegaUp\DAO;

/**
 * Submissions Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\Submissions}.
 *
 * @access public
 */
class Submissions extends \OmegaUp\DAO\Base\Submissions {
    final public static function getByGuid(string $guid): ?\OmegaUp\DAO\VO\Submissions {
        $sql = 'SELECT * FROM Submissions WHERE (guid = ?) LIMIT 1;';
        $params = [$guid];

        $rs = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($rs)) {
            return null;
        }

        return new \OmegaUp\DAO\VO\Submissions($rs);
    }

    final public static function disqualify(string $guid): void {
        $sql = '
            UPDATE
                Submissions s
            SET
                s.type = "disqualified"
            WHERE
                s.guid = ?;
        ';
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, [$guid]);
    }

    /**
     * Gets the count of total submissions sent to a given problem
     */
    final public static function countTotalSubmissionsOfProblem(
        int $problemId
    ): int {
        $sql = '
            SELECT
                COUNT(*)
            FROM
                Submissions s
            WHERE
                s.problem_id = ? AND s.`type` = "normal";
        ';
        $val = [$problemId];

        return \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, $val);
    }

    /**
     * Get the count of submissions of a problem in a given problemset
     *
     * @param int $problemId
     * @param int $problemsetId
     */
    final public static function countTotalRunsOfProblemInProblemset(
        int $problemId,
        int $problemsetId
    ): int {
        $sql = '
            SELECT
                COUNT(*)
            FROM
                Submissions
            WHERE
                problem_id = ? AND problemset_id = ? AND `type` = "normal";
        ';
        $val = [$problemId, $problemsetId];

        return \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, $val);
    }

    /**
     * Gets the count of total runs sent to a given problemset
     */
    final public static function countTotalSubmissionsOfProblemset(
        int $problemsetId
    ): int {
        $sql = '
            SELECT
                COUNT(*)
            FROM
                Submissions
            WHERE
                problemset_id = ? AND `type` = "normal";
        ';
        $val = [$problemsetId];

        return \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, $val);
    }

    /**
     * Get last submission time of a user.
     */
    final public static function getLastSubmissionTime(
        int $identityId,
        int $problemId,
        ?int $problemsetId
    ): ?int {
        if (is_null($problemsetId)) {
            $sql = '
                SELECT
                    UNIX_TIMESTAMP(MAX(s.time)) AS time
                FROM
                    Submissions s
                WHERE
                    s.identity_id = ? AND s.problem_id = ?
                ORDER BY
                    s.time DESC
                LIMIT 1;
            ';
            $val = [$identityId, $problemId];
        } else {
            $sql = '
                SELECT
                    UNIX_TIMESTAMP(MAX(s.time)) AS time
                FROM
                    Submissions s
                WHERE
                    s.identity_id = ? AND s.problem_id = ? AND s.problemset_id = ?
                ORDER BY
                    s.time DESC
                LIMIT 1;
            ';
            $val = [$identityId, $problemId, $problemsetId];
        }

        return \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, $val);
    }

    public static function countAcceptedSubmissions(
        int $startTimestamp,
        int $endTimestamp
    ): int {
        $sql = '
            SELECT
                COUNT(s.submission_id)
            FROM
                Submissions s
            INNER JOIN
                Runs r ON r.run_id = s.current_run_id
            WHERE
                r.verdict = "AC"
                AND s.time BETWEEN FROM_UNIXTIME(?) AND FROM_UNIXTIME(?);
';
        /** @var int */
        return \OmegaUp\MySQLConnection::getInstance()->GetOne(
            $sql,
            [$startTimestamp, $endTimestamp]
        );
    }

    /**
     * @return list<array{time: int, username: string, school_id: int, school_name: string, alias: string, title: string, language: string, verdict: string, runtime: int, memory: int}>
     */
    public static function getLatestSubmissions(
        int $page,
        int $rowcount,
        int $seconds = 3600 * 24
    ): array {
        $offset = ($page - 1) * $rowcount;
        $sql = '
            SELECT
                UNIX_TIMESTAMP(s.time) as time,
                i.username,
                s.school_id,
                sc.name as school_name,
                p.alias,
                p.title,
                s.language,
                r.verdict,
                r.runtime,
                r.memory,
                COALESCE (
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
                Submissions s
            INNER JOIN
                Identities i ON i.identity_id = s.identity_id
            INNER JOIN
                Problems p ON p.problem_id = s.problem_id
            INNER JOIN
                Runs r ON r.run_id = s.current_run_id
            INNER JOIN
                Users u ON u.main_identity_id = i.identity_id
            LEFT JOIN
                Schools sc ON sc.school_id = s.school_id
            LEFT JOIN
                Problemsets ps ON ps.problemset_id = s.problemset_id
            LEFT JOIN
                Contests c ON c.contest_id = ps.contest_id
            WHERE
                TIMESTAMPDIFF(SECOND, s.time, NOW()) <= ?
                AND u.is_private = 0
                AND p.visibility >= ?
                AND (
                    s.problemset_id IS NULL
                    OR ps.access_mode = "public"
                )
                AND (
                    c.contest_id IS NULL
                    OR c.finish_time < s.time
                )
            ORDER BY
                s.time DESC
            LIMIT
                ?, ?;';

        /** @var list<array{time: int, username: string, school_id: int, school_name: string, alias: string, title: string, language: string, verdict: string, runtime: int, memory: int}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [
                $seconds,
                \OmegaUp\ProblemParams::VISIBILITY_PUBLIC,
                $offset,
                $rowcount
            ]
        );
    }
}
