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
        $sql = 'SELECT ' .
            join(', ', array_keys(\OmegaUp\DAO\VO\Submissions::FIELD_NAMES)) .
            ' FROM Submissions WHERE (guid = ?) LIMIT 1;';
        $params = [$guid];

        /** @var array{current_run_id: int|null, guid: string, identity_id: int, language: string, problem_id: int, problemset_id: int|null, school_id: int|null, status: string, submission_id: int, submit_delay: int, time: \OmegaUp\Timestamp, type: null|string, verdict: string}|null */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($rs)) {
            return null;
        }

        return new \OmegaUp\DAO\VO\Submissions($rs);
    }

    final public static function disqualify(
        \OmegaUp\DAO\VO\Submissions $submission
    ): void {
        $sql = '
            UPDATE
                Submissions s
            SET
                s.type = "disqualified"
            WHERE
                s.submission_id = ?;
        ';
        \OmegaUp\MySQLConnection::getInstance()->Execute(
            $sql,
            [$submission->submission_id]
        );
    }

    final public static function requalify(
        \OmegaUp\DAO\VO\Submissions $submission
    ): void {
        $sql = '
            UPDATE
                Submissions s
            SET
                s.type = "normal"
            WHERE
                s.submission_id = ?;
        ';
        \OmegaUp\MySQLConnection::getInstance()->Execute(
            $sql,
            [$submission->submission_id]
        );
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
     * Gets the count of total runs sent by students (non-admins) to a given problemset
     *
     * @param list<int> $adminsIds
     */
    final public static function countTotalStudentsSubmissionsOfProblemset(
        int $problemsetId,
        array $adminsIds
    ): int {
        $placeholder = join(',', array_fill(0, count($adminsIds), '?'));
        $sql = "
            SELECT
                COUNT(*)
            FROM
                Submissions s
            INNER JOIN
                Identities i ON i.identity_id = s.identity_id
            WHERE
                s.problemset_id = ? AND
                s.`type` = 'normal' AND
                i.user_id NOT IN ($placeholder);
        ";
        $args = array_merge(
            [ $problemsetId ],
            $adminsIds,
        );

        /** @var int */
        return \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, $args);
    }

    /**
     * Get whether the to-be-created submission is within the allowed
     * submission gap.
     */
    final public static function isInsideSubmissionGap(
        \OmegaUp\DAO\VO\Submissions $submission,
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
                    Submissions s
                WHERE
                    s.identity_id = ? AND s.problem_id = ?
                FOR UPDATE;
            ';
            $val = [$identityId, $problemId];
        } else {
            $sql = '
                SELECT
                    MAX(s.time)
                FROM
                    Submissions s
                WHERE
                    s.identity_id = ? AND s.problem_id = ? AND s.problemset_id = ?
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

        return $submission->time->time >= ($lastRunTime->time + $submissionGap);
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
            WHERE
                s.verdict = "AC"
                AND s.time BETWEEN FROM_UNIXTIME(?) AND FROM_UNIXTIME(?);
';
        /** @var int */
        return \OmegaUp\MySQLConnection::getInstance()->GetOne(
            $sql,
            [$startTimestamp, $endTimestamp]
        );
    }

    /**
     * @return list<array{alias: string, classname: string, language: string, memory: int, runtime: int, school_id: int|null, school_name: null|string, time: \OmegaUp\Timestamp, title: string, username: string, verdict: string}>
     */
    public static function getLatestSubmissions(
        int $identityId = null,
    ): array {
        if (is_null($identityId)) {
            $indexHint = 'USE INDEX(PRIMARY)';
        } else {
            $indexHint = '';
        }
        $sql = "
            SELECT
                s.`time`,
                i.username,
                s.school_id,
                sc.name as school_name,
                p.alias,
                p.title,
                s.language,
                s.verdict,
                r.runtime,
                r.memory,
                IFNULL(ur.classname, 'user-rank-unranked') AS classname
            FROM
                Submissions s $indexHint
            INNER JOIN
                Identities i ON i.identity_id = s.identity_id
            LEFT JOIN
                User_Rank ur ON ur.user_id = i.user_id
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
                TIMESTAMPDIFF(SECOND, s.time, NOW()) <= 24 * 3600
                AND s.status = 'ready'
                AND u.is_private = 0
                AND p.visibility >= ?
                AND (
                    s.problemset_id IS NULL
                    OR ps.access_mode = 'public'
                )
                AND (
                    c.contest_id IS NULL
                    OR c.finish_time < s.time
                )
        ";
        $params = [
            \OmegaUp\ProblemParams::VISIBILITY_PUBLIC,
        ];

        if (!is_null($identityId)) {
            $sql .= '
                    AND i.identity_id = ?
            ';
            $params[] = $identityId;
        }

        $sql .= '
            ORDER BY
                s.submission_id DESC
            LIMIT 0, 100;
        ';

        /** @var list<array{alias: string, classname: string, language: string, memory: int, runtime: int, school_id: int|null, school_name: null|string, time: \OmegaUp\Timestamp, title: string, username: string, verdict: string}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            $params
        );
    }

    /**
     * Gets the alias of the problem, assignment
     * and course, along with the author's user_id
     * for a certain course submission
     *
     * @return array{assignment_alias: string, author_id: int|null, course_alias: string, course_id: int, problem_alias: string}|null
     */
    public static function getCourseSubmissionInfo(
        \OmegaUp\DAO\VO\Submissions $submission,
        string $assignmentAlias,
        string $courseAlias
    ): ?array {
        $sql = '
            SELECT
                a.alias as assignment_alias,
                c.course_id,
                c.alias as course_alias,
                p.alias as problem_alias,
                i.user_id as author_id
            FROM
                Submissions s
            INNER JOIN
                Identities i ON i.identity_id = s.identity_id
            INNER JOIN
                Problems p ON p.problem_id = s.problem_id
            INNER JOIN
                Problemsets ps ON ps.problemset_id = s.problemset_id
            INNER JOIN
                Problemset_Problems pp ON pp.problemset_id = ps.problemset_id AND pp.problem_id = p.problem_id
            INNER JOIN
                Assignments a ON a.problemset_id = ps.problemset_id
            INNER JOIN
                Courses c ON c.course_id = a.course_id
            WHERE
                s.submission_id = ?
                AND a.alias = ?
                AND c.alias = ?
        ';

        /** @var array{assignment_alias: string, author_id: int|null, course_alias: string, course_id: int, problem_alias: string}|null */
        return \OmegaUp\MySQLConnection::getInstance()->getRow(
            $sql,
            [
                $submission->submission_id,
                $assignmentAlias,
                $courseAlias
            ]
        );
    }

    /**
     * @return list<\OmegaUp\DAO\VO\Submissions>
     */
    public static function getAllSubmissionsByUserContest(
        string $username,
        string $contestAlias,
        ?string $problemAlias
    ) {
        $fields =  \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\Submissions::FIELD_NAMES,
            's'
        );
        $clause = '';
        $params = [
            $username,
            $contestAlias,
        ];
        if (!is_null($problemAlias)) {
            $clause = 'AND p.alias = ?';
            $params[] = $problemAlias;
        }
        $sql = "SELECT
                    {$fields}
                FROM
                    Submissions s
                INNER JOIN
                    Identities i ON i.identity_id = s.identity_id
                INNER JOIN
                    Problems p ON p.problem_id = s.problem_id
                INNER JOIN
                    Problemsets ps ON ps.problemset_id = s.problemset_id
                INNER JOIN
                    Contests c ON c.contest_id = ps.contest_id
                WHERE
                    current_run_id IS NOT NULL
                    AND username = ?
                    AND c.alias = ?
                    {$clause}";

        /** @var list<array{current_run_id: int|null, guid: string, identity_id: int, language: string, problem_id: int, problemset_id: int|null, school_id: int|null, status: string, submission_id: int, submit_delay: int, time: \OmegaUp\Timestamp, type: null|string, verdict: string}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $params);
        $submissions = [];
        foreach ($rs as $submission) {
            $submissions[] = new \OmegaUp\DAO\VO\Submissions(
                $submission
            );
        }
        return $submissions;
    }
}
