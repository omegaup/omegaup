<?php

namespace OmegaUp\DAO;

/**
 * Runs Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\Runs}.
 *
 * @author alanboy
 * @access public
 * @package docs
 */
class Runs extends \OmegaUp\DAO\Base\Runs {
    /**
     * Gets an array of the guids of the pending runs
     */
    final public static function getBestSolvingRunsForProblem(
        int $problemId
    ) : array {
        $sql = '
            SELECT
                i.username, s.language, r.runtime, r.memory, UNIX_TIMESTAMP(s.time) time
            FROM
                (SELECT
                    MIN(s.submission_id) submission_id, s.identity_id, r.runtime
                FROM
                    Submissions s
                INNER JOIN
                    Runs r
                ON
                    r.run_id = s.current_run_id
                INNER JOIN
                    (
                        SELECT
                            ss.identity_id, MIN(rr.runtime) AS runtime
                        FROM
                            Submissions ss
                        INNER JOIN
                            Runs rr
                        ON
                            rr.run_id = ss.current_run_id
                        WHERE
                            ss.problem_id = ? AND rr.status = "ready" AND rr.verdict = "AC" AND ss.type = "normal"
                        GROUP BY
                            ss.identity_id
                    ) AS sr ON sr.identity_id = s.identity_id AND sr.runtime = r.runtime
                WHERE
                    s.problem_id = ? AND r.status = "ready" AND r.verdict = "AC" AND s.type= "normal"
                GROUP BY
                    s.identity_id, r.runtime
                ORDER BY
                    r.runtime, submission_id
                LIMIT 0, 10) as runs
            INNER JOIN
                Identities i ON i.identity_id = runs.identity_id
            INNER JOIN
                Submissions s ON s.submission_id = runs.submission_id
            INNER JOIN
                Runs r ON r.run_id = s.current_run_id;';
        $val = [$problemId, $problemId];

        return \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $val);
    }

    /**
     * Gets an array of the guids of the pending runs
     */
    final public static function getPendingRunGuidsOfProblemset(
        int $problemsetId
    ) : array {
        $sql = '
            SELECT
                s.guid
            FROM
                Submissions s
            INNER JOIN
                Runs r
            ON
                r.run_id = s.current_run_id
            WHERE
                s.problemset_id = ? AND r.status != "ready" AND s.type = "normal";
        ';
        $val = [$problemsetId];

        $result = [];
        foreach (\OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $val) as $row) {
            $result[] = $row['guid'];
        }
        return $result;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    final public static function getAllRuns(
        ?int $problemset_id,
        ?string $status,
        ?string $verdict,
        ?int $problem_id,
        ?string $language,
        ?int $identity_id,
        ?int $offset,
        ?int $rowcount
    ) : array {
        $sql = '
            SELECT
                r.run_id, s.guid, s.language, r.status, r.verdict, r.runtime,
                r.penalty, r.memory, r.score, r.contest_score, r.judged_by,
                UNIX_TIMESTAMP(s.time) AS time, s.submit_delay, s.type, i.username, p.alias,
                i.country_id, c.alias AS contest_alias
            FROM
                Submissions s
            USE INDEX(PRIMARY)
            INNER JOIN
                Runs r
            ON
                r.run_id = s.current_run_id
            INNER JOIN
                Problems p ON p.problem_id = s.problem_id
            INNER JOIN
                Identities i ON i.identity_id = s.identity_id
            LEFT JOIN
                Contests c ON c.problemset_id = s.problemset_id
        ';
        $where = [];
        $val = [];

        if (!is_null($problemset_id)) {
            $where[] = 's.problemset_id = ?';
            $val[] = $problemset_id;
        }

        if (!is_null($status)) {
            $where[] = 'r.status = ?';
            $val[] = $status;
        }
        if (!is_null($verdict)) {
            $where[] = 'r.verdict = ?';
            $val[] = $verdict;
        }
        if (!is_null($problem_id)) {
            $where[] = 's.problem_id = ?';
            $val[] = $problem_id;
        }
        if (!is_null($language)) {
            $where[] = 's.language = ?';
            $val[] = $language;
        }
        if (!is_null($identity_id)) {
            $where[] = 's.identity_id = ?';
            $val[] = $identity_id;
        }
        if (!empty($where)) {
            $sql .= 'WHERE ' . implode(' AND ', $where) . ' ';
        }

        $sql .= 'ORDER BY s.submission_id DESC ';
        if (!is_null($offset)) {
            $sql .= 'LIMIT ?, ?';
            $val[] = (int) $offset;
            $val[] = (int) $rowcount;
        }

        return \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $val);
    }

    /*
     * Gets an array of the guids of the pending runs
     */
    final public static function getPendingRunsOfProblem(
        int $problemId
    ) : array {
        $sql = '
            SELECT
                s.guid
            FROM
                Submissions s
            INNER JOIN
                Runs r
            ON
                r.run_id = s.current_run_id
            WHERE
                s.problem_id = ? AND r.status != "ready" AND s.`type` = "normal";';
        $val = [$problemId];

        $result = [];
        foreach (\OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $val) as $row) {
            $result[] = $row['guid'];
        }
        return $result;
    }

    /**
     * Gets the count of total runs sent to a given contest by verdict
     */
    final public static function countTotalRunsOfProblemsetByVerdict(
        int $problemsetId,
        string $verdict
    ) : int {
        $sql = '
            SELECT
                COUNT(*)
            FROM
                Submissions s
            INNER JOIN
                Runs r
            ON
                r.run_id = s.current_run_id
            WHERE
                s.problemset_id = ? AND r.verdict = ? AND r.status = "ready" AND s.`type` = "normal";
        ';
        $val = [$problemsetId, $verdict];

        return \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, $val);
    }

    /**
     * Gets the count of total runs sent to a given contest by verdict
     */
    final public static function countTotalRunsOfProblemByVerdict(
        int $problemId,
        string $verdict
    ) : int {
        $sql = '
            SELECT
                COUNT(*)
            FROM
                Submissions s
            INNER JOIN
                Runs r
            ON
                s.current_run_id = r.run_id
            WHERE
                s.problem_id = ? AND r.status = "ready" AND r.verdict = ? AND s.`type` = "normal";
        ';
        $val = [$problemId, $verdict];

        return \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, $val);
    }

    /**
     * Gets the count of total runs sent to a given contest by verdict and by period of time
     */
    final public static function countRunsOfIdentityPerDatePerVerdict(
        int $identityId
    ) {
        $sql = '
            SELECT
                DATE(s.time) AS date,
                r.verdict AS verdict,
                COUNT(*) AS runs
            FROM
                Submissions s
            INNER JOIN
                Runs r
            ON
                r.run_id = s.current_run_id
            WHERE
                s.identity_id = ? AND r.status = "ready" AND s.`type` = "normal"
            GROUP BY
                date, verdict
            ORDER BY
                date ASC;
        ';
        $val = [$identityId];

        return \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $val);
    }

    /**
     * Gets the largest queued time of a run in seconds.
     */
    final public static function getLargestWaitTimeOfProblemset(
        int $problemsetId
    ) : ?array {
        $sql = '
            SELECT
                s.guid, UNIX_TIMESTAMP(s.time) AS time
            FROM
                Submissions s
            INNER JOIN
                Runs r
            ON
                r.run_id = s.current_run_id
            WHERE
                s.problemset_id = ? AND r.status != "ready" AND s.`type` = "normal"
            ORDER BY
                s.submission_id ASC
            LIMIT 1;
        ';
        $val = [$problemsetId];

        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $val);
        if (empty($row)) {
            return null;
        }
        return $row;
    }

    /**
     * Get all relevant identities for a problemset.
     *
     * @return array{identity_id: int, username: string, name: string, country_id: string, is_invited: bool}[]
     */
    final public static function getAllRelevantIdentities(
        int $problemsetId,
        int $aclId,
        bool $showAllRuns = false,
        ?string $filterUsersBy = null,
        ?int $groupId = null,
        ?bool $excludeAdmin = true
    ) : array {
        // Build SQL statement
        if ($showAllRuns) {
            if (is_null($groupId)) {
                $sql = '
                    SELECT
                        i.identity_id, i.username, i.name, i.country_id, pi.is_invited
                    FROM
                        Identities i
                    INNER JOIN
                        Problemset_Identities pi ON i.identity_id = pi.identity_id
                    WHERE
                        pi.problemset_id = ? AND
                        (i.user_id NOT IN (SELECT ur.user_id FROM User_Roles ur WHERE ur.acl_id IN (?, ?) AND ur.role_id = ?)';
                $val = [
                    $problemsetId,
                    $aclId,
                    \OmegaUp\Authorization::SYSTEM_ACL,
                    \OmegaUp\Authorization::ADMIN_ROLE,
                ];
                if ($excludeAdmin) {
                    $sql = $sql . ' AND i.user_id != (SELECT a.owner_id FROM ACLs a WHERE a.acl_id = ?)';
                    $val[] =  $aclId;
                }
                $sql = $sql . 'OR i.user_id IS NULL);';
            } else {
                $sql = '
                    SELECT
                        i.identity_id, i.username, i.name, i.country_id, 0 as is_invited
                    FROM
                        Identities i
                    INNER JOIN
                        Groups_Identities gi ON i.identity_id = gi.identity_id
                    WHERE
                        gi.group_id = ? AND
                        (i.user_id != (SELECT a.owner_id FROM ACLs a WHERE a.acl_id = ?) AND
                        i.user_id NOT IN (SELECT ur.user_id FROM User_Roles ur WHERE ur.acl_id IN (?, ?) AND ur.role_id = ?)
                        OR i.user_id IS NULL);';
                $val = [
                    $groupId,
                    $aclId,
                    $aclId,
                    \OmegaUp\Authorization::SYSTEM_ACL,
                    \OmegaUp\Authorization::ADMIN_ROLE,
                ];
            }
        } else {
            $sql = '
                SELECT
                    i.identity_id, i.username, i.name, i.country_id, 0 as is_invited
                FROM
                    Identities i
                INNER JOIN
                    (SELECT DISTINCT
                        s.identity_id
                    FROM
                        Submissions s
                    INNER JOIN
                        Runs r
                    ON
                        r.run_id = s.current_run_id
                    WHERE
                        r.verdict NOT IN (\'CE\', \'JE\') AND
                        s.problemset_id = ? AND
                        r.status = \'ready\' AND
                        s.type = \'normal\'
                    ) rc ON i.identity_id = rc.identity_id';
            $val = [$problemsetId];
            if (!is_null($filterUsersBy)) {
                $sql .= ' WHERE i.username LIKE ?';
                $val[] = $filterUsersBy . '%';
            }
            $sql .= ';';
        }

        /** @var array{identity_id: int, username: string, name: string, country_id: string, is_invited: bool}[] */
        $result = [];
        /** @var array{identity_id: int, username: string, name: string, country_id: string, is_invited: int} $row */
        foreach (\OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $val) as $row) {
            $row['is_invited'] = boolval($row['is_invited']);
            array_push($result, $row);
        }
        return $result;
    }

    /**
     * @return array{score: float, penalty: int, contest_score: float, problem_id: int, identity_id: int, type: string, time: int, submit_delay: int, guid: string}[]
     */
    final public static function getProblemsetRuns(
        \OmegaUp\DAO\VO\Problemsets $problemset,
        bool $onlyAC = false
    ) : array {
        $sql = '
            SELECT
                r.score, r.penalty, r.contest_score, s.problem_id,
                s.identity_id, s.type, UNIX_TIMESTAMP(s.time) AS time,
                s.submit_delay, s.guid
            FROM
                Problemset_Problems pp
            INNER JOIN
                Submissions s
            ON
                s.problemset_id = pp.problemset_id AND
                s.problem_id = pp.problem_id
            INNER JOIN
                Runs r
            ON
                s.current_run_id = r.run_id
            WHERE
                pp.problemset_id = ? AND
                r.status = \'ready\' AND
                s.type = \'normal\' AND ' .
                ($onlyAC ?
                    "r.verdict IN ('AC') " :
                    "r.verdict NOT IN ('CE', 'JE') "
                ) .
            ' ORDER BY s.submission_id;';

        /** @var array{score: float, penalty: int, contest_score: float, problem_id: int, identity_id: int, type: string, time: int, submit_delay: int, guid: string}[] */
        $result = [];
        /** @var array{score: float, penalty: int, contest_score: float, problem_id: int, identity_id: int, type: string, time: int, submit_delay: int, guid: string} $row */
        foreach (\OmegaUp\MySQLConnection::getInstance()->GetAll($sql, [$problemset->problemset_id]) as $row) {
            array_push($result, $row);
        }
        return $result;
    }

    /**
     * Get best contest score of a user for a problem in a problemset.
     */
    final public static function getBestProblemScoreInProblemset(
        int $problemsetId,
        int $problemId,
        int $identityId
    ) : ?float {
        $sql = '
            SELECT
                r.contest_score
            FROM
                Submissions s
            INNER JOIN
                Runs r
            ON
                s.current_run_id = r.run_id
            WHERE
                s.identity_id = ? AND s.problemset_id = ? AND s.problem_id = ? AND
                r.status = "ready" AND s.`type` = "normal"
            ORDER BY
                r.contest_score DESC, r.penalty ASC
            LIMIT 1;
        ';
        $val = [$identityId, $problemsetId, $problemId];
        return \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, $val);
    }

    /**
     * Returns best score for the given identity and problem, between 0 and 100
     */
    final public static function getBestProblemScore(
        int $problemId,
        int $identityId
    ) : ?float {
        $sql = '
            SELECT
                r.score * 100
            FROM
                Submissions s
            INNER JOIN
                Runs r
            ON
                s.current_run_id = r.run_id
            WHERE
                s.identity_id = ? AND s.problem_id = ? AND
                r.status = "ready" AND s.`type` = "normal"
            ORDER BY
                r.score DESC, r.penalty ASC
            LIMIT 1;
        ';
        $val = [$identityId, $problemId];
        return \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, $val);
    }

    final public static function getByProblemset(int $problemsetId) : array {
        $sql = '
            SELECT
                s.guid,
                s.language,
                r.verdict,
                r.contest_score,
                i.username,
                p.alias
            FROM
                Submissions s
            INNER JOIN
                Runs r
            ON
                s.current_run_id = r.run_id
            INNER JOIN
                Problems p
            ON
                p.problem_id = s.problem_id
            INNER JOIN
                Identities i
            ON
                i.identity_id = s.identity_id
            WHERE
                s.problemset_id = ?
            ORDER BY
                s.`time` DESC;
        ';

        return \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, [$problemsetId]);
    }

    final public static function getByProblem(
        int $problemId
    ) : array {
        $sql = '
            SELECT
                *
            FROM
                Submissions s
            INNER JOIN
                Runs r
            ON
                r.run_id = s.current_run_id
            WHERE
                s.problem_id = ?;
        ';
        $params = [$problemId];
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $params);
        $runs = [];
        foreach ($rs as $row) {
            array_push($runs, new \OmegaUp\DAO\VO\Runs($row));
        }
        return $runs;
    }

    final public static function getForProblemDetails(
        int $problemId,
        ?int $problemsetId,
        int $identityId
    ) : array {
        $sql = '
            SELECT
                s.guid, s.language, r.status, r.verdict, r.runtime, r.penalty,
                r.memory, r.score, r.contest_score, UNIX_TIMESTAMP(s.time) AS time,
                s.submit_delay
            FROM
                Submissions s
            INNER JOIN
                Runs r
            ON
                r.run_id = s.current_run_id
            WHERE
                s.problem_id = ? AND s.identity_id = ?
        ';
        $params = [$problemId, $identityId];
        if (!is_null($problemsetId)) {
            $sql .= ' AND s.problemset_id = ?';
            $params[] = $problemsetId;
        }
        return \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $params);
    }

    final public static function isRunInsideSubmissionGap(
        ?int $problemsetId,
        ?\OmegaUp\DAO\VO\Contests $contest,
        int $problemId,
        int $identityId
    ) : bool {
        $lastRunTime = \OmegaUp\DAO\Submissions::getLastSubmissionTime($identityId, $problemId, $problemsetId);
        if (is_null($lastRunTime)) {
            return true;
        }

        $submissionGap = \OmegaUp\Controllers\Run::$defaultSubmissionGap;
        if (!is_null($contest)) {
            // Get submissions gap
            $submissionGap = max(
                $submissionGap,
                (int)$contest->submissions_gap
            );
        }

        return \OmegaUp\Time::get() >= ($lastRunTime + $submissionGap);
    }

    /**
     * Returns the time of the next submission to the current problem
     */
    final public static function nextSubmissionTimestamp($contest) {
        $submission_gap = \OmegaUp\Controllers\Run::$defaultSubmissionGap;
        if (!is_null($contest)) {
            // Get submissions gap
            $submission_gap = max(
                $submission_gap,
                (int)$contest->submissions_gap
            );
        }
        return (\OmegaUp\Time::get() + $submission_gap);
    }

    final public static function searchWithRunIdGreaterThan(
        int $problemId,
        int $submissionId
    ) : array {
        $sql = '
            SELECT
                r.*
            FROM
                Submissions s
            INNER JOIN
                Runs r
            ON
                r.run_id = s.current_run_id
            WHERE
                s.problem_id = ? AND s.submission_id >= ?
            ORDER BY
                s.submission_id ASC;
        ';

        $result = [];
        foreach (\OmegaUp\MySQLConnection::getInstance()->GetAll($sql, [$problemId, $submissionId]) as $row) {
            array_push($result, new \OmegaUp\DAO\VO\Runs($row));
        }
        return $result;
    }

    /**
     * Recalculate the contest_score of all problemset and problem Runs
     */
    public static function recalculateScore($problemset_id, $problem_id, $current_points, $original_points) {
        $sql = '
            UPDATE
              Runs r
            INNER JOIN
              Submissions s
              ON s.submission_id = r.submission_id
            SET
              r.contest_score = r.score * ?
            WHERE
              s.problemset_id = ? AND
              s.problem_id = ?;
        ';

        $params = [
            $current_points,
            $problemset_id,
            $problem_id
        ];

        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Recalculate contest runs with the following rules:
     *
     * + If penalty_type is none then:
     *   - penalty = 0.
     * + If penalty_type is runtime then:
     *   - penalty = runtime.
     * + If penalty_type is anything else then:
     *   - penalty = submit_delay
     */
    public static function recalculatePenaltyForContest(\OmegaUp\DAO\VO\Contests $contest) {
        $penalty_type = $contest->penalty_type;
        if ($penalty_type == 'none') {
            $sql = '
                UPDATE
                    Runs r
                INNER JOIN
                    Submissions s
                    ON s.submission_id = r.submission_id
                SET
                    r.penalty = 0
                WHERE
                    s.problemset_id = ?;
            ';
        } elseif ($penalty_type == 'runtime') {
            $sql = '
                UPDATE
                    Runs r
                INNER JOIN
                    Submissions s
                    ON s.submission_id = r.submission_id
                SET
                    r.penalty = r.runtime
                WHERE
                    s.problemset_id = ?;
            ';
        } elseif ($penalty_type == 'contest_start') {
            $sql = '
                UPDATE
                    `Runs` r
                INNER JOIN
                    `Submissions` s
                    ON s.submission_id = r.run_id
                INNER JOIN `Contests` c ON (c.problemset_id = s.problemset_id)
                SET
                    r.penalty = ROUND(TIME_TO_SEC(TIMEDIFF(s.time, c.start_time))/60)
                WHERE
                    s.problemset_id = ?;
            ';
        } elseif ($penalty_type == 'problem_open') {
            $sql = '
                UPDATE
                    `Runs` r
                INNER JOIN
                    `Submissions` s
                    ON s.submission_id = r.run_id
                INNER JOIN
                    `Problemset_Problem_Opened` ppo
                    ON (ppo.problemset_id = s.problemset_id
                        AND ppo.problem_id = s.problem_id
                        AND ppo.identity_id = s.identity_id)
                SET
                    r.penalty = ROUND(TIME_TO_SEC(TIMEDIFF(s.time, ppo.open_time))/60)
                WHERE
                    s.problemset_id = ?;
            ';
        } else {
            return 0;
        }
        $params = [$contest->problemset_id];
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Creates any necessary runs for submissions against a problem, given a
     * problem version.
     *
     * @param \OmegaUp\DAO\VO\Problems $problem the problem.
     */
    final public static function createRunsForVersion(
        \OmegaUp\DAO\VO\Problems $problem
    ) : void {
        $sql = '
            INSERT IGNORE INTO
                Runs (
                    submission_id, version, verdict
                )
            SELECT
                s.submission_id, ?, "JE"
            FROM
                Submissions s
            WHERE
                s.problem_id = ?
            ORDER BY
                s.submission_id;
        ';
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, [$problem->current_version, $problem->problem_id]);
    }

    /**
     * Update the version of the non-problemset runs of a problem to the
     * current version.
     *
     * @param \OmegaUp\DAO\VO\Problems $problem the problem.
     */
    final public static function updateVersionToCurrent(\OmegaUp\DAO\VO\Problems $problem) : void {
        $sql = '
            UPDATE
                Submissions s
            INNER JOIN
                Runs r
            ON
                r.submission_id = s.submission_id
            SET
                s.current_run_id = r.run_id
            WHERE
                s.problemset_id IS NULL AND
                r.version = ? AND
                s.problem_id = ?;
        ';
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, [$problem->current_version, $problem->problem_id]);
    }

    /**
     * Gets the runs that were inserted due to a version change.
     *
     * @param \OmegaUp\DAO\VO\Problems $problem the problem.
     */
    final public static function getNewRunsForVersion(\OmegaUp\DAO\VO\Problems $problem) : array {
        $sql = '
            SELECT
                r.run_id
            FROM
                Runs r
            INNER JOIN
                Submissions s
            ON
                s.submission_id = r.submission_id
            WHERE
                r.status = "new" AND
                r.version = ? AND
                s.problem_id = ?;
        ';
        $params = [$problem->current_version, $problem->problem_id];

        $result = [];
        foreach (\OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $params) as $row) {
            $result[] = new \OmegaUp\DAO\VO\Runs($row);
        }
        return $result;
    }

    /**
     * Gets a report of the runs that could have score changes due to a version
     * change.
     *
     * @param \OmegaUp\DAO\VO\Problems $problem      the problem.
     * @param ?int     $problemsetId the optional problemset.
     * @param string   $oldVersion   the old version.
     * @param string   $newVersion   the new version.
     */
    final public static function getRunsDiffsForVersion(
        \OmegaUp\DAO\VO\Problems $problem,
        ?int $problemsetId,
        string $oldVersion,
        string $newVersion
    ) : array {
        $sql = '
            SELECT
                i.username,
                s.guid,
                s.problemset_id,
                old_runs.status AS old_status,
                old_runs.verdict AS old_verdict,
                old_runs.score AS old_score,
                new_runs.status AS new_status,
                new_runs.verdict AS new_verdict,
                new_runs.score AS new_score
            FROM
                Submissions s
            INNER JOIN
                Identities i
            ON
                i.identity_id = s.identity_id
            LEFT JOIN
                Runs old_runs
            ON
                old_runs.submission_id = s.submission_id AND
                old_runs.version = ?
            LEFT JOIN
                Runs new_runs
            ON
                new_runs.submission_id = s.submission_id AND
                new_runs.version = ?
        ';
        $params = [$oldVersion, $newVersion];

        $clauses = ['s.problem_id = ?'];
        $params[] = $problem->problem_id;

        if (is_null($problemsetId)) {
            $clauses[] = 's.problemset_id IS NULL';
        } else {
            $clauses[] = 's.problemset_id = ?';
            $params[] = $problemsetId;
        }

        $sql .= ' WHERE ' . implode(' AND ', $clauses) . ' ';
        $sql .= '
            ORDER BY
                s.submission_id
            LIMIT 0, 1000;
        ';

        $result = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $params);
        foreach ($result as &$row) {
            $row['old_score'] = floatval($row['old_score']);
            $row['new_score'] = floatval($row['new_score']);
        }
        return $result;
    }
}
