<?php

include("base/Problem_Viewed.dao.base.php");
include("base/Problem_Viewed.vo.base.php");
/** ProblemViewed Data Access Object (DAO).
  *
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
  * almacenar de forma permanente y recuperar instancias de objetos {@link ProblemViewed }.
  * @access public
  *
  */
class ProblemViewedDAO extends ProblemViewedDAOBase
{
	public static function MarkProblemViewed($user_id, $problem_id) {
		global $conn;
		$sql = 'INSERT IGNORE Problem_Viewed (user_id, problem_id) VALUES (?, ?);';
		$values = array($user_id, $problem_id);
		$conn->Execute($sql, $values);
	}
}
