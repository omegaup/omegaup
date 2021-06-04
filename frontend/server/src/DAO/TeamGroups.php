<?php

namespace OmegaUp\DAO;

/**
 * Groups Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\TeamGroups}.
 *
 * @author juan.pablo
 * @access public
 * @package docs
 */
class TeamGroups extends \OmegaUp\DAO\Base\TeamGroups {
    public static function getByAlias(string $alias): ?\OmegaUp\DAO\VO\TeamGroups {
        $sql = 'SELECT
                    `tg`.*
                FROM
                    `Team_Groups` AS `tg`
                WHERE
                    `tg`.`alias` = ?
                LIMIT 1;';
        $params = [$alias];
        /** @var array{acl_id: int, alias: string, create_time: \OmegaUp\Timestamp, description: null|string, name: string, team_group_id: int}|null */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($rs)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\TeamGroups($rs);
    }

    public static function getByName(string $name): ?\OmegaUp\DAO\VO\TeamGroups {
        $sql = 'SELECT
                    `tg`.*
                FROM
                    `Team_Groups` AS `tg`
                WHERE
                    `tg`.`name` = ?
                LIMIT 1;';
        /** @var array{acl_id: int, alias: string, create_time: \OmegaUp\Timestamp, description: null|string, name: string, team_group_id: int}|null */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, [$name]);
        if (empty($rs)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\TeamGroups($rs);
    }

    /**
     * Returns all teams groups that a user can manage.
     * @param int $userId
     * @param int $identityId
     * @return list<array{alias: string, create_time: \OmegaUp\Timestamp, description: null|string, name: string}>
     */
    final public static function getAllTeamsGroupsAdminedByUser(int $userId) {
        $sql = 'SELECT
                    DISTINCT tg.alias,
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
}
