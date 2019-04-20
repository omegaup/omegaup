<?php

require_once('base/Runs.dao.base.php');
require_once('base/Runs.vo.base.php');
/** Page-level DocBlock .
 *
 * @author alanboy
 * @package docs
 *
 */

/** Runs Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
 * almacenar de forma permanente y recuperar instancias de objetos {@link Runs }.
 * @author alanboy
 * @access public
 * @package docs
 *
 */
class RunsDAO extends RunsDAOBase {
    /**
     * Gets an array of the guids of the pending runs
     */
    final public static function getBestSolvingRunsForProblem(
        int $problemId
    ) : array {
        $sql = '
            SELECT
                i.username, r.language, r.runtime, r.memory, UNIX_TIMESTAMP(r.time) time
            FROM
                (SELECT
                    MIN(r.submission_id) submission_id, s.identity_id, r.runtime
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

        global $conn;
        return $conn->GetAll($sql, $val);
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

        global $conn;

        $result = [];
        foreach ($conn->Execute($sql, $val) as $row) {
            $result[] = $row['guid'];
        }
        return $result;
    }

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

        global $conn;
        return $conn->GetAll($sql, $val);
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

        global $conn;
        $result = [];
        foreach ($conn->Execute($sql, $val) as $row) {
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

        global $conn;
        return $conn->GetOne($sql, $val);
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

        global $conn;
        return $conn->GetOne($sql, $val);
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

        global $conn;
        return $conn->GetAll($sql, $val);
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

        global $conn;
        $row = $conn->GetRow($sql, $val);
        if (empty($row)) {
            return null;
        }
        return $row;
    }

    /**
     *  Get all relevant identities for a problemset.
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
        $log = Logger::getLogger('Scoreboard');
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
                        i.user_id NOT IN (SELECT ur.user_id FROM User_Roles ur WHERE ur.acl_id IN (?, ?) AND ur.role_id = ?)';
                $val = [
                    $problemsetId,
                    $aclId,
                    Authorization::SYSTEM_ACL,
                    Authorization::ADMIN_ROLE,
                ];
                if ($excludeAdmin) {
                    $sql = $sql . ' AND i.user_id != (SELECT a.owner_id FROM ACLs a WHERE a.acl_id = ?)';
                    $val[] =  $aclId;
                }
                $sql = $sql . ';';
            } else {
                $sql = '
                    SELECT
                        i.identity_id, i.username, i.name, i.country_id, \'0\' as is_invited
                    FROM
                        Identities i
                    INNER JOIN
                        Groups_Identities gi ON i.identity_id = gi.identity_id
                    WHERE
                        gi.group_id = ? AND
                        i.user_id != (SELECT a.owner_id FROM ACLs a WHERE a.acl_id = ?) AND
                        i.user_id NOT IN (SELECT ur.user_id FROM User_Roles ur WHERE ur.acl_id IN (?, ?) AND ur.role_id = ?);';
                $val = [
                    $groupId,
                    $aclId,
                    $aclId,
                    Authorization::SYSTEM_ACL,
                    Authorization::ADMIN_ROLE,
                ];
            }
        } else {
            $sql = '
                SELECT
                    i.identity_id, i.username, i.name, i.country_id, \'0\' as is_invited
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

        global $conn;
        $rs = $conn->Execute($sql, $val);

        $ar = [];
        foreach ($rs as $row) {
            array_push($ar, $row);
        }

        return $ar;
    }

    final public static function getProblemsetRuns(
        Problemsets $problemset,
        bool $onlyAC = false
    ) : array {
        $sql = '
            SELECT
                r.score, r.penalty, r.contest_score, s.problem_id,
                s.identity_id, s.type, UNIX_TIMESTAMP(s.time) AS time,
                s.submit_delay, s.guid
            FROM
                Submissions s
            INNER JOIN
                Runs r
            ON
                s.current_run_id = r.run_id
            WHERE
                s.problemset_id = ? AND
                r.status = \'ready\' AND
                s.type = \'normal\' AND ' .
                ($onlyAC ?
                    "r.verdict IN ('AC') " :
                    "r.verdict NOT IN ('CE', 'JE') "
                ) .
            ' ORDER BY s.submission_id;';

        $result = [];
        global $conn;
        foreach ($conn->Execute($sql, [$problemset->problemset_id]) as $row) {
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
        global $conn;
        return $conn->GetOne($sql, $val);
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
        global $conn;
        return $conn->GetOne($sql, $val);
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

        global $conn;
        return $conn->GetAll($sql, [$problemsetId]);
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
        global $conn;
        $rs = $conn->Execute($sql, $params);
        $runs = [];
        foreach ($rs as $row) {
            array_push($runs, new Runs($row));
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
        global $conn;
        return $conn->GetAll($sql, $params);
    }

    final public static function isRunInsideSubmissionGap(
        ?int $problemsetId,
        ?Contests $contest,
        int $problemId,
        int $identityId
    ) : bool {
        $lastRunTime = SubmissionsDAO::getLastSubmissionTime($identityId, $problemId, $problemsetId);
        if (is_null($lastRunTime)) {
            return true;
        }

        $submissionGap = RunController::$defaultSubmissionGap;
        if (!is_null($contest)) {
            // Get submissions gap
            $submissionGap = max(
                $submissionGap,
                (int)$contest->submissions_gap
            );
        }

        return Time::get() >= ($lastRunTime + $submissionGap);
    }

    /**
     * Returns the time of the next submission to the current problem
     */
    final public static function nextSubmissionTimestamp($contest) {
        $submission_gap = RunController::$defaultSubmissionGap;
        if (!is_null($contest)) {
            // Get submissions gap
            $submission_gap = max(
                $submission_gap,
                (int)$contest->submissions_gap
            );
        }
        return (Time::get() + $submission_gap);
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

        global $conn;
        $result = [];
        foreach ($conn->Execute($sql, [$problemId, $submissionId]) as $row) {
            array_push($result, new Runs($row));
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

        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
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
    public static function recalculatePenaltyForContest(Contests $contest) {
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
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }

    /**
     * Update the version of the runs of a problem to the current version.
     *
     * @param Problems $problem the problem.
     * @return integer the number of affected rows.
     */
    final public static function updateVersionToCurrent(Problems $problem) {
        $sql = '
            UPDATE
                Runs r
            INNER JOIN
                Submissions s
            ON
                s.submission_id = r.submission_id
            SET
                r.version = ?
            WHERE
                s.problem_id = ?;
        ';
        global $conn;
        $conn->Execute($sql, [$problem->current_version, $problem->problem_id]);
        return $conn->Affected_Rows();
    }
}
