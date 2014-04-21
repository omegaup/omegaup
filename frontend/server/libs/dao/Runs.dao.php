<?php

require_once("base/Runs.dao.base.php");
require_once("base/Runs.vo.base.php");
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

	const DEFAULT_SUBMISSION_GAP = 120;

	/*
	 * Gets a boolean indicating whether there are runs that are not ready.
	 */

	public static final function PendingRuns($contest_id, $showAllRuns = false) {
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

	public static final function GetPendingRuns($showAllRuns = false) {
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

	public static final function GetPendingRunsOfContest($contest_id, $showAllRuns = false) {
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

	public static final function GetAllRuns($contest_id, $status, $veredict, $problem_id, $language, $user_id, $offset, $rowcount) {
		$sql = 'SELECT r.run_id, r.guid, r.language, r.status, r.veredict, r.runtime, ' .
				'r.memory, r.score, r.contest_score, UNIX_TIMESTAMP(r.time) AS time, ' .
				'r.submit_delay, u.username, p.alias ' .
				'FROM Runs r ' .
				'INNER JOIN Problems p ON p.problem_id = r.problem_id ' .
				'INNER JOIN Users u ON u.user_id = r.user_id ';
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
		if (!is_null($veredict)) {
			$where[] = 'r.veredict = ?';
			$val[] = $veredict;
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

	public static final function GetPendingRunsOfProblem($problem_id, $showAllRuns = false) {
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

	public static final function getByAlias($alias) {
		$sql = "SELECT * FROM Runs WHERE (guid = ? ) LIMIT 1;";
		$params = array($alias);

		global $conn;
		$rs = $conn->GetRow($sql, $params);
		if (count($rs) == 0) {
			return NULL;
		}

		$contest = new Runs($rs);
		return $contest;
	}

	/*
	 * Gets the count of total runs sent to a given contest
	 */

	public static final function CountTotalRunsOfContest($contest_id, $showAllRuns = false) {
		// Build SQL statement.
		$sql = "SELECT COUNT(*) FROM Runs WHERE contest_id = ? ";
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

	public static final function CountTotalRunsOfUser($user_id, $showAllRuns = false) {
		// Build SQL statement.
		$sql = "SELECT COUNT(*) FROM Runs WHERE user_id = ? ";
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

	public static final function CountTotalRunsOfProblem($problem_id, $showAllRuns = false) {
		// Build SQL statement.
		$sql = "SELECT COUNT(*) FROM Runs WHERE problem_id = ? ";
		$val = array($problem_id);

		if (!$showAllRuns) {
			$sql .= ' AND test = 0';
		}

		global $conn;
		return $conn->GetOne($sql, $val);
	}

	/*
	 * Gets the count of total runs sent to a given contest by veredict
	 */

	public static final function CountTotalRunsOfContestByVeredict($contest_id, $veredict, $showAllRuns = false) {
		// Build SQL statement.
		$sql = "SELECT COUNT(*) FROM Runs WHERE contest_id = ? AND veredict = ? ";
		$val = array($contest_id, $veredict);

		if (!$showAllRuns) {
			$sql .= ' AND test = 0';
		}

		global $conn;
		return $conn->GetOne($sql, $val);
	}

	/*
	 * Gets the count of total runs sent to a given contest by veredict
	 */

	public static final function CountTotalRunsOfProblemByVeredict($problem_id, $veredict, $showAllRuns = false) {
		// Build SQL statement.
		$sql = "SELECT COUNT(*) FROM Runs WHERE problem_id = ? AND veredict = ? ";
		$val = array($problem_id, $veredict);

		if (!$showAllRuns) {
			$sql .= ' AND test = 0';
		}

		global $conn;
		return $conn->GetOne($sql, $val);
	}

	/*
	 * Gets the count of total runs sent to a given contest by veredict
	 */

	public static final function CountTotalRunsOfUserByVeredict($user_id, $veredict, $showAllRuns = false) {
		// Build SQL statement.
		$sql = "SELECT COUNT(*) FROM Runs WHERE user_id = ? AND veredict = ? ";
		$val = array($user_id, $veredict);

		if (!$showAllRuns) {
			$sql .= ' AND test = 0';
		}

		global $conn;
		return $conn->GetOne($sql, $val);
	}

	/*
	 * Gets the largest queued time of a run in ms 
	 */

	public static final function GetLargestWaitTimeOfContest($contest_id, $showAllRuns = false) {
		// Build SQL statement.
		$sql = "SELECT * FROM Runs WHERE contest_id = ? AND status != 'ready' ORDER BY time ASC LIMIT 1";
		$val = array($contest_id);

		global $conn;
		$rs = $conn->GetRow($sql, $val);

		if (count($rs) === 0) {
			return null;
		}

		$run = new Runs($rs);
		return array($run, time() - strtotime($run->getTime()));
	}

	/*
	 *  GetAllRelevantUsers
	 * 
	 */

	public static final function GetAllRelevantUsers($contest_id, $showAllRuns = false, $filterUsersBy = null) {

		// Build SQL statement
		if (!$showAllRuns) {
			$sql = "SELECT Users.user_id, username, Users.name from Users INNER JOIN ( SELECT DISTINCT Runs.user_id from Runs WHERE ( Runs.contest_id = ? AND Runs.status = 'ready' " . ($showAllRuns ? "" : " AND Runs.test = 0") . " ) ) RunsContests ON Users.user_id = RunsContests.user_id " . (!is_null($filterUsersBy) ? "WHERE Users.username LIKE ?" : "");

			if (is_null($filterUsersBy)) {
				$val = array($contest_id);
			} else {
				$val = array($contest_id, $filterUsersBy . "%");
			}
		} else {
			$sql = "SELECT Users.user_id, username, Users.name from Users INNER JOIN Contests_Users ON Users.user_id = Contests_Users.user_id WHERE contest_id = ?";
			$val = array($contest_id);
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

	public static final function GetContestRuns($contest_id, $order_by_column) {
		$sql = "SELECT contest_score, problem_id, user_id, test, time, submit_delay FROM Runs WHERE contest_id = ? AND status = 'ready' AND veredict NOT IN ('CE', 'JE') ORDER BY ?;";
		$val = array($contest_id, $order_by_column);

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

	public static final function GetLastRun($contest_id, $problem_id, $user_id) {
		//Build SQL statement
		if ($contest_id == null) {
			$sql = "SELECT * from Runs where user_id = ? and problem_id = ? ORDER BY time DESC LIMIT 1";
			$val = array($user_id, $problem_id);
		} else {
			$sql = "SELECT * from Runs where user_id = ? and contest_id = ? and problem_id = ? ORDER BY time DESC LIMIT 1";
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

	public static final function GetBestRun($contest_id, $problem_id, $user_id, $finish_time, $showAllRuns) {
		//Build SQL statement
		$sql = "SELECT contest_score, submit_delay, guid, run_id from Runs where user_id = ? and contest_id = ? and problem_id = ? and status = 'ready' and time <= FROM_UNIXTIME(?) " . ($showAllRuns ? "" : " AND test = 0 ") . " ORDER BY contest_score DESC, submit_delay ASC  LIMIT 1";
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
	public static final function GetBestScore($problem_id, $user_id) {
		//Build SQL statement
		$sql = "SELECT score from Runs where user_id = ? and problem_id = ? and status = 'ready' ORDER BY score DESC, submit_delay ASC  LIMIT 1";
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

	public static final function GetWrongRuns($contest_id, $problem_id, $user_id, $run_id, $showAllRuns) {
		//Build SQL statement
		$sql = "SELECT COUNT(*) AS wrong_runs FROM Runs WHERE user_id = ? AND contest_id = ? AND problem_id = ? AND veredict != 'JE' AND veredict != 'CE' AND run_id < ? " . ($showAllRuns ? "" : " AND test = 0 ");
		$val = array($user_id, $contest_id, $problem_id, $run_id);

		global $conn;
		$rs = $conn->GetRow($sql, $val);

		return $rs['wrong_runs'];
	}

	/*
	 * Get runs of a user with veredict eq AC
	 */

	public static final function GetRunsByUser($user_id) {
		// SQL sentence
		$sql = "SELECT DISTINCT * FROM Runs WHERE user_id = ? AND veredict = 'AC'";
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

	public static final function IsRunInsideSubmissionGap($contest_id, $problem_id, $user_id) {
		// Get last run
		$lastrun = self::GetLastRun($contest_id, $problem_id, $user_id);

		if (is_null($lastrun)) {
			return true;
		}

		if ($contest_id == null) {
			$submission_gap = RunController::$defaultSubmissionGap;
		} else {
			// Get submissions gap
			$contest = ContestsDAO::getByPK($contest_id);
			$submission_gap = (int) $contest->getSubmissionsGap();
		}

		// Giving 10 secs as gift
		return time() >= (strtotime($lastrun->getTime()) + $submission_gap - 10);
	}

	public static function GetRunCountsToDate($date) {

		$sql = "select count(*) as total from Runs where time <= ?";
		$val = array($date);

		global $conn;
		$rs = $conn->GetRow($sql, $val);

		return $rs['total'];
	}

	public static function GetAcRunCountsToDate($date) {

		$sql = "select count(*) as total from Runs where veredict = 'AC' and time <= ?";
		$val = array($date);

		global $conn;
		$rs = $conn->GetRow($sql, $val);

		return $rs['total'];
	}

	public static final function searchRunIdGreaterThan($Runs, $greaterThan, $orderBy = null, $orden = 'ASC', $columnas = NULL, $offset = 0, $rowcount = NULL) {
		// Implode array of columns to a coma-separated string               
		$columns_str = is_null($columnas) ? "*" : implode(",", $columnas);

		$sql = "SELECT " . $columns_str . "  from Runs ";

		if ($columnas != null) {
			if (in_array("Users.username", $columnas)) {
				$sql .= "INNER JOIN Users ON Users.user_id = Runs.user_id ";
			}
			if (in_array("Problems.alias", $columnas)) {
				$sql .= "INNER JOIN Problems ON Problems.problem_id = Runs.problem_id ";
			}
		}
		$sql .= "WHERE (";
		$val = array();
		if ($Runs->getRunId() != NULL) {
			$sql .= " run_id = ? AND";
			array_push($val, $Runs->getRunId());
		}

		if ($Runs->getUserId() != NULL) {
			$sql .= " user_id = ? AND";
			array_push($val, $Runs->getUserId());
		}

		if ($Runs->getProblemId() != NULL) {
			$sql .= " Runs.problem_id = ? AND";
			array_push($val, $Runs->getProblemId());
		}

		if ($Runs->getContestId() != NULL) {
			$sql .= " Runs.contest_id = ? AND";
			array_push($val, $Runs->getContestId());
		}

		if ($Runs->getGuid() != NULL) {
			$sql .= " guid = ? AND";
			array_push($val, $Runs->getGuid());
		}

		if ($Runs->getLanguage() != NULL) {
			$sql .= " language = ? AND";
			array_push($val, $Runs->getLanguage());
		}

		if ($Runs->getStatus() != NULL) {
			$sql .= " status = ? AND";
			array_push($val, $Runs->getStatus());
		}

		if ($Runs->getVeredict() != NULL) {
			if ($Runs->getVeredict() == "NO-AC") {
				$sql .= " veredict != ? AND";
				array_push($val, "AC");
			} else {
				$sql .= " veredict = ? AND";
				array_push($val, $Runs->getVeredict());
			}
		}

		if ($Runs->getRuntime() != NULL) {
			$sql .= " runtime = ? AND";
			array_push($val, $Runs->getRuntime());
		}

		if ($Runs->getMemory() != NULL) {
			$sql .= " memory = ? AND";
			array_push($val, $Runs->getMemory());
		}

		if ($Runs->getScore() != NULL) {
			$sql .= " score = ? AND";
			array_push($val, $Runs->getScore());
		}

		if ($Runs->getContestScore() != NULL) {
			$sql .= " contest_score = ? AND";
			array_push($val, $Runs->getContestScore());
		}

		if ($Runs->getIp() != NULL) {
			$sql .= " ip = ? AND";
			array_push($val, $Runs->getIp());
		}

		if ($Runs->getTime() != NULL) {
			$sql .= " time = ? AND";
			array_push($val, $Runs->getTime());
		}

		if ($Runs->getTest() !== NULL) {
			$sql .= " test = ?  AND";
			array_push($val, $Runs->getTest());
		}

		$sql .= " run_id > ?  AND";
		array_push($val, $greaterThan);

		if (sizeof($val) == 0) {
			return array();
		}
		$sql = substr($sql, 0, -3) . " )";
		if ($orderBy !== null) {
			$sql .= " order by " . $orderBy . " " . $orden;
		}

		// Add LIMIT offset, rowcount if rowcount is set
		if (!is_null($rowcount)) {
			$sql .= " LIMIT " . $offset . "," . $rowcount;
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
