<?php

namespace OmegaUp\DAO;

/**
 * UserRoles Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\UserRoles}.
 *
 * @author alanboy
 * @access public
 * @package docs
 */
class UserRoles extends \OmegaUp\DAO\Base\UserRoles {
    private static function getAdmins($acl_id) {
        $sql = '
            SELECT
                i.username, ur.acl_id AS acl
            FROM
                User_Roles ur
            INNER JOIN
                Identities i ON i.user_id = ur.user_id
            WHERE
                ur.role_id = ? AND ur.acl_id IN (?, ?);';
        $params = [
            \OmegaUp\Authorization::ADMIN_ROLE,
            \OmegaUp\Authorization::SYSTEM_ACL,
            $acl_id,
        ];

        $admins = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $params);

        $sql = '
            SELECT
                i.username
            FROM
                ACLs a
            INNER JOIN
                Users u ON u.user_id = a.owner_id
            INNER JOIN
                Identities i ON u.main_identity_id = i.identity_id
            WHERE
                a.acl_id = ?;';
        $params = [$acl_id];
        $owner = \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, $params);

        $found = false;
        for ($i = 0; $i < count($admins); $i++) {
            if ($admins[$i]['acl'] == \OmegaUp\Authorization::SYSTEM_ACL) {
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

    public static function hasRole($identity_id, $acl_id, $role_id) {
        $sql = '
            SELECT
                COUNT(*)
            FROM
                User_Roles ur
            INNER JOIN
                Users u ON u.user_id = ur.user_id
            INNER JOIN
                Identities i ON u.user_id = i.user_id
            WHERE
                i.identity_id = ? AND ur.role_id = ? AND ur.acl_id IN (?, ?);';
        $params = [
            $identity_id,
            $role_id,
            \OmegaUp\Authorization::SYSTEM_ACL,
            $acl_id,
        ];
        return \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, $params) > 0;
    }

    public static function getContestAdmins(\OmegaUp\DAO\VO\Contests $contest) {
        return self::getAdmins($contest->acl_id);
    }

    public static function getCourseAdmins(\OmegaUp\DAO\VO\Courses $course) {
        return self::getAdmins($course->acl_id);
    }

    public static function getProblemAdmins(\OmegaUp\DAO\VO\Problems $problem) {
        return self::getAdmins($problem->acl_id);
    }

    public static function getSystemRoles($user_id) {
        $sql = '
            SELECT
                r.name
            FROM
                User_Roles ur
            INNER JOIN
                Roles r ON r.role_id = ur.role_id
            WHERE
                ur.user_id = ? AND ur.acl_id = ?;';
        $params = [
            $user_id,
            \OmegaUp\Authorization::SYSTEM_ACL,
        ];

        $roles = [];
        foreach (\OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $params) as $role) {
            $roles[] = $role['name'];
        }
        return $roles;
    }

    public static function getSystemGroups($identity_id) {
        $sql = "
            SELECT
                g.name
            FROM
                Groups_Identities gi
            INNER JOIN
                Groups g ON gi.group_id = g.group_id
            WHERE
                gi.identity_id = ? AND g.name LIKE '%omegaup:%';";
        $params = [
            $identity_id
        ];

        $groups = [];
        foreach (\OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $params) as $group) {
            $groups[] = $group['name'];
        }

        return $groups;
    }
}
