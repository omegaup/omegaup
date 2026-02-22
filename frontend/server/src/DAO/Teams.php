<?php

namespace OmegaUp\DAO;

/**
 * Teams Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\Teams}.
 *
 * @access public
 * @package docs
 *
 * @psalm-type Identity=array{classname?: string, country: null|string, country_id: null|string, gender: null|string, name: null|string, password?: string, school: null|string, school_id: int|null, school_name?: string, state: null|string, state_id: null|string, username: string}
 */
class Teams extends \OmegaUp\DAO\Base\Teams {
    public static function getByTeamGroupIdAndIdentityId(
        int $teamGroupId,
        int $identityId
    ): ?\OmegaUp\DAO\VO\Teams {
        $sql = 'SELECT
                    `t`.`team_id`,
                    `t`.`team_group_id`,
                    `t`.`identity_id`
                FROM
                    `Teams` `t`
                WHERE
                    `t`.`team_group_id` = ?
                    AND `t`.`identity_id` = ?
                LIMIT 1;';
        $params = [$teamGroupId, $identityId];
        /** @var array{identity_id: int, team_group_id: int, team_id: int}|null */
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\Teams($row);
    }

    public static function getByIdentityId(int $identityId): ?\OmegaUp\DAO\VO\Teams {
        $sql = 'SELECT
                    `team_id`,
                    `team_group_id`,
                    `identity_id`
                FROM
                    `Teams`
                WHERE
                    `identity_id` = ?
                LIMIT 1;';
        /** @var array{identity_id: int, team_group_id: int, team_id: int}|null */
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow(
            $sql,
            [$identityId]
        );
        if ($row === null) {
            return null;
        }
        return new \OmegaUp\DAO\VO\Teams($row);
    }

    /**
     * @return list<Identity>
     */
    public static function getTeamGroupIdentities(
        \OmegaUp\DAO\VO\TeamGroups $teamGroup
    ) {
        $sql = 'SELECT
                    i.username,
                    i.name,
                    i.gender,
                    c.name AS country,
                    c.country_id,
                    s.name AS state,
                    s.state_id,
                    sc.name AS school,
                    sc.school_id AS school_id,
                    IFNULL(ur.classname, "user-rank-unranked") AS classname
                FROM
                    Teams t
                INNER JOIN
                    Team_Groups tg ON tg.team_group_id = t.team_group_id
                INNER JOIN
                    Identities i ON i.identity_id = t.identity_id
                LEFT JOIN
                    User_Rank ur ON ur.user_id = i.user_id
                LEFT JOIN
                    States s ON s.state_id = i.state_id AND s.country_id = i.country_id
                LEFT JOIN
                    Countries c ON c.country_id = s.country_id
                LEFT JOIN
                    Identities_Schools isc ON isc.identity_school_id = i.current_identity_school_id
                LEFT JOIN
                    Schools sc ON sc.school_id = isc.school_id
                WHERE
                    t.team_group_id = ?;';

        /** @var list<array{classname: string, country: null|string, country_id: null|string, gender: null|string, name: null|string, school: null|string, school_id: int|null, state: null|string, state_id: null|string, username: string}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$teamGroup->team_group_id]
        );
    }
}
