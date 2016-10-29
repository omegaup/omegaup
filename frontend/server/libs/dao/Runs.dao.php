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
	 * Gets a boolean indicating whether there are runs that are not ready.
	 */

    final public static function PendingRuns($contest_id, $showAllRuns = false) {
        // Build SQL statement.
        $sql = "SELECT COUNT(*) FROM Runs WHERE contest_id = ? AND status != 'ready'";
        $val = array($contest_id);

        if (!$showAllRuns) {
            $sql .= ' AND test = 0';
        }

        global $conn;
        return $conn->GetOne($sql, $val) === 0;
    }

    /*
	 * Gets an array of the guids of the pending runs
	 */

    final public static function GetPendingRuns($showAllRuns = false) {
        // Build SQL statement.
        $sql = "SELECT guid, UNIX_TIMESTAMP(time) AS time FROM Runs WHERE status != 'ready'";

        if (!$showAllRuns) {
            $sql .= ' AND test = 0';
        }

        $sql .= ' ORDER BY run_id;';

        global $conn;
        $rs = $conn->Execute($sql);

        $ar = array();
        foreach ($rs as $row) {
            array_push($ar, array('guid' => $row['guid'], 'time' => intval($row['time'])));
        }

        return $ar;
    }

    /*
	 * Gets an array of the guids of the pending runs
	 */

    final public static function GetBestSolvingRunsForProblem($problem_id) {
        $sql = '
			SELECT u.username, r.language, r.runtime, r.memory, UNIX_TIMESTAMP(r.time) time FROM
				(SELECT
					MIN(r.run_id) run_id, r.user_id, r.runtime
				FROM
					Runs r
				INNER JOIN
					(
						SELECT
							rr.user_id, MIN(rr.runtime) AS runtime
						FROM
							Runs rr
						WHERE
							rr.problem_id = ? AND rr.verdict = \'AC\' AND rr.test = 0 GROUP BY rr.user_id
					) AS sr ON sr.user_id = r.user_id AND sr.runtime = r.runtime
				WHERE
					r.problem_id = ? AND r.verdict = \'AC\' AND r.test = 0
				GROUP BY
					r.user_id, r.runtime
				ORDER
					BY r.runtime, run_id
				LIMIT 0, 10) as runs
			INNER JOIN
				Users u ON u.user_id = runs.user_id
			INNER JOIN
				Runs r ON r.run_id = runs.run_id;';
        $val = array($problem_id, $problem_id);

        global $conn;
        return $conn->GetAll($sql, $val);
    }

    /*
	 * Gets an array of the guids of the pending runs
	 */

    final public static function GetPendingRunsOfContest($contest_id, $showAllRuns = false) {
        // Build SQL statement.
        $sql = "SELECT guid FROM Runs WHERE contest_id = ? AND status != 'ready'";
        $val = array($contest_id);

        if (!$showAllRuns) {
            $sql .= ' AND test = 0';
        }

        global $conn;
        $rs = $conn->Execute($sql, $val);

        $ar = array();
        foreach ($rs as $foo) {
            array_push($ar, $foo['guid']);
        }

        return $ar;
    }

    final public static function GetAllRuns($contest_id, $status, $verdict, $problem_id, $language, $user_id, $offset, $rowcount) {
        $sql = 'SELECT r.run_id, r.guid, r.language, r.status, r.verdict, r.runtime, r.penalty, ' .
                'r.memory, r.score, r.contest_score, r.judged_by, UNIX_TIMESTAMP(r.time) AS time, ' .
                'r.submit_delay, u.username, p.alias, u.country_id, c.alias AS contest_alias ' .
                'FROM Runs r USE INDEX(PRIMARY) ' .
                'INNER JOIN Problems p ON p.problem_id = r.problem_id ' .
                'INNER JOIN Users u ON u.user_id = r.user_id ' .
                'LEFT JOIN Contests c ON c.contest_id = r.contest_id ';
        $where = array();
        $val = array();

        if (!is_null($contest_id)) {
            $where[] = 'r.contest_id = ?';
            $val[] = $contest_id;
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
        if (!is_null($user_id)) {
            $where[] = 'r.user_id = ?';
            $val[] = $user_id;
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
        $val = array($problem_id);

        if (!$showAllRuns) {
            $sql .= ' AND test = 0';
        }

        global $conn;
        $rs = $conn->Execute($sql, $val);

        $ar = array();
        foreach ($rs as $foo) {
            array_push($ar, $foo['guid']);
        }

        return $ar;
    }

    final public static function getByAlias($alias) {
        $sql = 'SELECT * FROM Runs WHERE (guid = ? ) LIMIT 1;';
        $params = array($alias);

        global $conn;
        $rs = $conn->GetRow($sql, $params);
        if (count($rs) == 0) {
            return null;
        }

        $contest = new Runs($rs);
        return $contest;
    }

    /*
	 * Gets the count of total runs sent to a given contest
	 */

    final public static function CountTotalRunsOfContest($contest_id, $showAllRuns = false) {
        // Build SQL statement.
        $sql = 'SELECT COUNT(*) FROM Runs WHERE contest_id = ? ';
        $val = array($contest_id);

        if (!$showAllRuns) {
            $sql .= ' AND test = 0';
        }

        global $conn;
        return $conn->GetOne($sql, $val);
    }

    /*
	 * Gets the count of total runs sent by an user
	 */

    final public static function CountTotalRunsOfUser($user_id, $showAllRuns = false) {
        // Build SQL statement.
        $sql = 'SELECT COUNT(*) FROM Runs WHERE user_id = ? ';
        $val = array($user_id);

        if (!$showAllRuns) {
            $sql .= ' AND test = 0';
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
        $val = array($problem_id);

        if (!$showAllRuns) {
            $sql .= ' AND test = 0';
        }

        global $conn;
        return $conn->GetOne($sql, $val);
    }

    /**
     * Get the count of runs of a problem in a given contest
     *
     * @param string  $problem_id
     * @param string  $contest_id
     */
    final public static function CountTotalRunsOfProblemInContest($problem_id, $contest_id) {
        // Build SQL statement.
        $sql = 'SELECT COUNT(*) FROM Runs WHERE problem_id = ? AND contest_id = ? AND test = 0';
        $val = array($problem_id, $contest_id);

        global $conn;
        return $conn->GetOne($sql, $val);
    }

    /*
	 * Gets the count of total runs sent to a given contest by verdict
	 */

    final public static function CountTotalRunsOfContestByVerdict($contest_id, $verdict, $showAllRuns = false) {
        // Build SQL statement.
        $sql = 'SELECT COUNT(*) FROM Runs WHERE contest_id = ? AND verdict = ? ';
        $val = array($contest_id, $verdict);

        if (!$showAllRuns) {
            $sql .= ' AND test = 0';
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
        $val = array($problem_id, $verdict);

        if (!$showAllRuns) {
            $sql .= ' AND test = 0';
        }

        global $conn;
        return $conn->GetOne($sql, $val);
    }

    /*
	 * Gets the count of total runs sent to a given contest by verdict
	 */

    final public static function CountTotalRunsOfUserByVerdict($user_id, $verdict, $showAllRuns = false) {
        // Build SQL statement.
        $sql = 'SELECT COUNT(*) FROM Runs WHERE user_id = ? AND verdict = ? ';
        $val = array($user_id, $verdict);

        if (!$showAllRuns) {
            $sql .= ' AND test = 0';
        }

        global $conn;
        return $conn->GetOne($sql, $val);
    }

    /*
	 * Gets the largest queued time of a run in ms
	 */

    final public static function GetLargestWaitTimeOfContest($contest_id, $showAllRuns = false) {
        // Build SQL statement.
        $sql = "SELECT * FROM Runs WHERE contest_id = ? AND status != 'ready' ORDER BY run_id ASC LIMIT 1";
        $val = array($contest_id);

        global $conn;
        $rs = $conn->GetRow($sql, $val);

        if (count($rs) === 0) {
            return null;
        }

        $run = new Runs($rs);
        return array($run, time() - strtotime($run->time));
    }

    /*
	 *  GetAllRelevantUsers
	 *
	 */

    final public static function GetAllRelevantUsers($contest_id, $showAllRuns = false, $filterUsersBy = null) {
        // Build SQL statement
        if (!$showAllRuns) {
            $sql = 'SELECT Users.user_id, username, Users.name, Users.country_id from Users INNER JOIN ( '
                    . 'SELECT DISTINCT Runs.user_id from Runs '
                    . "WHERE ( Runs.verdict NOT IN ('CE', 'JE') AND Runs.contest_id = ? AND Runs.status = 'ready' " . ($showAllRuns ? '' : ' AND Runs.test = 0') . ' ) ) '
                . 'RunsContests ON Users.user_id = RunsContests.user_id ' . (!is_null($filterUsersBy) ? 'WHERE Users.username LIKE ?' : '');

            if (is_null($filterUsersBy)) {
                $val = array($contest_id);
            } else {
                $val = array($contest_id, $filterUsersBy . '%');
            }
        } else {
            $sql = 'SELECT Users.user_id, username, Users.name, Users.country_id from Users '
                    . 'INNER JOIN Contests_Users ON Users.user_id = Contests_Users.user_id '
                    . 'WHERE contest_id = ? AND Users.user_id NOT IN'
                        . ' (SELECT user_id FROM User_Roles WHERE contest_id = ? OR contest_id = 0)'
                    . 'AND Users.user_id != (SELECT director_id FROM Contests where contest_id = ?)';
            $val = array($contest_id, $contest_id, $contest_id);
        }

        global $conn;
        $rs = $conn->Execute($sql, $val);

        $ar = array();
        foreach ($rs as $foo) {
            $bar = new Users($foo);
            array_push($ar, $bar);
        }

        return $ar;
    }

    final public static function GetContestRuns($contest_id, $onlyAC = false) {
        $sql =    'SELECT '
                    . 'r.score, r.penalty, r.contest_score, r.problem_id, r.user_id, r.test, r.time, r.submit_delay, r.guid '
                . 'FROM '
                    . 'Runs r '
                . 'INNER JOIN '
                    . 'Contest_Problems cp '
                . 'ON '
                    . 'r.problem_id = cp.problem_id '
                    . 'AND r.contest_id = cp.contest_id '
                . 'WHERE '
                    . 'cp.contest_id = ? '
                    . "AND r.status = 'ready' "
                    . "AND r.test = '0' " .
                    (($onlyAC === false) ?
                        "AND r.verdict NOT IN ('CE', 'JE') " :
                        "AND r.verdict IN ('AC') ")
                . 'ORDER BY r.run_id;';

        $val = array($contest_id);

        global $conn;
        $rs = $conn->Execute($sql, $val);

        $ar = array();
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

    final public static function GetLastRun($contest_id, $problem_id, $user_id) {
        //Build SQL statement
        if ($contest_id == null) {
            $sql = 'SELECT * from Runs where user_id = ? and problem_id = ? ORDER BY time DESC LIMIT 1';
            $val = array($user_id, $problem_id);
        } else {
            $sql = 'SELECT * from Runs where user_id = ? and contest_id = ? and problem_id = ? ORDER BY time DESC LIMIT 1';
            $val = array($user_id, $contest_id, $problem_id);
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

    final public static function GetBestRun($contest_id, $problem_id, $user_id, $finish_time, $showAllRuns) {
        //Build SQL statement
        $sql = "SELECT contest_score, penalty, submit_delay, guid, run_id from Runs where user_id = ? and contest_id = ? and problem_id = ? and status = 'ready' and time <= FROM_UNIXTIME(?) " . ($showAllRuns ? '' : ' AND test = 0 ') . ' ORDER BY contest_score DESC, penalty ASC  LIMIT 1';
        $val = array($user_id, $contest_id, $problem_id, $finish_time);

        global $conn;
        $rs = $conn->GetRow($sql, $val);

        return new Runs($rs);
    }

    /**
     * Returns best score for the given user and problem, between 0 and 100
     *
     * @global type $conn
     * @param type $problem_id
     * @param type $user_id
     * @return int
     */
    final public static function GetBestScore($problem_id, $user_id) {
        //Build SQL statement
        $sql = "SELECT score from Runs where user_id = ? and problem_id = ? and status = 'ready' ORDER BY score DESC, penalty ASC  LIMIT 1";
        $val = array($user_id, $problem_id);

        global $conn;
        $rs = $conn->GetRow($sql, $val);

        if (count($rs) === 0) {
            return 0;
        } else {
            return number_format($rs['score'] * 100, 2);
        }
    }

    /*
	 * Get number of runs before current.
	 */

    final public static function GetWrongRuns($contest_id, $problem_id, $user_id, $run_id, $showAllRuns) {
        //Build SQL statement
        $sql = "SELECT COUNT(*) AS wrong_runs FROM Runs WHERE user_id = ? AND contest_id = ? AND problem_id = ? AND verdict != 'JE' AND verdict != 'CE' AND run_id < ? " . ($showAllRuns ? '' : ' AND test = 0 ');
        $val = array($user_id, $contest_id, $problem_id, $run_id);

        global $conn;
        $rs = $conn->GetRow($sql, $val);

        return $rs['wrong_runs'];
    }

    /*
	 * Get runs of a user with verdict eq AC
	 */

    final public static function GetRunsByUser($user_id) {
        // SQL sentence
        $sql = "SELECT DISTINCT * FROM Runs WHERE user_id = ? AND verdict = 'AC'";
        $val = array($user_id);

        global $conn;
        //Get the rows
        $rs = $conn->Execute($sql, $val);

        $ar = array();
        //Wrap every row in a Runs object
        foreach ($rs as $iter) {
            $run = new Runs($iter);
            array_push($ar, $run);
        }
        return $ar;
    }

    final public static function IsRunInsideSubmissionGap($contest_id, $problem_id, $user_id) {
        // Get last run
        $lastrun = self::GetLastRun($contest_id, $problem_id, $user_id);

        if (is_null($lastrun)) {
            return true;
        }

        $submission_gap = 0;
        if ($contest_id != null) {
            // Get submissions gap
            $contest = ContestsDAO::getByPK($contest_id);
            $submission_gap = (int) $contest->submissions_gap;
        }
        $submission_gap = max($submission_gap, RunController::$defaultSubmissionGap);

        return time() >= (strtotime($lastrun->time) + $submission_gap);
    }

    public static function GetRunCountsToDate($date) {
        $sql = 'select count(*) as total from Runs where time <= ?';
        $val = array($date);

        global $conn;
        $rs = $conn->GetRow($sql, $val);

        return $rs['total'];
    }

    public static function GetAcRunCountsToDate($date) {
        $sql = "select count(*) as total from Runs where verdict = 'AC' and time <= ?";
        $val = array($date);

        global $conn;
        $rs = $conn->GetRow($sql, $val);

        return $rs['total'];
    }

    final public static function searchRunIdGreaterThan($Runs, $greaterThan, $orderBy = null, $orden = 'ASC', $columnas = null, $offset = 0, $rowcount = null) {
        // Implode array of columns to a coma-separated string
        $columns_str = is_null($columnas) ? '*' : implode(',', $columnas);

        $sql = 'SELECT ' . $columns_str . '  from Runs ';

        if ($columnas != null) {
            if (in_array('Users.username', $columnas)) {
                $sql .= 'INNER JOIN Users ON Users.user_id = Runs.user_id ';
            }
            if (in_array('Problems.alias', $columnas)) {
                $sql .= 'INNER JOIN Problems ON Problems.problem_id = Runs.problem_id ';
            }
        }
        $sql .= 'WHERE (';
        $val = array();
        if ($Runs->run_id != null) {
            $sql .= ' run_id = ? AND';
            array_push($val, $Runs->run_id);
        }

        if ($Runs->user_id != null) {
            $sql .= ' user_id = ? AND';
            array_push($val, $Runs->user_id);
        }

        if ($Runs->problem_id != null) {
            $sql .= ' Runs.problem_id = ? AND';
            array_push($val, $Runs->problem_id);
        }

        if ($Runs->contest_id != null) {
            $sql .= ' Runs.contest_id = ? AND';
            array_push($val, $Runs->contest_id);
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

        if ($Runs->test !== null) {
            $sql .= ' test = ?  AND';
            array_push($val, $Runs->test);
        }

        $sql .= ' run_id > ?  AND';
        array_push($val, $greaterThan);

        if (sizeof($val) == 0) {
            return array();
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
        $ar = array();
        foreach ($rs as $foo) {
            $bar = new Runs($foo);
            array_push($ar, $bar);
        }
        return $ar;
    }
}
