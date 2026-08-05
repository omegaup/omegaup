<?php

namespace OmegaUp\DAO;

/**
 * Groups Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\Groups}.
 * @access public
 * @package docs
 */
class Groups extends \OmegaUp\DAO\Base\Groups {
    public static function findByAlias(string $alias): ?\OmegaUp\DAO\VO\Groups {
        $fields = \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\Groups::FIELD_NAMES,
            'g'
        );
        $sql = "SELECT {$fields} FROM `Groups_` `g` WHERE `g`.`alias` = ? LIMIT 1;";
        $params = [$alias];
        /** @var array{acl_id: int, alias: string, create_time: \OmegaUp\Timestamp, description: null|string, group_id: int, name: string}|null */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($rs)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\Groups($rs);
    }

    /**
     * @return list<\OmegaUp\DAO\VO\Groups>
     */
    public static function searchByName(string $name) {
        $fields = \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\Groups::FIELD_NAMES,
            'g'
        );
        $sql = "SELECT {$fields} FROM `Groups_` `g` WHERE `g`.`name` LIKE CONCAT('%', ?, '%') LIMIT 100;";

        /** @var list<array{acl_id: int, alias: string, create_time: \OmegaUp\Timestamp, description: null|string, group_id: int, name: string}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, [$name]);
        $groups = [];
        foreach ($rs as $row) {
            $groups[] = new \OmegaUp\DAO\VO\Groups($row);
        }
        return $groups;
    }

    /**
     * @return list<array{label: string, value: string}>
     */
    public static function searchByNameOrAlias(string $name) {
        $sql = "SELECT
                    `g`.`name` AS `label`,
                    `g`.`alias` AS `value`
                FROM
                    `Groups_` `g`
                WHERE
                    `g`.`name` LIKE CONCAT('%', ?, '%')
                    OR `g`.`alias` LIKE CONCAT('%', ?, '%')
                LIMIT
                    100;";

        $params = [$name, $name];
        /** @var list<array{label: string, value: string}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $params);
    }

    public static function getByName(string $name): ?\OmegaUp\DAO\VO\Groups {
        $sql = 'SELECT ' .  \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\Groups::FIELD_NAMES,
            'g'
        ) . ' FROM `Groups_` AS `g` WHERE `g`.`name` = ? LIMIT 1;';

        /** @var array{acl_id: int, alias: string, create_time: \OmegaUp\Timestamp, description: null|string, group_id: int, name: string}|null */
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
     * @return list<array{alias: string, create_time: \OmegaUp\Timestamp, description: null|string, name: string}>
     */
    final public static function getAllGroupsAdminedByUser(
        int $userId,
        int $identityId
    ): array {
        // group_id is only necessary to make ORDER BY work, because
        // ONLY_FULL_GROUP_BY mode is enabled.
        $sql = '
            SELECT
                admined_groups.alias,
                admined_groups.create_time,
                admined_groups.description,
                admined_groups.name,
                admined_groups.group_id
            FROM (
                SELECT
                    g.alias,
                    g.create_time,
                    g.description,
                    g.name,
                    g.group_id
                FROM
                    ACLs AS a
                INNER JOIN
                    `Groups_` AS g ON g.acl_id = a.acl_id
                WHERE
                    a.owner_id = ?

                UNION DISTINCT

                SELECT
                    g.alias,
                    g.create_time,
                    g.description,
                    g.name,
                    g.group_id
                FROM
                    User_Roles AS ur
                INNER JOIN
                    `Groups_` AS g ON g.acl_id = ur.acl_id
                WHERE
                    ur.user_id = ?
                    AND ur.role_id = ?

                UNION DISTINCT

                SELECT
                    g.alias,
                    g.create_time,
                    g.description,
                    g.name,
                    g.group_id
                FROM
                    Groups_Identities AS gi
                INNER JOIN
                    Group_Roles AS gr ON gr.group_id = gi.group_id
                INNER JOIN
                    `Groups_` AS g ON g.acl_id = gr.acl_id
                WHERE
                    gi.identity_id = ?
                    AND gr.role_id = ?
            ) AS admined_groups
            ORDER BY
                admined_groups.group_id DESC;';

        /** @var list<array{alias: string, create_time: \OmegaUp\Timestamp, description: null|string, group_id: int, name: string}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, [
            $userId,
            $userId,
            \OmegaUp\Authorization::ADMIN_ROLE,
            $identityId,
            \OmegaUp\Authorization::ADMIN_ROLE,
        ]);
        foreach ($rs as &$row) {
            unset($row['group_id']);
        }
        return $rs;
    }

    /**
     * Gets a random sample (of up to size $n) of group members.
     *
     * @return list<\OmegaUp\DAO\VO\Identities> $identities
     */
    final public static function sampleMembers(
        \OmegaUp\DAO\VO\Groups $group,
        int $membersCount
    ): array {
        $fields = \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\Identities::FIELD_NAMES,
            'i'
        );
        $sql = "SELECT
                    {$fields}
                FROM
                    Groups_Identities gi
                INNER JOIN
                    Identities i ON i.identity_id = gi.identity_id
                WHERE
                    gi.group_id = ?
                ORDER BY
                    RAND()
                LIMIT
                    0, ?;";

        /** @var list<\OmegaUp\DAO\VO\Identities> */
        $identities = [];
        /** @var array{country_id: null|string, current_identity_school_id: int|null, gender: null|string, identity_id: int, language_id: int|null, name: null|string, password: null|string, state_id: null|string, user_id: int|null, username: string} $row */
        foreach (
            \OmegaUp\MySQLConnection::getInstance()->GetAll(
                $sql,
                [$group->group_id, $membersCount]
            ) as $row
        ) {
            $identities[] = new \OmegaUp\DAO\VO\Identities($row);
        }
        return $identities;
    }
}
