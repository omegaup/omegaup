<?php

require_once("base/Coder_Of_The_Month.dao.base.php");
require_once("base/Coder_Of_The_Month.vo.base.php");
/** Page-level DocBlock .
 * 
 * @author alanboy
 * @package docs
 * 
 */

/** CoderOfTheMonth Data Access Object (DAO).
 * 
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita para 
 * almacenar de forma permanente y recuperar instancias de objetos {@link CoderOfTheMonth }. 
 * @author alanboy
 * @access public
 * @package docs
 * 
 */
class CoderOfTheMonthDAO extends CoderOfTheMonthDAOBase {

	/**
	 * Gets the user that solved more problems during last month
	 * 
	 * @global type $conn
	 * @param string (date) $firstDay
	 * @return null|Users
	 */
	public static function calculateCoderOfTheMonth($firstDay) {

		$endTime = $firstDay;
		$startTime = null;
		
		$lastMonth = intval(date('m')) - 1;		
		
		if ($lastMonth === 0) {
			// First month of the year, we need to check into last month of last year.
			$lastYear = intval(date('Y')) - 1;
			$startTime = date($lastYear . '-12-01');
		} else {			
			$startTime = date('Y-' . $lastMonth . '-01');
		}
		
		$sql = "
			SELECT
				username, name, up.user_id, COUNT(ps.problem_id) ProblemsSolved, SUM(ps.points) score
			FROM
				(
					SELECT DISTINCT
						r.user_id, r.problem_id
					FROM
						Runs r
					WHERE
						r.veredict = 'AC' AND r.test = 0 AND 
						r.time >= ? AND 
						r.time <= ?
				) AS up
			INNER JOIN
				(
					SELECT
						p.problem_id, ROUND(100 / LOG(2, accepted+1) , 0) AS points
					FROM
						Problems p

				) AS ps ON ps.problem_id = up.problem_id
			INNER JOIN
				Users u ON u.user_id = up.user_id 
			GROUP BY
				username
			ORDER BY
				score DESC
			LIMIT 1
		";

		$val = array($startTime, $endTime);

		global $conn;
		$rs = $conn->GetRow($sql, $val);		
		if (count($rs) == 0) {			
			return NULL;
		}
		
		$totalCount = $rs['ProblemsSolved'];
		$user = UsersDAO::getByPK($rs['user_id']);
		$score = $rs['score'];
		

		return array("totalCount" => $totalCount, "user" => $user, "score" => $score);
	}
}
