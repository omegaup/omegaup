<?php

require_once('base/User_Roles.dao.base.php');
require_once('base/User_Roles.vo.base.php');
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
    private static function getAdmins($acl_id) {
        $sql = '
            SELECT
                u.username, ur.acl_id AS acl
            FROM
                User_Roles ur
            INNER JOIN
                Users u ON u.user_id = ur.user_id
            WHERE
                ur.role_id = ? AND ur.acl_id IN (?, ?);';
        $params = [
            Authorization::ADMIN_ROLE,
            Authorization::SYSTEM_ACL,
            $acl_id,
        ];

        global $conn;
        $admins = $conn->GetAll($sql, $params);

        $sql = '
            SELECT
                u.username
            FROM
                ACLs a
            INNER JOIN
                Users u ON u.user_id = a.owner_id
            WHERE
                a.acl_id = ?;';
        $params = [$acl_id];
        $owner = $conn->GetOne($sql, $params);

        $found = false;
        for ($i = 0; $i < count($admins); $i++) {
            if ($admins[$i]['acl'] == Authorization::SYSTEM_ACL) {
                $admins[$i]['role'] = 'site-admin';
            } elseif ($admins[$i]['username'] == $owner) {
                $admins[$i]['role'] = 'owner';
                $found = true;
            } else {
                $admins[$i]['role'] = 'admin';
            }
            unset($admins[$i]['acl']);
        }

        if (!$found) {
            array_push($admins, ['username' => $owner, 'role' => 'owner']);
        }

        return $admins;
    }

    public static function isAdmin($user_id, $acl_id) {
        $sql = '
            SELECT
                COUNT(*)
            FROM
                User_Roles ur
            WHERE
                ur.user_id = ? AND ur.role_id = ? AND ur.acl_id IN (?, ?);';
        $params = [
            $user_id,
            Authorization::ADMIN_ROLE,
            Authorization::SYSTEM_ACL,
            $acl_id,
        ];
        global $conn;
        return $conn->GetOne($sql, $params) > 0;
    }

    public static function getContestAdmins(Contests $contest) {
        return self::getAdmins($contest->acl_id);
    }

    public static function getProblemAdmins(Problems $problem) {
        return self::getAdmins($problem->acl_id);
    }

    public static function isSystemAdmin($user_id) {
        return self::isAdmin($user_id, Authorization::SYSTEM_ACL);
    }
}
