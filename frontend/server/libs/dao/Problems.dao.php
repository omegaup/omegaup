<?php

require_once("base/Problems.dao.base.php");
require_once("base/Problems.vo.base.php");
/** Page-level DocBlock .
  * 
  * @author alanboy
  * @package docs
  * 
  */
/** Problems Data Access Object (DAO).
  * 
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para 
  * almacenar de forma permanente y recuperar instancias de objetos {@link Problems }. 
  * @author alanboy
  * @access public
  * @package docs
  * 
  */
class ProblemsDAO extends ProblemsDAOBase
{
	public static final function byUserType($user_type, $order, $mode, $offset, $rowcount, $query, $user_id = null) {
		global $conn;
		if (!is_null($query)) {
			$escaped_query = mysql_real_escape_string($query);
		}

		// Just in case.
		if ($mode !== 'asc' && $mode !== 'desc') {
			$mode = 'desc';
		}

		// Use BINARY mode to force case sensitive comparisons when ordering by title.
		$collation = ($order === 'title') ? 'COLLATE utf8_bin' : '';

		$result = null;
		if ($user_type === USER_ADMIN) {
			$args = array($user_id);
			$sql = "
				SELECT
					100 / LOG2(GREATEST(accepted, 1) + 1)	AS points,
					accepted / GREATEST(1, submissions)		AS ratio,
					ROUND(100 * COALESCE(ps.score, 0))		AS score,
					p.*
				FROM
					Problems p
				LEFT JOIN (
					SELECT
						Problems.problem_id,
						MAX(Runs.score) AS score
					FROM 
						Problems
					INNER JOIN
						Runs ON Runs.user_id = ? AND Runs.problem_id = Problems.problem_id
					GROUP BY
						Problems.problem_id
				) ps ON ps.problem_id = p.problem_id";

			if (!is_null($query)) {
				$sql .= " WHERE title LIKE '%$escaped_query%'";
			}

			$sql .= " ORDER BY `$order` $collation $mode ";

			if (!is_null($rowcount)) {
				$sql .= "LIMIT ?, ?";
				array_push($args, $offset, $rowcount);
			}

			$result = $conn->Execute($sql, $args);
		} else if ($user_type === USER_NORMAL && !is_null($user_id)) {
			$like_query = '';
			if (!is_null($query)) {
				$like_query = " AND title LIKE '%$escaped_query%'";
			}
			$args = array($user_id, $user_id, $user_id);
			$sql = "
				SELECT
					100 / LOG2(GREATEST(accepted, 1) + 1)	AS points,
					accepted / GREATEST(1, submissions)		AS ratio,
					ROUND(100 * COALESCE(ps.score, 0), 2)	AS score,
					p.*
				FROM
					Problems p
				LEFT JOIN (
					SELECT
						p.problem_id,
						MAX(r.score) AS score
					  FROM 
						Problems p
						INNER JOIN
						Runs r ON r.user_id = ? AND r.problem_id = p.problem_id
						GROUP BY
							p.problem_id
				) ps ON ps.problem_id = p.problem_id
				LEFT JOIN
					User_Roles ur ON ur.user_id = ? AND p.problem_id = ur.contest_id
				WHERE
					(public = 1 OR p.author_id = ? OR ur.role_id = 3) $like_query
				ORDER BY
					`$order` $collation $mode";

			if (!is_null($rowcount)) {
				$sql .= " LIMIT ?, ?";
				array_push($args, $offset, $rowcount);
			}
			$result = $conn->Execute($sql, $args);
		} else if ($user_type === USER_ANONYMOUS) {
			$like_query = '';
			if (!is_null($query)) {
				$like_query = " AND title LIKE '%{$escaped_query}%'";
			}
			$sql = "
					SELECT
						0 AS score,
						100 / LOG2(GREATEST(accepted, 1) + 1) AS points,
						accepted / GREATEST(1, submissions)   AS ratio,
						Problems.*
					FROM
						Problems
					WHERE
						public = 1 $like_query
					ORDER BY
						 `$order` $collation $mode";

			$args = array();
			if (!is_null($rowcount)) {
				$sql .= " LIMIT ?, ?";
				$args = array($offset, $rowcount);
			}

			$result = $conn->Execute($sql, $args);
		}

		// Only these fields (plus score, points and ratio) will be returned.
		$filters = array('title', 'submissions', 'accepted', 'alias', 'public');
		$problems = array();
		if (!is_null($result)) {
			foreach ($result as $row) {
				$temp = new Problems($row);
				$problem = $temp->asFilteredArray($filters);

				// score, points and ratio are not actually fields of a Problems object.
				$problem['score'] = $row['score'];
				$problem['points'] = $row['points'];
				$problem['ratio'] = $row['ratio'];
				array_push($problems, $problem);
			}
		}
		return $problems;
	}

