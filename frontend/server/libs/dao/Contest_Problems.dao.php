<?php

require_once("base/Contest_Problems.dao.base.php");
require_once("base/Contest_Problems.vo.base.php");
/** Page-level DocBlock .
  * 
  * @author alanboy
  * @package docs
  * 
  */
/** ContestProblems Data Access Object (DAO).
  * 
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para 
  * almacenar de forma permanente y recuperar instancias de objetos {@link ContestProblems }. 
  * @author alanboy
  * @access public
  * @package docs
  * 
  */
class ContestProblemsDAO extends ContestProblemsDAOBase
{
	/*
	 * Get contest problems including contest alias, points, and order
	 */
	public static final function GetContestProblems($contest_id) {
		// Build SQL statement
		$sql = 'SELECT p.problem_id, p.alias, cp.points, cp.order ' .
		       'FROM Problems p ' .
		       'INNER JOIN Contest_Problems cp ON cp.problem_id = p.problem_id ' .
		       'WHERE cp.contest_id = ? ' .
		       'ORDER BY cp.`order`;';
		$val = array($contest_id);

		global $conn;
		return $conn->GetAll($sql, $val);
	}

	/*
	 * 
	 * Get relevant problems including contest alias
	 */
	public static final function GetRelevantProblems($contest_id)
	{

		// Build SQL statement
		$sql = "SELECT Problems.problem_id, alias from Problems INNER JOIN ( SELECT Contest_Problems.problem_id from Contest_Problems WHERE ( Contest_Problems.contest_id = ? ) ) ProblemsContests ON Problems.problem_id = ProblemsContests.problem_id ";
		$val = array($contest_id);

		global $conn;
		$rs = $conn->Execute($sql, $val);

		$ar = array();
		foreach ($rs as $foo) {
			$bar =  new Problems($foo);
			array_push( $ar,$bar);
		}

		return $ar;
	}
}
