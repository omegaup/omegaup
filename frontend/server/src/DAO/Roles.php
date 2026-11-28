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

    private const SUMMARY_COLUMNS = '
                r.role_id,
                r.name,
                r.description,
                (SELECT COUNT(DISTINCT ur.user_id) FROM User_Roles ur WHERE ur.role_id = r.role_id) AS users_count,
                (SELECT COUNT(DISTINCT gr.group_id) FROM Group_Roles gr WHERE gr.role_id = r.role_id) AS groups_count,
                (SELECT COUNT(*) FROM Roles_Permissions rpc WHERE rpc.role_id = r.role_id) AS permissions_count';

    /**
     * @param array{description: null|string, groups_count: int|null, name: null|string, permissions_count: int|null, role_id: int|null, users_count: int|null} $row
     * @return array{role_id: int, name: string, description: string, users_count: int, groups_count: int, permissions_count: int}
     */
    private static function buildSummary(array $row): array {
        return [
            'role_id' => intval($row['role_id']),
            'name' => strval($row['name']),
            'description' => strval($row['description']),
            'users_count' => intval($row['users_count']),
            'groups_count' => intval($row['groups_count']),
            'permissions_count' => intval($row['permissions_count']),
        ];
    }

    /**
     * @return list<array{role_id: int, name: string, description: string, users_count: int, groups_count: int, permissions_count: int}>
     */
    public static function getAllWithCounts(): array {
        $sql = '
            SELECT' . self::SUMMARY_COLUMNS . '
            FROM
                Roles r
            ORDER BY
                r.role_id;';

        /** @var list<array{description: null|string, groups_count: int|null, name: null|string, permissions_count: int|null, role_id: int|null, users_count: int|null}> */
        $rows = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql);

        return array_map(
            fn (array $row): array => self::buildSummary($row),
            $rows
        );
    }

    /**
     * Gets a role with its counts and permissions in a single query. Roles
     * without permissions are returned with an empty list.
     *
     * @return null|array{role: array{role_id: int, name: string, description: string, users_count: int, groups_count: int, permissions_count: int}, permissions: list<array{permission_id: int, name: string, resource_type: string, description: string}>}
     */
    public static function getDetailsByName(string $name): ?array {
        $sql = '
            SELECT' . self::SUMMARY_COLUMNS . ',
                p.permission_id,
                p.name AS permission_name,
                p.resource_type,
                p.description AS permission_description
            FROM
                Roles r
            LEFT JOIN
                Roles_Permissions rp ON rp.role_id = r.role_id
            LEFT JOIN
                Permissions p ON p.permission_id = rp.permission_id
            WHERE
                r.name = ?
            ORDER BY
                p.permission_id;';

        /** @var list<array{description: null|string, groups_count: int|null, name: null|string, permission_description: null|string, permission_id: int|null, permission_name: null|string, permissions_count: int|null, resource_type: null|string, role_id: int|null, users_count: int|null}> */
        $rows = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, [$name]);
        if (empty($rows)) {
            return null;
        }

        $permissions = [];
        foreach ($rows as $row) {
            if (is_null($row['permission_id'])) {
                continue;
            }
            $permissions[] = [
                'permission_id' => intval($row['permission_id']),
                'name' => strval($row['permission_name']),
                'resource_type' => strval($row['resource_type']),
                'description' => strval($row['permission_description']),
            ];
        }

        return [
            'role' => self::buildSummary($rows[0]),
            'permissions' => $permissions,
        ];
    }
}