	/* byPage: search and return all the results by size and number of page and other attributes */
	public static final function byPage( $sizePage , $noPage , $condition = null , $serv = null, $orderBy = null, $orden = 'ASC')
	{	
		global $conn;
		$val = array($condition);		
		$sql = "SELECT count(*) from Problems WHERE $condition"; 	
		$rs = $conn->getRow($sql);
		$total = $rs[0];
		
		//problem_id	public	author_id	title	alias	validator	server	remote_id	
		//time_limit	memory_limit	visits	submissions	accepted	difficulty	creation_date	source	order
		$ord = array("problem_id"=>1,
					 "public"=>0,
					 "author_id"=>0,
					 "title"=>1,
					 "alias"=>0,
					 "validator"=>0,
					 "server"=>0,
					 "remote_id"=>0,
					 "time_limit"=>1,
					 "memory_limit"=>1,
					 "visits"=>1,
					 "submissions"=>1,
					 "accepted"=>1,
					 "difficulty"=>1,
					 "creation_date"=>0,
					 "source"=>0,
					 "order"=>0
					 );					 
		if(!isset($ord[$orderBy]))
			$orderBy = "title";
		else if($ord[$orderBy] == 0)$orderBy = "title";
		
		$total_paginas = ceil($total/$sizePage);
		$inicio = $sizePage * ($noPage - 1);
		$limite = $inicio + $sizePage;
		$val = array(  $inicio, $sizePage);		
		$sql = "SELECT * from Problems WHERE $condition ORDER BY $orderBy $orden LIMIT ?, ?"; 
		
		$rs = $conn->Execute($sql, $val);
		$ar = array();
		$info = "Mostrando $noPage de $total_paginas p&aacute;ginas. De ".( $inicio + 1 )." 
		a $limite de  $total registros.";
		$bar_size = 5;//sera 1 mas
		
		if( ( $noPage - $bar_size/2 ) >= ($total_paginas  - $bar_size )) $i = $total_paginas - $bar_size;		
		else if( ( $noPage - $bar_size/2 )>1)$i = ceil($noPage - $bar_size/2);
		else $i=1;
		$cont = 0;
		$bar = "<a href='?noPage=1&serv=$serv&order=$orderBy'> << </a>";
		for(;$i <= $total_paginas && $cont <= $bar_size ; $i++  ){
			if($noPage == $i) $bar .= " $i ";
			else $bar .= "<a href='?noPage=$i&serv=$serv&order=$orderBy'> $i </a>";
			$cont ++;
		}
		$bar .= "<a href='?noPage=$total_paginas&serv=$serv&order=$orderBy'> >> </a>";
		array_push( $ar, $info);
		array_push( $ar, $bar);
		foreach ($rs as $foo) {		
    		array_push( $ar, new Problems($foo));
		}
		return $ar;
	}
	
	public static final function getByAlias($alias)
	{
		$sql = "SELECT * FROM Problems WHERE (alias = ? ) LIMIT 1;";
		$params = array(  $alias );
                
		global $conn;
		$rs = $conn->GetRow($sql, $params);
		if(count($rs)==0)
                {
                    return NULL;
                }
                
                $contest = new Problems( $rs );

                return $contest;
	}

	public static final function searchByAlias($alias)
	{
		global $conn;
		$quoted = $conn->Quote($alias);
		
		if (strpos($quoted, "'") !== FALSE) {
			$quoted = substr($quoted, 1, strlen($quoted) - 2);
		}

		$sql = "SELECT * FROM Problems WHERE (alias LIKE '%$quoted%' OR title LIKE '%$quoted%') LIMIT 0,10;";
		$rs = $conn->Execute($sql);

		$result = array();

		foreach ($rs as $r) {
			array_push($result, new Problems($r));
		}

		return $result;
	}

	public static final function getPracticeDeadline($id) {
		global $conn;

		$sql = "SELECT COALESCE(UNIX_TIMESTAMP(MAX(finish_time)), 0) FROM Contests c INNER JOIN Contest_Problems cp USING(contest_id) WHERE cp.problem_id = ?";
		return $conn->GetOne($sql, $id);
	}
	
	public static final function getProblemsSolved($id) {
		global $conn;
		
		$sql = "SELECT DISTINCT `Problems`.* FROM `Problems` INNER JOIN `Runs` ON `Problems`.problem_id = `Runs`.problem_id WHERE `Runs`.verdict = 'AC' and `Runs`.test = 0 and `Runs`.user_id = ? ORDER BY `Problems`.problem_id DESC";
		$val = array($id);
		$rs = $conn->Execute($sql, $val);
		
		$result = array();

		foreach ($rs as $r) {
			array_push($result, new Problems($r));
		}

		return $result;		
	}
	
	public static function getPrivateCount(Users $user) {
		$sql = "SELECT count(*) as Total FROM Problems WHERE public = 0 and (author_id = ?);";		
		$params = array($user->getUserId());
                
		global $conn;
		$rs = $conn->GetRow($sql, $params);				                        
		
		if (!array_key_exists("Total", $rs)) {
			return 0;
		}
 		
        return $rs["Total"];
	}
}
