<?php

namespace OmegaUp\DAO;

/**
 * Roles Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\Roles}.
 * @access public
 * @package docs
 */
class Roles extends \OmegaUp\DAO\Base\Roles {
    final public static function getByName(string $name): \OmegaUp\DAO\VO\Roles {
        $sql = 'SELECT
                    ' .  \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\Roles::FIELD_NAMES,
            'Roles'
        ) . '
                FROM
                    Roles
                WHERE
                    name = ?';

        /** @var array{description: string, name: string, role_id: int}|null */
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, [$name]);
        if (empty($row)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterNotFound',
                'role'
            );
        }
        return new \OmegaUp\DAO\VO\Roles($row);
    }

    /**
     * @return list<array{role_id: int, name: string, description: string, users_count: int, groups_count: int, permissions_count: int}>
     */
    public static function getAllWithCounts(): array {
        $sql = '
            SELECT
                r.role_id,
                r.name,
                r.description,
                (SELECT COUNT(DISTINCT ur.user_id) FROM User_Roles ur WHERE ur.role_id = r.role_id) AS users_count,
                (SELECT COUNT(DISTINCT gr.group_id) FROM Group_Roles gr WHERE gr.role_id = r.role_id) AS groups_count,
                (SELECT COUNT(*) FROM Roles_Permissions rp WHERE rp.role_id = r.role_id) AS permissions_count
            FROM
                Roles r
            ORDER BY
                r.role_id;';

        /** @var list<array{role_id: int, name: string, description: string, users_count: int, groups_count: int, permissions_count: int}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll($sql);
    }

    /**
     * @return list<array{permission_id: int, name: string, description: string}>
     */
    public static function getPermissions(int $roleId): array {
        $sql = '
            SELECT
                p.permission_id,
                p.name,
                p.description
            FROM
                Roles_Permissions rp
            INNER JOIN
                Permissions p ON p.permission_id = rp.permission_id
            WHERE
                rp.role_id = ?
            ORDER BY
                p.permission_id;';

        /** @var list<array{permission_id: int, name: string, description: string}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, [$roleId]);
    }
}
