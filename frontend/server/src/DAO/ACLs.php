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
     * Returns the type and alias of each ACL in a single query.
     *
     * @return array<int, array{type: string, alias: string}>
     */
    public static function getAclTypesWithAliases(): array {
        $sql = '
            SELECT acl_id, alias, "contest" AS type FROM Contests
            UNION
            SELECT acl_id, alias, "course" AS type FROM Courses
            UNION
            SELECT acl_id, alias, "problem" AS type FROM Problems
            UNION
            SELECT acl_id, alias, "group" AS type FROM Groups_';

        $rows = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql);

        $aclTypes = [];
        foreach ($rows as $row) {
            if (
                isset($row['alias'], $row['type'], $row['acl_id']) &&
                is_string($row['alias']) &&
                is_string($row['type'])
            ) {
                $aclTypes[intval($row['acl_id'])] = [
                    'type' => $row['type'],
                    'alias' => $row['alias'],
                ];
            }
        }

        return $aclTypes;
    }
}
