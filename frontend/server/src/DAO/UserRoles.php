<?php

namespace OmegaUp\DAO;

/**
 * UserRoles Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\UserRoles}.
 * @access public
 * @package docs
 */
class UserRoles extends \OmegaUp\DAO\Base\UserRoles {
    /**
     * Gets the username of the owner of an ACL
     *
     * @return null|string
     */
    public static function getOwner(int $aclId): ?string {
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

        /** @var string|null */
        return \OmegaUp\MySQLConnection::getInstance()->GetOne(
            $sql,
            [ $aclId ]
        );
    }

    /**
     * @return list<array{user_id: int|null, role: 'admin'|'owner'|'site-admin', username: string}>
     */
    private static function getAdmins(int $aclId): array {
        $sql = '
            SELECT
                i.user_id, i.username, ur.acl_id AS acl
            FROM
                User_Roles ur
            INNER JOIN
                Identities i ON i.user_id = ur.user_id
            WHERE
                ur.role_id = ? AND ur.acl_id IN (?, ?);';
        $params = [
            \OmegaUp\Authorization::ADMIN_ROLE,
            \OmegaUp\Authorization::SYSTEM_ACL,
            $aclId,
        ];

        /** @var list<array{acl: int, user_id: int|null, username: string}> */
        $rawAdmins = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            $params
        );

