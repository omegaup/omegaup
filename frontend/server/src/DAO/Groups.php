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
    public static function findByAlias(string $alias): ?\OmegaUp\DAO\VO\Groups {
        $sql = 'SELECT `g`.* FROM `Groups` AS `g` WHERE `g`.`alias` = ? LIMIT 1;';
        $params = [$alias];
        /** @var array{acl_id: int, alias: string, create_time: string, description: null|string, group_id: int, name: string}|null */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($rs)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\Groups($rs);
    }

    /**
     * @return \OmegaUp\DAO\VO\Groups[]
     */
    public static function SearchByName(string $name) {
        $sql = "SELECT `g`.* FROM `Groups` AS `g` WHERE `g`.`name` LIKE CONCAT('%', ?, '%') LIMIT 10;";
        $args = [$name];

        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $args);
        $ar = [];
        foreach ($rs as $row) {
            array_push($ar, new \OmegaUp\DAO\VO\Groups($row));
        }
        return $ar;
    }

    public static function getByName(string $name): ?\OmegaUp\DAO\VO\Groups {
        $sql = 'SELECT `g`.* FROM `Groups` AS `g` WHERE `g`.`name` = ? LIMIT 1;';

        /** @var array{acl_id: int, alias: string, create_time: string, description: null|string, group_id: int, name: string}|null */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, [$name]);
        if (empty($rs)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\Groups($rs);
    }

    /**
     * Returns all groups that a user can manage.
     * @param int $userId
     * @param int $identityId
     * @return list<array{alias: string, create_time: int, description: null|string, name: string}>
     */
    final public static function getAllGroupsAdminedByUser(
        int $userId,
        int $identityId
    ): array {
        // group_id is only necessary to make ORDER BY work, because
        // ONLY_FULL_GROUP_BY mode is enabled.
        $sql = '
            SELECT
                DISTINCT g.alias,
                UNIX_TIMESTAMP(g.create_time) AS create_time,
                g.description,
                g.name,
                g.group_id
            FROM
                `Groups` AS g
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

        /** @var list<array{alias: string, create_time: int, description: null|string, group_id: int, name: string}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, [
            $userId,
            \OmegaUp\Authorization::ADMIN_ROLE,
            $userId,
            \OmegaUp\Authorization::ADMIN_ROLE,
            $identityId,
        ]);
        foreach ($rs as &$row) {
            unset($row['group_id']);
        }
        return $rs;
    }

    /**
     * Gets a random sample (of up to size $n) of group members.
     *
     * @return \OmegaUp\DAO\VO\Identities[] $identities
     */
    final public static function sampleMembers(
        \OmegaUp\DAO\VO\Groups $group,
        int $n
    ): array {
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

        /** @var \OmegaUp\DAO\VO\Identities[] */
        $identities = [];
        /** @var array{country_id: null|string, current_identity_school_id: int|null, gender: null|string, identity_id: int, language_id: int|null, name: null|string, password: null|string, state_id: null|string, user_id: int|null, username: string} $row */
        foreach (
            \OmegaUp\MySQLConnection::getInstance()->GetAll(
                $sql,
                [$group->group_id, $n]
            ) as $row
        ) {
            $identities[] = new \OmegaUp\DAO\VO\Identities($row);
        }
        return $identities;
    }
}
