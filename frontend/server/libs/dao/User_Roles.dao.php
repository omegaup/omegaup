<?php

require_once("base/User_Roles.dao.base.php");
require_once("base/User_Roles.vo.base.php");
/** Page-level DocBlock .
  *
  * @author alanboy
  * @package docs
  *
  */
/** UserRoles Data Access Object (DAO).
  *
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
  * almacenar de forma permanente y recuperar instancias de objetos {@link UserRoles }.
  * @author alanboy
  * @access public
  * @package docs
  *
  */
class UserRolesDAO extends UserRolesDAOBase {
	public static function getContestAdmins(Contests $contest) {
		$sql = '
			SELECT
				u.username, ur.role_id AS role
			FROM
				User_Roles ur
			INNER JOIN
				Users u ON u.user_id = ur.user_id
			WHERE
				ur.role_id = 1 OR ur.role_id = 2 AND ur.contest_id = ?;';
		$params = array($contest->contest_id);

		global $conn;
		$admins = $conn->GetAll($sql, $params);

		$sql = '
			SELECT
				u.username
			FROM
				Contests c
			INNER JOIN
				Users u ON u.user_id = c.director_id
			WHERE
			c.contest_id = ?;';
		$params = array($contest->contest_id);
		$director = $conn->GetOne($sql, $params);

		$found = false;
		for ($i = 0; $i < count($admins); $i++) {
			if ($admins[$i]['role'] == ADMIN_ROLE)	{
				$admins[$i]['role'] = 'site-admin';
			} else if ($admins[$i]['username'] == $director) {
				$admins[$i]['role'] = 'director';
				$found = true;
			} else {
				$admins[$i]['role'] = 'admin';
			}
		}

		if ($found) {
			array_push($admins, array('username' => $director, 'role' => 'director'));
		}

		return $admins;
	}

	public static function getProblemAdmins(Problems $problem) {
		$sql = '
			SELECT
				u.username, ur.role_id AS role
			FROM
				User_Roles ur
			INNER JOIN
				Users u ON u.user_id = ur.user_id
			WHERE
				ur.role_id = 1 OR ur.role_id = 3 AND ur.contest_id = ?;';
		$params = array($problem->problem_id);

		global $conn;
		$admins = $conn->GetAll($sql, $params);

		$sql = '
			SELECT
				u.username
			FROM
				Problems p
			INNER JOIN
				Users u ON u.user_id = p.author_id
			WHERE
			p.problem_id = ?;';
		$params = array($problem->problem_id);
		$author = $conn->GetOne($sql, $params);

		$found = false;
		for ($i = 0; $i < count($admins); $i++) {
			if ($admins[$i]['role'] == ADMIN_ROLE)	{
				$admins[$i]['role'] = 'site-admin';
			} else if ($admins[$i]['username'] == $author) {
				$admins[$i]['role'] = 'author';
				$found = true;
			} else {
				$admins[$i]['role'] = 'admin';
			}
		}

		if (!$found) {
			array_push($admins, array('username' => $author, 'role' => 'author'));
		}

		return $admins;
	}
}
