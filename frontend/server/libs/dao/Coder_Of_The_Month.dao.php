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
			SELECT COUNT( * ) TotalSolved, Users . user_id 
			FROM (


				SELECT user_id, problem_id, COUNT( * ) AS Total

				FROM Runs

				WHERE TIME >= ?

				AND TIME <= ?

				AND veredict = 'AC'

				AND Test =0

				GROUP BY user_id, problem_id

				ORDER BY Total DESC
				) T
			INNER JOIN Users ON T.user_id = Users.user_id
			GROUP BY T.user_id
			ORDER BY TotalSolved DESC 
			LIMIT 1 
			";

		$val = array($startTime, $endTime);

		global $conn;
		$rs = $conn->GetRow($sql, $val);		
		if (count($rs) == 0) {			
			return NULL;
		}
		
		$totalCount = $rs[0];				
		$user = UsersDAO::getByPK($rs[1]);

		return array("totalCount" => $totalCount, "user" => $user);
	}

}
