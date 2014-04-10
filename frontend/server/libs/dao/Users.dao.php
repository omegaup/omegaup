<?php

require_once("base/Users.dao.base.php");
require_once("base/Users.vo.base.php");
/** Page-level DocBlock .
  * 
  * @author alanboy
  * @package docs
  * 
  */
/** Users Data Access Object (DAO).
  * 
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para 
  * almacenar de forma permanente y recuperar instancias de objetos {@link Users }. 
  * @author alanboy
  * @access public
  * @package docs
  * 
  */
class UsersDAO extends UsersDAOBase
{


	public static function FindByEmail($email) {
		global  $conn;
		$sql = "select u.* from Users u, Emails e where e.email = ? and e.user_id = u.user_id";
		$params = array( $email );
		$rs = $conn->GetRow($sql, $params);
		if(count($rs)==0)return NULL;
		return new Users( $rs );
	}

	public static function FindByUsername($username) {
		$vo_Query = new Users( array( 
			"username" => $username
		));

		$a_Results = UsersDAO::search( $vo_Query );

		if (sizeof($a_Results) != 1) {
			return NULL;
		}

		return array_pop( $a_Results );
	}
	
	public static function FindByUsernameOrName($usernameOrName) {
		
		global  $conn;
		$escapedStr = mysql_real_escape_string($usernameOrName);
		$sql = "select DISTINCT u.* from Users u where u.username LIKE '%{$escapedStr}%' or u.name LIKE '%{$escapedStr}%' LIMIT 10";				
		
		$rs = $conn->Execute($sql);
		$ar = array();
		foreach ($rs as $foo) {
			$bar =  new Users($foo);
    		array_push( $ar,$bar);    		
		}
		return $ar;		
	}
	
	public static function GetRankByProblemsSolved($limit = 100, $offset = 0, Users $user = null) {
		
		$filterByUser = !is_null($user);
		
		global  $conn;
		$conn->Execute("SET @prev_value = NULL;");
		$conn->Execute("SET @rank_count = 0;");
		$sql = "SELECT ProblemsSolved, username, name, rank, user_id FROM (
					SELECT ProblemsSolved, username, name, user_id,
					CASE
						WHEN @prev_value = ProblemsSolved THEN @rank_count
						WHEN @prev_value := ProblemsSolved THEN @rank_count := @rank_count + 1
					END AS rank

					 FROM (
						SELECT COUNT( * ) AS ProblemsSolved, Users . *
									FROM Users
									INNER JOIN (
										SELECT COUNT( * ) AS TotalPerProblem, problem_id, user_id
										FROM Runs
										WHERE Runs.veredict =  'AC'
										AND Runs.test =0
										GROUP BY user_id, problem_id
										ORDER BY TotalPerProblem DESC
									) AS p ON p.user_id = Users.user_id

									WHERE Users.main_email_id IS NOT NULL 
									GROUP BY user_id
									ORDER BY ProblemsSolved DESC 
						 ) AS UsersProblemsSolved
					) AS Rank ";
		($filterByUser) ? $sql .= "WHERE user_id = ? " : $sql .= "ORDER BY Rank ASC LIMIT $offset, $limit";		
		
		$rs = null;
		if ($filterByUser) {
			$params = array($user->user_id);			
			$rs = $conn->Execute($sql, $params);
		} else {
			$rs = $conn->Execute($sql);
		}
		
		$ar = array();
		foreach ($rs as $foo) {			
			$bar =  new Users($foo);
			$result = array("user" => $bar, "problems_solved" =>  $foo["ProblemsSolved"], "rank" => $foo["rank"]);
    		array_push( $ar, $result);    		
		}
		return $ar;	
	}
}
