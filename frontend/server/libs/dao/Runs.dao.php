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
    /*
	 * Gets an array of the guids of the pending runs
	 */

    final public static function GetPendingRuns($showAllRuns = false) {
        // Build SQL statement.
        $sql = "SELECT guid, UNIX_TIMESTAMP(time) AS time FROM Runs WHERE status != 'ready'";

        if (!$showAllRuns) {
            $sql .= ' AND `type` = \'normal\'';
        }

        $sql .= ' ORDER BY run_id;';

        global $conn;
        $rs = $conn->Execute($sql);

        $ar = [];
        foreach ($rs as $row) {
            array_push($ar, ['guid' => $row['guid'], 'time' => intval($row['time'])]);
        }

        return $ar;
    }

    /*
	 * Gets an array of the guids of the pending runs
	 */

    final public static function GetBestSolvingRunsForProblem($problem_id) {
        $sql = '
			SELECT i.username, r.language, r.runtime, r.memory, UNIX_TIMESTAMP(r.time) time FROM
				(SELECT
					MIN(r.run_id) run_id, r.identity_id, r.runtime
				FROM
					Runs r
				INNER JOIN
					(
						SELECT
							rr.identity_id, MIN(rr.runtime) AS runtime
						FROM
							Runs rr
						WHERE
							rr.problem_id = ? AND rr.verdict = \'AC\' AND rr.type = \'normal\' GROUP BY rr.identity_id
					) AS sr ON sr.identity_id = r.identity_id AND sr.runtime = r.runtime
				WHERE
					r.problem_id = ? AND r.verdict = \'AC\' AND r.type= \'normal\'
				GROUP BY
					r.identity_id, r.runtime
				ORDER
					BY r.runtime, run_id
				LIMIT 0, 10) as runs
			INNER JOIN
				Identities i ON i.identity_id = runs.identity_id
			INNER JOIN
				Runs r ON r.run_id = runs.run_id;';
        $val = [$problem_id, $problem_id];

        global $conn;
        return $conn->GetAll($sql, $val);
    }

    /*
	 * Gets an array of the guids of the pending runs
	 */

    final public static function GetPendingRunsOfProblemset($problemset_id, $showAllRuns = false) {
        // Build SQL statement.
        $sql = "SELECT guid FROM Runs WHERE problemset_id = ? AND status != 'ready'";
        $val = [$problemset_id];

        if (!$showAllRuns) {
            $sql .= ' AND `type` = \'normal\'';
        }

        global $conn;
        $rs = $conn->Execute($sql, $val);

        $ar = [];
        foreach ($rs as $foo) {
            array_push($ar, $foo['guid']);
        }

        return $ar;
    }

    final public static function GetAllRuns($problemset_id, $status, $verdict, $problem_id, $language, $identity_id, $offset, $rowcount) {
        $sql = 'SELECT r.run_id, r.guid, r.language, r.status, r.verdict, r.runtime, r.penalty, ' .
                'r.memory, r.score, r.contest_score, r.judged_by, UNIX_TIMESTAMP(r.time) AS time, ' .
                'r.submit_delay, r.type, i.username, p.alias, i.country_id, c.alias AS contest_alias ' .
                'FROM Runs r USE INDEX(PRIMARY) ' .
                'INNER JOIN Problems p ON p.problem_id = r.problem_id ' .
                'INNER JOIN Identities i ON i.identity_id = r.identity_id ' .
                'LEFT JOIN Contests c ON c.problemset_id = r.problemset_id ';
        $where = [];
        $val = [];

        if (!is_null($problemset_id)) {
            $where[] = 'r.problemset_id = ?';
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
            $where[] = 'r.problem_id = ?';
            $val[] = $problem_id;
        }
        if (!is_null($language)) {
            $where[] = 'r.language = ?';
            $val[] = $language;
        }
        if (!is_null($identity_id)) {
            $where[] = 'r.identity_id = ?';
            $val[] = $identity_id;
        }
        if (!empty($where)) {
            $sql .= 'WHERE ' . implode(' AND ', $where) . ' ';
        }

        $sql .= 'ORDER BY run_id DESC ';
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

    final public static function GetPendingRunsOfProblem($problem_id, $showAllRuns = false) {
        // Build SQL statement.
        $sql = "SELECT guid FROM Runs WHERE problem_id = ? AND status != 'ready'";
        $val = [$problem_id];

        if (!$showAllRuns) {
            $sql .= ' AND `type` = \'normal\'';
        }

        global $conn;
        $rs = $conn->Execute($sql, $val);

        $ar = [];
        foreach ($rs as $foo) {
            array_push($ar, $foo['guid']);
        }

        return $ar;
    }

    final public static function getByAlias($alias) {
        $sql = 'SELECT * FROM Runs WHERE (guid = ? ) LIMIT 1;';
        $params = [$alias];

        global $conn;
        $rs = $conn->GetRow($sql, $params);
        if (count($rs) == 0) {
            return null;
        }

        $contest = new Runs($rs);
        return $contest;
    }

    /*
	 * Gets the count of total runs sent to a given problemset
	 */
    final public static function CountTotalRunsOfProblemset($problemset_id, $showAllRuns = false) {
        // Build SQL statement.
        $sql = 'SELECT COUNT(*) FROM Runs WHERE problemset_id = ? ';
        $val = [$problemset_id];

        if (!$showAllRuns) {
            $sql .= ' AND `type` = \'normal\'';
        }

        global $conn;
        return $conn->GetOne($sql, $val);
    }

    /*
	 * Gets the count of total runs sent to a given problem
	 */

    final public static function CountTotalRunsOfProblem($problem_id, $showAllRuns = false) {
        // Build SQL statement.
        $sql = 'SELECT COUNT(*) FROM Runs WHERE problem_id = ? ';
        $val = [$problem_id];

        if (!$showAllRuns) {
            $sql .= ' AND `type` = \'normal\'';
        }

        global $conn;
        return $conn->GetOne($sql, $val);
    }

    /**
     * Get the count of runs of a problem in a given problemset
     *
     * @param int $problem_id
     * @param int $problemset_id
     */
    final public static function CountTotalRunsOfProblemInProblemset($problem_id, $problemset_id) {
        // Build SQL statement.
        $sql = 'SELECT COUNT(*) FROM Runs WHERE problem_id = ? AND problemset_id = ? AND `type` = \'normal\'';
        $val = [$problem_id, $problemset_id];

        global $conn;
        return $conn->GetOne($sql, $val);
    }

    /*
	 * Gets the count of total runs sent to a given contest by verdict
	 */

    final public static function CountTotalRunsOfProblemsetByVerdict($problemset_id, $verdict, $showAllRuns = false) {
        // Build SQL statement.
        $sql = 'SELECT COUNT(*) FROM Runs WHERE problemset_id = ? AND verdict = ? ';
        $val = [$problemset_id, $verdict];

        if (!$showAllRuns) {
            $sql .= ' AND `type` = \'normal\'';
        }

        global $conn;
        return $conn->GetOne($sql, $val);
    }

    /*
	 * Gets the count of total runs sent to a given contest by verdict
	 */

    final public static function CountTotalRunsOfProblemByVerdict($problem_id, $verdict, $showAllRuns = false) {
        // Build SQL statement.
        $sql = 'SELECT COUNT(*) FROM Runs WHERE problem_id = ? AND verdict = ? ';
        $val = [$problem_id, $verdict];

        if (!$showAllRuns) {
            $sql .= ' AND `type` = \'normal\'';
        }

        global $conn;
        return $conn->GetOne($sql, $val);
    }

    /*
     * Gets the count of total runs sent to a given contest by verdict and by period of time
     */
    final public static function CountRunsOfIdentityPerDatePerVerdict($identity_id) {
        // Build SQL statement.
        $sql = '
                SELECT
                    r.date,
                    r.verdict,
                    COUNT(1) runs
                FROM (
                    SELECT
                        DATE(time) AS date,
                        verdict
                    FROM
                        Runs
                    WHERE
                        identity_id = ? AND status = \'ready\'
                ) AS r
                GROUP BY
                    r.date, r.verdict
                ORDER BY
                  date ASC;';

        $val = [$identity_id];

        global $conn;
        $rs = $conn->Execute($sql, $val);

        $ar = [];
        foreach ($rs as $row) {
            array_push($ar, [
                'date' => $row['date'],
                'verdict' => $row['verdict'],
                'runs' => $row['runs']
            ]);
        }

        return $ar;
    }

    /*
	 * Gets the largest queued time of a run in ms
	 */
    final public static function GetLargestWaitTimeOfProblemset($problemset_id, $showAllRuns = false) {
        // Build SQL statement.
        $sql = "SELECT * FROM Runs WHERE problemset_id = ? AND status != 'ready' ORDER BY run_id ASC LIMIT 1";
        $val = [$problemset_id];

        global $conn;
        $rs = $conn->GetRow($sql, $val);

        if (count($rs) === 0) {
            return null;
        }

        $run = new Runs($rs);
        return [$run, Time::get() - strtotime($run->time)];
    }

    /*
	 *  getAllRelevantIdentities
	 *
	 */

    final public static function getAllRelevantIdentities($problemset_id, $acl_id, $showAllRuns = false, $filterUsersBy = null, $group_id = null, $excludeAdmin = true) {
        // Build SQL statement
        $log = Logger::getLogger('Scoreboard');
        if ($showAllRuns) {
            if (is_null($group_id)) {
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
                    $problemset_id,
                    $acl_id,
                    Authorization::SYSTEM_ACL,
                    Authorization::ADMIN_ROLE,
                ];
                if ($excludeAdmin) {
                    $sql = $sql . ' AND i.user_id != (SELECT a.owner_id FROM ACLs a WHERE a.acl_id = ?)';
                    $val[] =  $acl_id;
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
                    $group_id,
                    $acl_id,
                    $acl_id,
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
                        r.identity_id
                    FROM
                        Runs r
                    WHERE
                        r.verdict NOT IN (\'CE\', \'JE\') AND
                        r.problemset_id = ? AND
                        r.status = \'ready\' AND
                        r.type = \'normal\') rc ON i.identity_id = rc.identity_id';
            $val = [$problemset_id];
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

    final public static function getProblemsetRuns(Problemsets $problemset, $onlyAC = false) {
        $sql =    'SELECT '
                    . 'r.score, r.penalty, r.contest_score, r.problem_id, r.identity_id, r.type, r.time, r.submit_delay, r.guid '
                . 'FROM '
                    . 'Runs r '
                . 'INNER JOIN '
                    . 'Problemset_Problems pp '
                . 'ON '
                    . 'r.problem_id = pp.problem_id '
                    . 'AND r.problemset_id = pp.problemset_id '
                . 'WHERE '
                    . 'pp.problemset_id = ? '
                    . "AND r.status = 'ready' "
                    . "AND r.type = 'normal' " .
                    (($onlyAC === false) ?
                        "AND r.verdict NOT IN ('CE', 'JE') " :
                        "AND r.verdict IN ('AC') ")
                . 'ORDER BY r.run_id;';

        $val = [$problemset->problemset_id];

        global $conn;
        $rs = $conn->Execute($sql, $val);

        $ar = [];
        foreach ($rs as $foo) {
            array_push($ar, new Runs($foo));
        }

        return $ar;
    }

    /*
	 *
	 * Get last run of a user
	 *
	 */
    final public static function GetLastRun($problemset_id, $problem_id, $identity_id) {
        //Build SQL statement
        if (is_null($problemset_id)) {
            $sql = 'SELECT * from Runs where identity_id = ? and problem_id = ? ORDER BY time DESC LIMIT 1';
            $val = [$identity_id, $problem_id];
        } else {
            $sql = 'SELECT * from Runs where identity_id = ? and problemset_id = ? and problem_id = ? ORDER BY time DESC LIMIT 1';
            $val = [$identity_id, $problemset_id, $problem_id];
        }

        global $conn;
        $rs = $conn->GetRow($sql, $val);

        if (count($rs) === 0) {
            return null;
        }
        $bar = new Runs($rs);

        return $bar;
    }

    /*
	 *
	 * Get best run of a user
	 *
	 */

    final public static function GetBestRun($problemset_id, $problem_id, $identity_id, $showAllRuns) {
        $filterTest = $showAllRuns ? '' : ' AND `type` = \'normal\'';
        $sql = "
            SELECT
                contest_score, penalty, submit_delay, guid, run_id
            FROM
                Runs
            WHERE
                identity_id = ? AND problemset_id = ? AND problem_id = ? AND
                status = 'ready'
                $filterTest
            ORDER BY
                contest_score DESC, penalty ASC
            LIMIT 1;";
        $val = [$identity_id, $problemset_id, $problem_id];
        global $conn;
        $rs = $conn->GetRow($sql, $val);

        return new Runs($rs);
    }

    /**
     * Returns best score for the given identity and problem, between 0 and 100
     *
     * @global type $conn
     * @param type $problem_id
     * @param type $identity_id
     * @return int
     */
    final public static function GetBestScore($problem_id, $identity_id) {
        //Build SQL statement
        $sql = "SELECT score from Runs where identity_id = ? and problem_id = ? and status = 'ready' ORDER BY score DESC, penalty ASC  LIMIT 1";
        $val = [$identity_id, $problem_id];

        global $conn;
        $rs = $conn->GetRow($sql, $val);

        if (count($rs) === 0) {
            return 0;
        } else {
            return number_format($rs['score'] * 100, 2);
        }
    }

    /*
	 * Get runs of an identity with verdict eq AC
	 */
    final public static function GetRunsByUser($identity_id) {
        // SQL sentence
        $sql = "SELECT DISTINCT * FROM Runs WHERE identity_id = ? AND verdict = 'AC'";
        $val = [$identity_id];

        global $conn;
        //Get the rows
        $rs = $conn->Execute($sql, $val);

        $ar = [];
        //Wrap every row in a Runs object
        foreach ($rs as $iter) {
            $run = new Runs($iter);
            array_push($ar, $run);
        }
        return $ar;
    }

    final public static function getByContest($contest_id) {
        $sql = 'SELECT
                    `run_id`,
                    `guid`,
                    `language`,
                    `status`,
                    `verdict`,
                    `runtime`,
                    `penalty`,
                    `memory`,
                    `score`,
                    `contest_score`,
                    `time`,
                    `submit_delay`,
                    `Identities.username`,
                    `Problems.alias`
                FROM
                    Runs r
                INNER JOIN
                    Contests c
                ON
                    c.problemset_id = r.problemset_id
                INNER JOIN
                    Problems p
                ON
                    p.problem_id = r.problem_id
                INNER JOIN
                    Identities i
                ON
                    i.identity_id = r.identity_id
                WHERE
                    c.contest_id = ?
                ORDER BY
                    `time` DESC;';

        global $conn;
        $rs = $conn->Execute($sql, [$contest_id]);

        $runs = [];
        foreach ($rs as $row) {
            array_push($runs, new Runs($row));
        }
        return $runs;
    }

    final public static function getByKeys($problem_id, $problemset_id = null, $identity_id = null) {
        $sql = 'SELECT
                    *
                FROM
                    Runs r
                WHERE
                    problem_id = ?';
        $params = [$problem_id];
        if (!is_null($problemset_id)) {
            $sql .= ' AND problemset_id = ?';
            $params[] = $problemset_id;
        }
        if (!is_null($identity_id)) {
            $sql .= ' AND identity_id = ?';
            $params[] = $identity_id;
        }
        global $conn;
        $rs = $conn->Execute($sql, $params);

        $runs = [];
        foreach ($rs as $row) {
            array_push($runs, new Runs($row));
        }

        return $runs;
    }

    final public static function IsRunInsideSubmissionGap(
        $problemset_id,
        $contest,
        $problem_id,
        $identity_id
    ) {
        // Get last run
        $lastrun = self::GetLastRun($problemset_id, $problem_id, $identity_id);

        if (is_null($lastrun)) {
            return true;
        }

        $submission_gap = RunController::$defaultSubmissionGap;
        if (!is_null($contest)) {
            // Get submissions gap
            $submission_gap = max(
                $submission_gap,
                (int)$contest->submissions_gap
            );
        }

        return Time::get() >= (strtotime($lastrun->time) + $submission_gap);
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

    public static function GetRunCountsToDate($date) {
        $sql = 'select count(*) as total from Runs where time <= ?';
        $val = [$date];

        global $conn;
        $rs = $conn->GetRow($sql, $val);

        return $rs['total'];
    }

    public static function GetAcRunCountsToDate($date) {
        $sql = "select count(*) as total from Runs where verdict = 'AC' and time <= ?";
        $val = [$date];

        global $conn;
        $rs = $conn->GetRow($sql, $val);

        return $rs['total'];
    }

    final public static function searchRunIdGreaterThan($Runs, $greaterThan, $orderBy = null, $orden = 'ASC', $columnas = null, $offset = 0, $rowcount = null) {
        // Implode array of columns to a coma-separated string
        $columns_str = is_null($columnas) ? '*' : implode(',', $columnas);

        $sql = 'SELECT ' . $columns_str . '  from Runs ';

        if ($columnas != null) {
            if (in_array('Identities.username', $columnas)) {
                $sql .= 'INNER JOIN Identities ON Identities.identity_id = Runs.identity_id ';
            }
            if (in_array('Problems.alias', $columnas)) {
                $sql .= 'INNER JOIN Problems ON Problems.problem_id = Runs.problem_id ';
            }
        }
        $sql .= 'WHERE (';
        $val = [];
        if ($Runs->run_id != null) {
            $sql .= ' run_id = ? AND';
            array_push($val, $Runs->run_id);
        }

        if ($Runs->identity_id != null) {
            $sql .= ' identity_id = ? AND';
            array_push($val, $Runs->identity_id);
        }

        if ($Runs->problem_id != null) {
            $sql .= ' Runs.problem_id = ? AND';
            array_push($val, $Runs->problem_id);
        }

        if ($Runs->problemset_id != null) {
            $sql .= ' Runs.problemset_id = ? AND';
            array_push($val, $Runs->problemset_id);
        }

        if ($Runs->guid != null) {
            $sql .= ' guid = ? AND';
            array_push($val, $Runs->guid);
        }

        if ($Runs->language != null) {
            $sql .= ' language = ? AND';
            array_push($val, $Runs->language);
        }

        if ($Runs->status != null) {
            $sql .= ' status = ? AND';
            array_push($val, $Runs->status);
        }

        if ($Runs->verdict != null) {
            if ($Runs->verdict == 'NO-AC') {
                $sql .= ' verdict != ? AND';
                array_push($val, 'AC');
            } else {
                $sql .= ' verdict = ? AND';
                array_push($val, $Runs->verdict);
            }
        }

        if ($Runs->runtime != null) {
            $sql .= ' runtime = ? AND';
            array_push($val, $Runs->runtime);
        }

        if ($Runs->memory != null) {
            $sql .= ' memory = ? AND';
            array_push($val, $Runs->memory);
        }

        if ($Runs->score != null) {
            $sql .= ' score = ? AND';
            array_push($val, $Runs->score);
        }

        if ($Runs->contest_score != null) {
            $sql .= ' contest_score = ? AND';
            array_push($val, $Runs->contest_score);
        }

        if ($Runs->time != null) {
            $sql .= ' time = ? AND';
            array_push($val, $Runs->time);
        }

        if ($Runs->type !== null) {
            $sql .= ' `type` = ?  AND';
            array_push($val, $Runs->type);
        }

        $sql .= ' run_id > ?  AND';
        array_push($val, $greaterThan);

        if (sizeof($val) == 0) {
            return [];
        }
        $sql = substr($sql, 0, -3) . ' )';
        if ($orderBy !== null) {
            $sql .= ' order by ' . $orderBy . ' ' . $orden;
        }

        // Add LIMIT offset, rowcount if rowcount is set
        if (!is_null($rowcount)) {
            $sql .= ' LIMIT ' . $offset . ',' . $rowcount;
        }

        global $conn;
        $rs = $conn->Execute($sql, $val);
        $ar = [];
        foreach ($rs as $foo) {
            $bar = new Runs($foo);
            array_push($ar, $bar);
        }
        return $ar;
    }

    /**
     * Recalculate the contest_score of all problemset and problem Runs
     */
    public static function recalculateScore($problemset_id, $problem_id, $current_points, $original_points) {
        $sql = 'UPDATE
                  `Runs`
                SET
                  `contest_score` = `score` * ?
                WHERE
                  `problemset_id` = ?
                  AND `problem_id` = ?;';

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
            $sql = 'UPDATE
                        `Runs`
                    SET
                        `penalty` = 0
                    WHERE
                        `problemset_id` = ?;';
        } elseif ($penalty_type == 'runtime') {
            $sql = 'UPDATE
                        `Runs`
                    SET
                        `penalty` = `runtime`
                    WHERE
                        `problemset_id` = ?;';
        } elseif ($penalty_type == 'contest_start') {
            $sql = 'UPDATE
                        `Runs` r
                    INNER JOIN
                        `Problemset_Problem_Opened` ppo
                        ON (ppo.problemset_id = r.problemset_id
                            AND r.problem_id = ppo.problem_id)
                    INNER JOIN
                        `Identities` i
                        ON (i.identity_id = ppo.identity_id AND r.identity_id = i.identity_id)
                    INNER JOIN `Contests` c ON (c.problemset_id = r.problemset_id)
                    SET
                        r.penalty = ROUND(TIME_TO_SEC(TIMEDIFF(r.time, c.start_time))/60)
                    WHERE
                        r.problemset_id = ?;';
        } elseif ($penalty_type == 'problem_open') {
            $sql = 'UPDATE
                        `Runs` r
                    INNER JOIN
                        `Problemset_Problem_Opened` ppo
                        ON (ppo.problemset_id = r.problemset_id
                            AND r.problem_id = ppo.problem_id)
                    INNER JOIN
                        `Identities`
                        ON (i.identity_id = ppo.identity_id AND r.identity_id = i.identity_id)
                    INNER JOIN `Contests` c ON (c.problemset_id = r.problemset_id)
                    SET
                        r.penalty = ROUND(TIME_TO_SEC(TIMEDIFF(r.time, ppo.open_time))/60)
                    WHERE
                        r.problemset_id = ?;';
        } else {
            return 0;
        }
        $params = [$contest->problemset_id];
        global $conn;
        $conn->Execute($sql, $params);
        return $conn->Affected_Rows();
    }
}
