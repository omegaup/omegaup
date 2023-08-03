<?php

namespace OmegaUp\DAO;

/**
 * GroupRoles Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\GroupRoles}.
 *
 * @access public
 */
class GroupRoles extends \OmegaUp\DAO\Base\GroupRoles {
    /**
     * @return list<array{alias: string, name: string, role: string}>
     */
    public static function getAdmins(int $aclId): array {
        $sql = '
            SELECT
                g.alias, g.name, gr.acl_id AS acl
            FROM
                Group_Roles gr
            INNER JOIN
                `Groups_` AS g ON g.group_id = gr.group_id
            WHERE
                gr.role_id = ? AND gr.acl_id IN (?, ?);';
        $params = [
            \OmegaUp\Authorization::ADMIN_ROLE,
            \OmegaUp\Authorization::SYSTEM_ACL,
            $aclId,
        ];

        /** @var list<array{acl: int, alias: string, name: string}> */
        $rawAdmins = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            $params
        );

        $admins = [];
        foreach ($rawAdmins as &$admin) {
            if ($admin['acl'] == \OmegaUp\Authorization::SYSTEM_ACL) {
                $admins[] = [
                    'alias' => $admin['alias'],
                    'name' => $admin['name'],
                    'role' => 'site-admin',
                ];
            } else {
                $admins[] = [
                    'alias' => $admin['alias'],
                    'name' => $admin['name'],
                    'role' => 'admin',
                ];
            }
        }
        return $admins;
    }

    /**
     * @return list<array{alias: string, name: string}>
     */
    public static function getContestantGroups(int $problemsetId): array {
        $sql = '
            SELECT
                g.alias, g.name
            FROM
                Problemsets p
            INNER JOIN
                Group_Roles gr ON gr.acl_id = p.acl_id
            INNER JOIN
                `Groups_` AS g ON g.group_id = gr.group_id
            WHERE
                p.problemset_id = ? AND
                gr.role_id = ?;
        ';
        $params = [
            $problemsetId,
            \OmegaUp\Authorization::CONTESTANT_ROLE,
        ];

        /** @var list<array{alias: string, name: string}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            $params
        );
    }

    public static function hasRole(
        int $identityId,
        int $aclId,
        int $roleId
    ): bool {
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
            $identityId,
            $roleId,
            \OmegaUp\Authorization::SYSTEM_ACL,
            $aclId,
        ];
        return boolval(
            /** @var int|null */
            \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, $params)
        );
    }

    /**
     * @return list<array{alias: string, name: string, role: string}>
     */
    public static function getCourseTeachingAssistants(\OmegaUp\DAO\VO\Courses $course): array {
        $sql = "
            SELECT
                g.alias, g.name, 'teaching_assistant' AS role
            FROM
                Group_Roles gr
            INNER JOIN
                `Groups_` AS g ON g.group_id = gr.group_id
            WHERE
                gr.role_id = ? AND gr.acl_id IN (?);";
        $params = [
            \OmegaUp\Authorization::TEACHING_ASSISTANT_ROLE,
            $course->acl_id,
        ];

        /** @var list<array{alias: string, name: string, role: string}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            $params
        );
    }

    public static function isContestant(int $identityId, int $aclId): bool {
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
            $identityId,
            \OmegaUp\Authorization::CONTESTANT_ROLE,
            $aclId,
        ];
        return boolval(
            /** @var int|null */
            \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, $params)
        );
    }

    /**
     * @return list<array{alias: string, name: string, role: string}>
     */
    public static function getContestAdmins(\OmegaUp\DAO\VO\Contests $contest): array {
        return self::getAdmins(intval($contest->acl_id));
    }

    /**
     * @return list<array{alias: string, name: string, role: string}>
     */
    public static function getCourseAdmins(\OmegaUp\DAO\VO\Courses $course): array {
        return self::getAdmins(intval($course->acl_id));
    }

    /**
     * @return list<array{alias: string, name: string, role: string}>
     */
    public static function getProblemAdmins(\OmegaUp\DAO\VO\Problems $problem): array {
        return self::getAdmins(intval($problem->acl_id));
    }

    /**
     * @return list<string>
     */
    public static function getSystemRoles(int $identityId): array {
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
            $identityId,
            \OmegaUp\Authorization::SYSTEM_ACL,
        ];

        $roles = [];
        /** @var array{name: string} $role */
        foreach (
            \OmegaUp\MySQLConnection::getInstance()->GetAll(
                $sql,
                $params
            ) as $role
        ) {
            $roles[] = $role['name'];
        }
        return $roles;
    }
}