        $sql = '
            SELECT
                i.user_id, i.username
            FROM
                ACLs a
            INNER JOIN
                Users u ON u.user_id = a.owner_id
            INNER JOIN
                Identities i ON u.main_identity_id = i.identity_id
            WHERE
                a.acl_id = ?;';
        $params = [$aclId];
        /** @var array{user_id: int|null, username: string} */
        $owner = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);

        $found = false;
        $admins = [];
        foreach ($rawAdmins as &$admin) {
            if ($admin['acl'] === \OmegaUp\Authorization::SYSTEM_ACL) {
                $admins[] = [
                    'user_id' => $admin['user_id'],
                    'username' => $admin['username'],
                    'role' => 'site-admin',
                ];
            } elseif ($admin['username'] === $owner['username']) {
                $found = true;
                $admins[] = [
                    'user_id' => $admin['user_id'],
                    'username' => $admin['username'],
                    'role' => 'owner',
                ];
            } else {
                $admins[] = [
                    'user_id' => $admin['user_id'],
                    'username' => $admin['username'],
                    'role' => 'admin',
                ];
            }
        }

        if (!$found) {
            $admins[] = [
                'user_id' => $owner['user_id'],
                'username' => $owner['username'],
                'role' => 'owner',
            ];
        }

        return $admins;
    }

    public static function hasRole(
        int $identityId,
        int $aclId,
        int $roleId
    ): bool {
        $sql = '
            SELECT
                COUNT(*)
            FROM
                User_Roles ur
            INNER JOIN
                Identities i ON ur.user_id = i.user_id
            WHERE
                i.identity_id = ? AND ur.role_id = ? AND ur.acl_id IN (?, ?);';
        $params = [
            $identityId,
            $roleId,
            \OmegaUp\Authorization::SYSTEM_ACL,
            $aclId,
        ];
        return (
            /** @var int */
            \OmegaUp\MySQLConnection::getInstance()->GetOne(
                $sql,
                $params
            )
        ) > 0;
    }

    /**
     * @return list<array{role: 'teaching_assistant', user_id: int|null, username: string}>
     */
    public static function getCourseTeachingAssistants(
        \OmegaUp\DAO\VO\Courses $course
    ): array {
        $sql = '
            SELECT
                i.username, ur.acl_id AS acl, i.user_id
            FROM
                User_Roles ur
            INNER JOIN
                Identities i ON i.user_id = ur.user_id
            WHERE
                ur.role_id = ? AND ur.acl_id IN (?);';
        $params = [
            \OmegaUp\Authorization::TEACHING_ASSISTANT_ROLE,
            $course->acl_id,
        ];

        /** @var list<array{acl: int, user_id: int|null, username: string}> */
        $rawTeachingAssistants = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            $params
        );

        $teachingAssistants = [];
        foreach ($rawTeachingAssistants as $teachingAssistant) {
                $teachingAssistants[] = [
                    'username' => $teachingAssistant['username'],
                    'role' => 'teaching_assistant',
                    'user_id' => $teachingAssistant['user_id'],
                ];
        }

        return $teachingAssistants;
    }

    /**
     * @return list<array{acl: int, user_id: int|null, username: string}>
     */
    public static function getCourseAdministrators(
        \OmegaUp\DAO\VO\Courses $course
    ): array {
        $sql = '
            (SELECT DISTINCT
                i.user_id,
                i.username,
                ur.acl_id AS acl
                FROM
                    User_Roles ur
                INNER JOIN
                    Identities i ON i.user_id = ur.user_id
                WHERE
                    ur.role_id IN (?,?) AND ur.acl_id IN (?))
            UNION DISTINCT
            (SELECT DISTINCT
                i.user_id,
                i.username,
                gr.acl_id as acl
                FROM
                    `Group_Roles` gr
                INNER JOIN
                    `Groups_` g ON gr.group_id = g.group_id
                INNER JOIN
                    `Groups_Identities` gi ON g.group_id = gi.group_id
                INNER JOIN
                    `Identities` i ON gi.identity_id = i.identity_id
                INNER JOIN
                    `User_Roles` ur ON gr.acl_id = ur.acl_id
                INNER JOIN
                `Users` u ON u.user_id = i.user_id
                WHERE
                    gr.role_id IN (?,?)
                    AND gr.acl_id IN (?))
            UNION DISTINCT
            (SELECT DISTINCT
                i.user_id,
                i.username,
                a.acl_id as acl
            FROM
                `ACLs` a
            INNER JOIN
                `Users` u ON u.user_id = a.owner_id
            INNER JOIN
                `Identities` i ON u.user_id = i.user_id
            WHERE
                a.acl_id IN (?));';
        $params = [
            \OmegaUp\Authorization::TEACHING_ASSISTANT_ROLE,
            \OmegaUp\Authorization::ADMIN_ROLE,
            $course->acl_id,
            \OmegaUp\Authorization::ADMIN_ROLE,
            \OmegaUp\Authorization::TEACHING_ASSISTANT_ROLE,
            $course->acl_id,
            $course->acl_id,
        ];

        /** @var list<array{acl: int, user_id: int|null, username: string}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            $params
        );
    }

    /**
     * @return list<array{user_id: int|null, role: 'admin'|'owner'|'site-admin', username: string}>
     */
    public static function getContestAdmins(\OmegaUp\DAO\VO\Contests $contest): array {
        return self::getAdmins(intval($contest->acl_id));
    }

    /**
     * @return list<array{user_id: int|null, role: 'admin'|'owner'|'site-admin', username: string}>
     */
    public static function getCourseAdmins(
        \OmegaUp\DAO\VO\Courses $course
    ): array {
        return self::getAdmins(intval($course->acl_id));
    }

    /**
     * @return list<array{user_id: int|null, role: 'admin'|'owner'|'site-admin', username: string}>
     */
    public static function getProblemAdmins(\OmegaUp\DAO\VO\Problems $problem): array {
        return self::getAdmins(intval($problem->acl_id));
    }

    /**
     * @return list<string>
     */
    public static function getSystemRoles(int $userId): array {
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
            $userId,
            \OmegaUp\Authorization::SYSTEM_ACL,
        ];

        $roles = [];
        /** @var array{name: string} $row */
        foreach (
            \OmegaUp\MySQLConnection::getInstance()->GetAll(
                $sql,
                $params
            ) as $row
        ) {
            $roles[] = $row['name'];
        }
        return $roles;
    }

    /**
     * @return list<string>
     */
    public static function getSystemGroups(int $identityId): array {
        $sql = "
            SELECT
                g.name
            FROM
                Groups_Identities gi
            INNER JOIN
                `Groups_` AS g ON gi.group_id = g.group_id
            WHERE
                gi.identity_id = ? AND g.name LIKE '%omegaup:%';";
        $params = [
            $identityId
        ];

        $groups = [];
        /** @var array{name: string} $group */
        foreach (
            \OmegaUp\MySQLConnection::getInstance()->GetAll(
                $sql,
                $params
            ) as $group
        ) {
            $groups[] = $group['name'];
        }

        return $groups;
    }

    /**
     * @param list<int> $aclIds
     * @return list<array{acl_id: int, user_id: int, role_id: int, username: string}>
     */
    public static function getByAclIds(array $aclIds): array {
        if (empty($aclIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($aclIds), '?'));
        $sql = "
            SELECT
                ur.acl_id,
                ur.user_id,
                ur.role_id,
                i.username
            FROM
                User_Roles ur
            INNER JOIN
                Identities i ON ur.user_id = i.user_id
            WHERE
                ur.acl_id IN ($placeholders)
        ";

        /** @var list<array{acl_id: int, user_id: int, role_id: int, username: string}> */
        $rows = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $aclIds);

        $result = [];
        foreach ($rows as $row) {
            $result[] = [
                'acl_id' => $row['acl_id'],
                'user_id' => $row['user_id'],
                'role_id' => $row['role_id'],
                'username' => $row['username'],
            ];
        }

        return $result;
    }
}
