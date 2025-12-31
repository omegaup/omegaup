<?php

namespace OmegaUp\DAO;

/**
 * Groups Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\TeamGroups}.
 *
 * @access public
 * @package docs
 */
class TeamGroups extends \OmegaUp\DAO\Base\TeamGroups {
    public static function getByAlias(string $alias): ?\OmegaUp\DAO\VO\TeamGroups {
        $sql = 'SELECT
                    ' .  \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\TeamGroups::FIELD_NAMES,
            'tg'
        ) . '
                FROM
                    `Team_Groups` `tg`
                WHERE
                    `tg`.`alias` = ?
                LIMIT 1;';
        $params = [$alias];
        /** @var array{acl_id: int, alias: string, create_time: \OmegaUp\Timestamp, description: null|string, name: string, number_of_contestants: int, team_group_id: int}|null */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($rs)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\TeamGroups($rs);
    }

    public static function getByName(string $name): ?\OmegaUp\DAO\VO\TeamGroups {
        $sql = 'SELECT
                    ' .  \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\TeamGroups::FIELD_NAMES,
            'tg'
        ) . '
                FROM
                    `Team_Groups` `tg`
                WHERE
                    `tg`.`name` = ?
                LIMIT 1;';
        /** @var array{acl_id: int, alias: string, create_time: \OmegaUp\Timestamp, description: null|string, name: string, number_of_contestants: int, team_group_id: int}|null */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, [$name]);
        if (empty($rs)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\TeamGroups($rs);
    }

    /**
     * Returns all teams groups that a user can manage.
     * @param int $userId
     * @return list<array{alias: string, create_time: \OmegaUp\Timestamp, description: null|string, name: string}>
     */
    final public static function getAllTeamsGroupsAdminedByUser(int $userId) {
        $sql = 'SELECT
                    tg.alias,
                    tg.create_time,
                    tg.description,
                    tg.name
                FROM
                    `Team_Groups` AS tg
                INNER JOIN
                    ACLs AS a ON a.acl_id = tg.acl_id
                WHERE
                    a.owner_id = ?
                ORDER BY
                    tg.create_time DESC;';

        /** @var list<array{alias: string, create_time: \OmegaUp\Timestamp, description: null|string, name: string}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, [$userId]);
    }

    /**
     * @return list<array{key: string, value: string}>
     */
    public static function findByNameOrAlias(string $aliasOrName) {
        $sql = "SELECT DISTINCT
                    tg.alias AS `key`,
                    tg.name AS `value`
                FROM
                    Team_Groups tg
                WHERE
                    tg.alias LIKE CONCAT('%', ?, '%') OR
                    tg.name LIKE CONCAT('%', ?, '%')
                LIMIT 100;";

        /** @var list<array{key: string, value: string}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$aliasOrName, $aliasOrName]
        );
    }

    /**
     * @return array{alias: string, team_id: int}|null
     */
    public static function getByTeamUsername(string $teamUsername) {
        $sql = 'SELECT
                    `tg`.`alias`,
                    `t`.`team_id`
                FROM
                    `Team_Groups` `tg`
                INNER JOIN
                    `Teams` `t`
                ON
                    `t`.`team_group_id` = `tg`.`team_group_id`
                INNER JOIN
                    `Identities` `i`
                ON
                    `t`.`identity_id` = `i`.`identity_id`
                WHERE
                    `i`.`username` = ?
                LIMIT 1;';
        /** @var array{alias: string, team_id: int}|null */
        return \OmegaUp\MySQLConnection::getInstance()->GetRow(
            $sql,
            [$teamUsername]
        );
    }
}
