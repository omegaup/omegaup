<?php

namespace OmegaUp\DAO;

/**
 * ACLs Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
 * almacenar de forma permanente y recuperar instancias de objetos {@link \OmegaUp\DAO\VO\ACLs}.
 * @access public
 */
class ACLs extends \OmegaUp\DAO\Base\ACLs {
        /**
     * Get all ACLs owned by a user along with their type, alias, and an empty users array.
     *
     * @param int $userId The user ID whose ACLs are being fetched.
     * @return list<array{acl_id: int, type: string, alias: string, users: list<array{user_id: int, username: string, role_id: int, role_name: string, role_description: string}>}>
     */
    public static function getUserOwnedAclTypesWithAliases(int $userId): array {
        $sql = '
            SELECT a.acl_id, c.alias, "contest" AS type
            FROM ACLs a
            INNER JOIN Contests c ON c.acl_id = a.acl_id
            WHERE a.owner_id = ?
            UNION
            SELECT a.acl_id, c.alias, "course" AS type
            FROM ACLs a
            INNER JOIN Courses c ON c.acl_id = a.acl_id
            WHERE a.owner_id = ?
            UNION
            SELECT a.acl_id, p.alias, "problem" AS type
            FROM ACLs a
            INNER JOIN Problems p ON p.acl_id = a.acl_id
            WHERE a.owner_id = ?
            UNION
            SELECT a.acl_id, g.alias, "group" AS type
            FROM ACLs a
            INNER JOIN Groups_ g ON g.acl_id = a.acl_id
            WHERE a.owner_id = ?
        ';
        $params = array_fill(0, 4, $userId);

        /** @var list<array{acl_id: int, alias: string, type: string}> */
        $rows = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $params);

        return array_map(fn($row) => [
            'acl_id' => $row['acl_id'],
            'type' => $row['type'],
            'alias' => $row['alias'],
            'users' => [],
        ], $rows);
    }
}
