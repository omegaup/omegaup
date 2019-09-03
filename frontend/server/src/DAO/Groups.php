<?php

namespace OmegaUp\DAO;

/**
 * Groups Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\Groups}.
 *
 * @author alanboy
 * @access public
 * @package docs
 */
class Groups extends \OmegaUp\DAO\Base\Groups {
    public static function findByAlias(string $alias) : ?\OmegaUp\DAO\VO\Groups {
        $sql = 'SELECT g.* FROM Groups g WHERE g.alias = ? LIMIT 1;';
        $params = [$alias];
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($rs)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\Groups($rs);
    }

    public static function SearchByName($name) {
        $sql = "SELECT g.* from Groups g where g.name LIKE CONCAT('%', ?, '%') LIMIT 10;";
        $args = [$name];

        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $args);
        $ar = [];
        foreach ($rs as $row) {
            array_push($ar, new \OmegaUp\DAO\VO\Groups($row));
        }
        return $ar;
    }

    public static function getByName(string $name) : ?\OmegaUp\DAO\VO\Groups {
        $sql = 'SELECT g.* from Groups g where g.name = ? LIMIT 1;';

        $rs = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, [$name]);
        if (empty($rs)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\Groups($rs);
    }

    /**
     * Returns all groups that a user can manage.
     */
    final public static function getAllGroupsAdminedByUser($user_id, $identity_id) {
        $sql = '
            SELECT
                DISTINCT g.*
            FROM
                Groups g
            INNER JOIN
                ACLs AS a ON a.acl_id = g.acl_id
            LEFT JOIN
                User_Roles ur ON ur.acl_id = g.acl_id
            LEFT JOIN
                Group_Roles gr ON gr.acl_id = g.acl_id
            LEFT JOIN
                Groups_Identities gi ON gi.group_id = gr.group_id
            WHERE
                a.owner_id = ? OR
                (ur.role_id = ? AND ur.user_id = ?) OR
                (gr.role_id = ? AND gi.identity_id = ?)
            ORDER BY
                g.group_id DESC;';
        $params = [
            $user_id,
            \OmegaUp\Authorization::ADMIN_ROLE,
            $user_id,
            \OmegaUp\Authorization::ADMIN_ROLE,
            $identity_id,
        ];

        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $params);

        $groups = [];
        foreach ($rs as $row) {
            array_push($groups, new \OmegaUp\DAO\VO\Groups($row));
        }
        return $groups;
    }

    /**
     * Gets a random sample (of up to size $n) of group members.
     *
     * @return \OmegaUp\DAO\VO\Identities[] $identities
     */
    final public static function sampleMembers(\OmegaUp\DAO\VO\Groups $group, int $n): array {
        $sql = '
            SELECT
                i.*
            FROM
                Groups_Identities gi
            INNER JOIN
                Identities i ON i.identity_id = gi.identity_id
            WHERE
                gi.group_id = ?
            ORDER BY
                RAND()
            LIMIT
                0, ?;';

        $identities = [];
        foreach (\OmegaUp\MySQLConnection::getInstance()->GetAll($sql, [$group->group_id, (int)$n]) as $row) {
            $identities[] = new \OmegaUp\DAO\VO\Identities($row);
        }
        return $identities;
    }
}
