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

        /** @var array{current_run_id: int|null, guid: string, identity_id: int, language: string, problem_id: int, problemset_id: int|null, school_id: int|null, submission_id: int, submit_delay: int, time: \OmegaUp\Timestamp, type: null|string}|null */
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

        /** @var int */
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

        /** @var int */
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

        /** @var int */
        return \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, $val);
    }

    /**
     * Get whether the to-be-created submission is within the allowed
     * submission gap.
     */
    final public static function isInsideSubmissionGap(
        ?int $problemsetId,
        ?\OmegaUp\DAO\VO\Contests $contest,
        int $problemId,
        int $identityId
    ): bool {
        // Acquire row-level locks using `FOR UPDATE` so that multiple
        // concurrent queries cannot all obtain the same submission time and
        // incorrectly insert several submissions, thinking that they were all
        // within the submission gap.
        if (is_null($problemsetId)) {
            $sql = '
                SELECT
                    MAX(s.time)
                FROM
                    Identities AS i
                LEFT JOIN
                    Submissions s ON s.identity_id = i.identity_id
                WHERE
                    i.identity_id = ? AND s.problem_id = ?
                FOR UPDATE;
            ';
            $val = [$identityId, $problemId];
        } else {
            $sql = '
                SELECT
                    MAX(s.time)
                FROM
                    Identities AS i
                LEFT JOIN
                    Submissions s ON s.identity_id = i.identity_id
                WHERE
                    i.identity_id = ? AND s.problem_id = ? AND s.problemset_id = ?
                FOR UPDATE;
            ';
            $val = [$identityId, $problemId, $problemsetId];
        }

        /** @var \OmegaUp\Timestamp|null */
        $lastRunTime = \OmegaUp\MySQLConnection::getInstance()->GetOne(
            $sql,
            $val
        );
        if (is_null($lastRunTime)) {
            return true;
        }

        $submissionGap = \OmegaUp\Controllers\Run::$defaultSubmissionGap;
        if (!is_null($contest)) {
            // Get submissions gap
            $submissionGap = max(
                $submissionGap,
                intval($contest->submissions_gap)
            );
        }

        return \OmegaUp\Time::get() >= ($lastRunTime->time + $submissionGap);
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
     * @return array{submissions: list<array{alias: string, classname: string, language: string, memory: int, runtime: int, school_id: int|null, school_name: null|string, time: \OmegaUp\Timestamp, title: string, username: string, verdict: string}>, totalRows: int}
     */
    public static function getLatestSubmissions(
        int $page,
        int $rowcount,
        int $identityId = null,
        int $seconds = 3600 * 24
    ): array {
        $offset = ($page - 1) * $rowcount;

        $sqlFrom = '
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
        ';

        $filterByUser = '
                AND i.identity_id = ?
        ';

        $sqlOrderBy = '
            ORDER BY
                s.time DESC
        ';

        $sqlCount = '
            SELECT
                COUNT(*)
        ';

        $sql = '
            SELECT
                s.`time`,
                i.username,
                s.school_id,
                sc.name as school_name,
                p.alias,
                p.title,
                s.language,
                r.verdict,
                r.runtime,
                r.memory,
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
        ';

        $sqlLimit = 'LIMIT ?, ?;';

        $query = $sql . $sqlFrom;
        $countQuery = $sqlCount . $sqlFrom;

        $params = [
            $seconds,
            \OmegaUp\ProblemParams::VISIBILITY_PUBLIC,
        ];

        if (!is_null($identityId)) {
            $countQuery .= $filterByUser;
            $query .= $filterByUser;
            $params[] = $identityId;
        }

        /** @var int */
        $totalRows = \OmegaUp\MySQLConnection::getInstance()->GetOne(
            $countQuery,
            $params
        ) ?? 0;

        $params[] = $offset;
        $params[] = $rowcount;

        $query .=  $sqlOrderBy . $sqlLimit;

        /** @var list<array{alias: string, classname: string, language: string, memory: int, runtime: int, school_id: int|null, school_name: null|string, time: \OmegaUp\Timestamp, title: string, username: string, verdict: string}> */
        $submissions = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $query,
            $params
        );

        return [
            'totalRows' => $totalRows,
            'submissions' => $submissions,
        ];
    }

    /** @return \OmegaUp\Timestamp|null */
    public static function getLastSubmissionByProblem(
        int $problemId,
        ?int $identityId
    ) {
        $params = [$problemId, $identityId];
        $sql = '
            SELECT
                s.time
            FROM
                Submissions s
            WHERE
                s.problem_id = ? AND s.identity_id = ?
            ORDER BY
                s.time DESC
            LIMIT 1
        ';

        /** @var \OmegaUp\Timestamp|null */
        return \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, $params);
    }
}
