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
class GroupRolesDAO extends GroupRolesDAOBase {
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
        $params = [
            Authorization::ADMIN_ROLE,
            Authorization::SYSTEM_ACL,
            $acl_id,
        ];

        $admins = MySQLConnection::getInstance()->GetAll($sql, $params);

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

    public static function hasRole($identity_id, $acl_id, $role_id) {
        $sql = '
            SELECT
                COUNT(*) > 0
            FROM
                Group_Roles gr
            INNER JOIN
                Groups_Identities gi ON gi.group_id = gr.group_id
            WHERE
                gi.identity_id = ? AND gr.role_id = ? AND gr.acl_id IN (?, ?);';
        $params = [
            $identity_id,
            $role_id,
            Authorization::SYSTEM_ACL,
            $acl_id,
        ];
        return MySQLConnection::getInstance()->GetOne($sql, $params);
    }

    public static function isContestant($identity_id, $acl_id) {
        $sql = '
            SELECT
                COUNT(*) > 0
            FROM
                Group_Roles gr
            INNER JOIN
                Groups_Identities gi ON gi.group_id = gr.group_id
            WHERE
                gi.identity_id = ? AND gr.role_id = ? AND gr.acl_id = ?;';
        $params = [
            $identity_id,
            Authorization::CONTESTANT_ROLE,
            $acl_id,
        ];
        return MySQLConnection::getInstance()->GetOne($sql, $params);
    }

    public static function getContestAdmins(Contests $contest) {
        return self::getAdmins($contest->acl_id);
    }

    public static function getCourseAdmins(Courses $course) {
        return self::getAdmins($course->acl_id);
    }

    public static function getProblemAdmins(Problems $problem) {
        return self::getAdmins($problem->acl_id);
    }

    public static function getSystemRoles($identity_id) {
        $sql = '
            SELECT
                r.name
            FROM
                Group_Roles gr
            INNER JOIN
                Groups_Identities gi ON gi.group_id = gr.group_id
            INNER JOIN
                Roles r ON r.role_id = gr.role_id
            WHERE
                gi.identity_id = ? AND gr.acl_id = ?;';
        $params = [
            $identity_id,
            Authorization::SYSTEM_ACL,
        ];

        $roles = [];
        foreach (MySQLConnection::getInstance()->GetAll($sql, $params) as $role) {
            $roles[] = $role['name'];
        }
        return $roles;
    }
}
