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
			       'c.message, c.answer, c.time, c.public, true can_answer ' .
			       'FROM Clarifications c ' .
			       'INNER JOIN Users u ON u.user_id = c.author_id ';
		} else {
			$sql = 'SELECT c.clarification_id, p.alias problem_alias, c.message, ' .
			       'c.time, c.answer, c.public, false can_answer ' .
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
}
