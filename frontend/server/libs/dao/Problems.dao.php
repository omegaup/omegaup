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
	public static final function byUserType($user_type, $order, $mode, $offset,
			$rowcount, $query, $user_id, $tag, &$total) {
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

		$select = "";
		$sql= "";
		$args = array();

		if ($user_type === USER_ADMIN) {
			$args = array($user_id);
			$select = "
				SELECT
					100 / LOG2(GREATEST(accepted, 1) + 1)	AS points,
					accepted / GREATEST(1, submissions)		AS ratio,
					ROUND(100 * COALESCE(ps.score, 0))		AS score,
					p.*";
			$sql = "
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

			$added_where = false;
			if (!is_null($tag)) {
				$sql .= " INNER JOIN Problems_Tags pt ON pt.problem_id = p.problem_id";
				$sql .= " INNER JOIN Tags t ON pt.tag_id = t.tag_id";
				$sql .= " WHERE t.name = ?";
				$args[] = $tag;
				$added_where = true;
			}

			if (!is_null($query)) {
				if (!$added_where) {
					$sql .= " WHERE";
				} else {
					$sql .= " AND";
				}
				$sql .= " title LIKE '%$escaped_query%'";
			}
		} else if ($user_type === USER_NORMAL && !is_null($user_id)) {
			$like_query = '';
			if (!is_null($query)) {
				$like_query = " AND p.title LIKE '%$escaped_query%'";
			}
			$select = "
				SELECT
					100 / LOG2(GREATEST(p.accepted, 1) + 1)	AS points,
					p.accepted / GREATEST(1, p.submissions)		AS ratio,
					ROUND(100 * COALESCE(ps.score, 0), 2)	AS score,
					p.*";
			$sql = "
				FROM
					Problems p
				LEFT JOIN (
					SELECT
						pi.problem_id,
						MAX(r.score) AS score
					FROM 
						Problems pi
					INNER JOIN
						Runs r ON r.user_id = ? AND r.problem_id = pi.problem_id
					GROUP BY
						pi.problem_id
				) ps ON ps.problem_id = p.problem_id
				LEFT JOIN
					User_Roles ur ON ur.user_id = ? AND p.problem_id = ur.contest_id";
			$args = array($user_id, $user_id);

			if (!is_null($tag)) {
				$sql .= " INNER JOIN Problems_Tags pt ON pt.problem_id = p.problem_id";
				$sql .= " INNER JOIN Tags t ON pt.tag_id = t.tag_id";
				$sql .= " WHERE t.name = ? AND pt.public = 1 AND";
				$args[] = $tag;
			} else {
				$sql .= " WHERE";
			}

			$sql .= "
				(p.public = 1 OR p.author_id = ? OR ur.role_id = 3) $like_query";
			$args[] = $user_id;
		} else if ($user_type === USER_ANONYMOUS) {
			$like_query = '';
			if (!is_null($query)) {
				$like_query = " AND p.title LIKE '%{$escaped_query}%'";
			}
			$select = "
					SELECT
						0 AS score,
						100 / LOG2(GREATEST(p.accepted, 1) + 1) AS points,
						accepted / GREATEST(1, p.submissions)   AS ratio,
						p.*";
			$sql = "
					FROM
						Problems p";

			if (!is_null($tag)) {
				$sql .= " INNER JOIN Problems_Tags pt ON pt.problem_id = p.problem_id";
				$sql .= " INNER JOIN Tags t ON pt.tag_id = t.tag_id";
				$sql .= " WHERE t.name = ? AND pt.public = 1 AND";
				$args[] = $tag;
			} else {
				$sql .= " WHERE";
			}

			$sql .= " p.public = 1 $like_query";
		}

		$total = $conn->GetOne("SELECT COUNT(*) $sql", $args);

		// Reset the offset to 0 if out of bounds.
		if ($offset < 0 || $offset > $total) {
			$offset = 0;
		}

		if ($order == 'problem_id') {
			$sql .= " ORDER BY p.problem_id $collation $mode";
		} else {
			$sql .= " ORDER BY `$order` $collation $mode";
		}
		$sql .= " LIMIT ?, ?";
		$args[] = $offset;
		$args[] = $rowcount;

		$result = $conn->Execute("$select $sql", $args);

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
				$problem['tags'] = ProblemsDAO::getTagsForProblem($temp, true);
				array_push($problems, $problem);
			}
		}
		return $problems;
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

	public static final function getTagsForProblem($problem, $public) {
		global $conn;

		$sql = "SELECT
			t.name
		FROM
			Problems_Tags pt
		INNER JOIN
			Tags t ON t.tag_id = pt.tag_id
		WHERE
			pt.problem_id = ?";
		if ($public) {
			$sql .= " AND pt.public = 1";
		}

		$rs = $conn->Execute($sql, $problem->problem_id);
		$result = array();

		foreach ($rs as $r) {
			$result[] = $r['name'];
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
