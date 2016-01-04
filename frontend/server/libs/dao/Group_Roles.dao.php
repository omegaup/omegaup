<?php

include('base/Group_Roles.dao.base.php');
include('base/Group_Roles.vo.base.php');
/** GroupRoles Data Access Object (DAO).
  *
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
  * almacenar de forma permanente y recuperar instancias de objetos {@link GroupRoles }.
  * @access public
  *
  */
class GroupRolesDAO extends GroupRolesDAOBase
{
    public static function getContestAdmins(Contests $contest) {
        $sql = '
			SELECT
				g.alias, g.name, gr.role_id AS role
			FROM
				Group_Roles gr
			INNER JOIN
				Groups g ON g.group_id = gr.group_id
			WHERE
				gr.role_id = 1 OR gr.role_id = 2 AND gr.contest_id = ?;';
        $params = array($contest->contest_id);

        global $conn;
        $admins = $conn->GetAll($sql, $params);

        for ($i = 0; $i < count($admins); $i++) {
            if ($admins[$i]['role'] == ADMIN_ROLE) {
                $admins[$i]['role'] = 'site-admin';
            } else {
                $admins[$i]['role'] = 'admin';
            }
        }

        return $admins;
    }

    public static function getProblemAdmins(Problems $problem) {
        $sql = '
			SELECT
				g.alias, g.name, gr.role_id AS role
			FROM
				Group_Roles gr
			INNER JOIN
				Groups g ON g.group_id = gr.group_id
			WHERE
				gr.role_id = 1 OR gr.role_id = 3 AND gr.contest_id = ?;';
        $params = array($problem->problem_id);

        global $conn;
        $admins = $conn->GetAll($sql, $params);

        for ($i = 0; $i < count($admins); $i++) {
            if ($admins[$i]['role'] == ADMIN_ROLE) {
                $admins[$i]['role'] = 'site-admin';
            } else {
                $admins[$i]['role'] = 'admin';
            }
        }

        return $admins;
    }

    public static function IsContestAdmin($user_id, Contests $contest) {
        $sql = '
			SELECT
				COUNT(*)
			FROM
				Group_Roles gr
			INNER JOIN
				Groups_Users gu ON gu.group_id = gr.group_id
			WHERE
				gu.user_id = ? AND
				(gr.role_id = 1 OR gr.role_id = 2 AND gr.contest_id = ?);';
        $params = array($user_id, $contest->contest_id);
        global $conn;
        return $conn->GetOne($sql, $params) > 0;
    }

    public static function IsProblemAdmin($user_id, Problems $problem) {
        $sql = '
			SELECT
				COUNT(*)
			FROM
				Group_Roles gr
			INNER JOIN
				Groups_Users gu ON gu.group_id = gr.group_id
			WHERE
				gu.user_id = ? AND
				(gr.role_id = 1 OR gr.role_id = 3 AND gr.contest_id = ?);';
        $params = array($user_id, $problem->problem_id);
        global $conn;
        return $conn->GetOne($sql, $params) > 0;
    }

    public static function IsSystemAdmin($user_id) {
        $sql = '
			SELECT
				COUNT(*)
			FROM
				Group_Roles gr
			INNER JOIN
				Groups_Users gu ON gu.group_id = gr.group_id
			WHERE
				gu.user_id = ? AND gr.role_id = 1;';
        $params = array($user_id);
        global $conn;
        return $conn->GetOne($sql, $params) > 0;
    }
}
