<?php

namespace OmegaUp\DAO;

/**
 * GroupsIdentities Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\GroupsIdentities}.
 *
 * @access public
 */
class GroupsIdentities extends \OmegaUp\DAO\Base\GroupsIdentities {
    public static function GetMemberIdentities(\OmegaUp\DAO\VO\Groups $group) {
        $sql = '
            SELECT
                i.username,
                i.name,
                c.name as country,
                c.country_id,
                s.name as state,
                s.state_id,
                sc.name as school,
                sc.school_id as school_id,
                IFNULL(
                    (
                        SELECT `urc`.classname FROM
                            `User_Rank_Cutoffs` urc
                        WHERE
                            `urc`.score <= (
                                    SELECT
                                        `ur`.`score`
                                    FROM
                                        `User_Rank` `ur`
                                    WHERE
                                        `ur`.user_id = `i`.`user_id`
                                )
                        ORDER BY
                            `urc`.percentile ASC
                        LIMIT
                            1
                    ),
                    "user-rank-unranked"
                ) `classname`
            FROM
                Groups_Identities gi
            INNER JOIN
                Identities i ON i.identity_id = gi.identity_id
            LEFT JOIN
                States s ON s.state_id = i.state_id AND s.country_id = i.country_id
            LEFT JOIN
                Countries c ON c.country_id = s.country_id
            LEFT JOIN
                Identities_Schools isc ON isc.identity_school_id = i.current_identity_school_id
            LEFT JOIN
                Schools sc ON sc.school_id = isc.school_id
            LEFT JOIN
                Users u ON u.user_id = i.user_id
            WHERE
                gi.group_id = ?;';

        /** @var list<array{classname: string, country: null|string, country_id: null|string, name: null|string, school: null|string, school_id: int|null, state: null|string, state_id: null|string, username: string}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$group->group_id]
        );
        $identities = [];
        foreach ($rs as $row) {
            if (strpos($row['username'], ':') === false) {
                array_push($identities, [
                    'username' => $row['username'],
                    'classname' => $row['classname'],
                ]);
                continue;
            }
            array_push($identities, $row);
        }
        return $identities;
    }

    public static function GetMemberCountById(int $groupId): int {
        $sql = '
            SELECT
                COUNT(*) AS count
            FROM
                Groups_Identities gi
            WHERE
                gi.group_id = ?;';
        /** @var null|int */
        $result = \OmegaUp\MySQLConnection::getInstance()->GetOne(
            $sql,
            [$groupId]
        );
        if (empty($result)) {
            return 0;
        }
        return intval($result);
    }

    /**
     * @return \OmegaUp\DAO\VO\GroupsIdentities[]
     */
    final public static function getByGroupId(int $groupId): array {
        $sql = '
            SELECT
                *
            FROM
                Groups_Identities i
            WHERE
                group_id = ?;';

        $groupsIdentities = [];
        /** @var array{accept_teacher: bool|null, group_id: int, identity_id: int, privacystatement_consent_id: int|null, share_user_information: bool|null} $row */
        foreach (
            \OmegaUp\MySQLConnection::getInstance()->GetAll(
                $sql,
                [$groupId]
            ) as $row
        ) {
            array_push(
                $groupsIdentities,
                new \OmegaUp\DAO\VO\GroupsIdentities(
                    $row
                )
            );
        }
        return $groupsIdentities;
    }

    final public static function getUsernamesByGroupId($group_id) {
        $sql = '
            SELECT
                i.username
            FROM
                Identities i
            INNER JOIN
                Groups_Identities gi
            ON
                i.identity_id = gi.identity_id
            WHERE
                gi.group_id = ?;';

        $identities = [];
        /** @var array{username: string} $row */
        foreach (
            \OmegaUp\MySQLConnection::getInstance()->GetAll(
                $sql,
                [$group_id]
            ) as $row
        ) {
            array_push($identities, $row['username']);
        }
        return $identities;
    }
}
