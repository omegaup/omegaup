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
    public static function getAdmins($acl_id) {
        $sql = '
            SELECT
                g.alias, g.name, gr.acl_id AS acl
            FROM
                Group_Roles gr
            INNER JOIN
                Groups g ON g.group_id = gr.group_id
            WHERE
                gr.role_id = ? AND gr.acl_id IN (?, ?);';
        $params = array(
            Authorization::ADMIN_ROLE,
            Authorization::SYSTEM_ACL,
            $acl_id,
        );

        global $conn;
        $admins = $conn->GetAll($sql, $params);

        for ($i = 0; $i < count($admins); $i++) {
            if ($admins[$i]['acl'] == Authorization::SYSTEM_ACL) {
                $admins[$i]['role'] = 'site-admin';
            } else {
                $admins[$i]['role'] = 'admin';
            }
            unset($admins[$i]['acl']);
        }

        return $admins;
    }

    private static function isAdmin($user_id, $acl_id) {
        $sql = '
            SELECT
                COUNT(*)
            FROM
                Group_Roles gr
            INNER JOIN
                Groups_Users gu ON gu.group_id = gr.group_id
            WHERE
                gu.user_id = ? AND gr.role_id = ? AND gr.acl_id IN (?, ?);';
        $params = array(
            $user_id,
            Authorization::ADMIN_ROLE,
            Authorization::SYSTEM_ACL,
            $acl_id,
        );
        global $conn;
        return $conn->GetOne($sql, $params) > 0;
    }

    public static function getContestAdmins(Contests $contest) {
        return self::getAdmins($contest->acl_id);
    }

    public static function getProblemAdmins(Problems $problem) {
        return self::getAdmins($problem->acl_id);
    }

    public static function isContestAdmin($user_id, Contests $contest) {
        return self::isAdmin($user_id, $contest->acl_id);
    }

    public static function isProblemAdmin($user_id, Problems $problem) {
        return self::isAdmin($user_id, $problem->acl_id);
    }

    public static function isSystemAdmin($user_id) {
        return self::isAdmin($user_id, Authorization::SYSTEM_ACL);
    }
}
