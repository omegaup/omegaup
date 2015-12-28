<?php

require_once("base/Clarifications.dao.base.php");
require_once("base/Clarifications.vo.base.php");
/** Page-level DocBlock .
  *
  * @author alanboy
  * @package docs
  *
  */
/** Clarifications Data Access Object (DAO).
  *
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
  * almacenar de forma permanente y recuperar instancias de objetos {@link Clarifications }.
  * @author alanboy
  * @access public
  * @package docs
  *
  */
class ClarificationsDAO extends ClarificationsDAOBase {
	public static final function GetContestClarifications($contest_id, $admin, $user_id, $offset, $rowcount) {
		$sql = '';
		if ($admin) {
			$sql = 'SELECT c.clarification_id, p.alias problem_alias, u.username author, ' .
			       'c.message, c.answer, UNIX_TIMESTAMP(c.time) `time`, c.public ' .
			       'FROM Clarifications c ' .
			       'INNER JOIN Users u ON u.user_id = c.author_id ';
		} else {
			$sql = 'SELECT c.clarification_id, p.alias problem_alias, c.message, ' .
			       'UNIX_TIMESTAMP(c.time) `time`, c.answer, c.public ' .
			       'FROM Clarifications c ';
		}
		$sql .= 'INNER JOIN Problems p ON p.problem_id = c.problem_id ' .
		        'WHERE ' .
		        'c.contest_id = ? ';
		$val = array($contest_id);

		if (!$admin) {
			$sql .= 'AND (c.public = 1 OR c.author_id = ?) ';
			$val[] = $user_id;
		}

		$sql .= 'ORDER BY c.answer IS NULL DESC, c.clarification_id DESC ';
		if (!is_null($offset)) {
			$sql .= 'LIMIT ?, ?';
			$val[] = (int)$offset;
			$val[] = (int)$rowcount;
		}

		global $conn;
		return $conn->GetAll($sql, $val);
	}

	public static final function GetProblemClarifications($problem_id, $admin, $user_id, $offset, $rowcount) {
		$sql = '';
		if ($admin) {
			$sql = 'SELECT c.clarification_id, con.alias contest_alias, u.username author, ' .
			       'c.message, c.answer, UNIX_TIMESTAMP(c.time) `time`, c.public ' .
			       'FROM Clarifications c ' .
			       'INNER JOIN Users u ON u.user_id = c.author_id ';
		} else {
			$sql = 'SELECT c.clarification_id, con.alias contest_alias, c.message, ' .
			       'UNIX_TIMESTAMP(c.time) `time`, c.answer, c.public ' .
			       'FROM Clarifications c ';
		}
		$sql .= 'INNER JOIN Contests con ON con.contest_id = c.contest_id ' .
		        'WHERE ' .
		        'c.problem_id = ? ';
		$val = array($problem_id);

		if (!$admin) {
			$sql .= 'AND (c.public = 1 OR c.author_id = ?) ';
			$val[] = $user_id;
		}

		$sql .= 'ORDER BY c.answer IS NULL DESC, c.clarification_id DESC ';
		if (!is_null($offset)) {
			$sql .= 'LIMIT ?, ?';
			$val[] = (int)$offset;
			$val[] = (int)$rowcount;
		}

		global $conn;
		return $conn->GetAll($sql, $val);
	}
}
