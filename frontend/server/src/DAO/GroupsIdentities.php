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
    /**
     * @return list<array{classname: string, country: null|string, country_id: null|string, gender: null|string, name: null|string, school: null|string, school_id: int|null, state: null|string, state_id: null|string, username: string}>
     */
    public static function getMemberIdentities(\OmegaUp\DAO\VO\Groups $group) {
        $sql = '
            SELECT
                i.username,
                i.name,
                i.gender,
                c.name as country,
                c.country_id,
                s.name as state,
                s.state_id,
                sc.name as school,
                sc.school_id as school_id,
                IFNULL(ur.classname, "user-rank-unranked") AS classname
            FROM
                Groups_Identities gi
            INNER JOIN
                Identities i ON i.identity_id = gi.identity_id
            LEFT JOIN
                User_Rank ur ON ur.user_id = i.user_id
            LEFT JOIN
                States s ON s.state_id = i.state_id AND s.country_id = i.country_id
            LEFT JOIN
                Countries c ON c.country_id = i.country_id
            LEFT JOIN
                Identities_Schools isc ON isc.identity_school_id = i.current_identity_school_id
            LEFT JOIN
                Schools sc ON sc.school_id = isc.school_id
            WHERE
                gi.group_id = ?;';

        /** @var list<array{classname: string, country: null|string, country_id: null|string, gender: null|string, name: null|string, school: null|string, school_id: int|null, state: null|string, state_id: null|string, username: string}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$group->group_id]
        );
        $identities = [];
        foreach ($rs as $row) {
            if (!str_contains($row['username'], ':')) {
                $identities[] = [
                    'username' => $row['username'],
                    'name' => $row['name'],
                    'classname' => $row['classname'],
                    'gender' => null,
                    'country' => null,
                    'country_id' => null,
                    'school' => null,
                    'school_id' => null,
                    'state' => null,
                    'state_id' => null,
                ];
            } else {
                $identities[] = $row;
            }
        }
        return $identities;
    }

    /**
     * @return list<array{identity_id: int}>
     */
    public static function getGroupIdentities(\OmegaUp\DAO\VO\Groups $group) {
        $sql = 'SELECT
                i.identity_id
            FROM
                Groups_Identities gi
            INNER JOIN
                Identities i ON i.identity_id = gi.identity_id
            WHERE
                gi.group_id = ?;';

        /** @var list<array{identity_id: int}> */
        return \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$group->group_id]
        );
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
     * @return list<\OmegaUp\DAO\VO\GroupsIdentities>
     */
    final public static function getByGroupId(int $groupId): array {
        $sql = '
            SELECT
            ' .  \OmegaUp\DAO\DAO::getFields(
            \OmegaUp\DAO\VO\GroupsIdentities::FIELD_NAMES,
            'i'
        ) . '
            FROM
                Groups_Identities i
            WHERE
                group_id = ?;';

        $groupsIdentities = [];
        /** @var array{accept_teacher: bool|null, group_id: int, identity_id: int, is_invited: bool, privacystatement_consent_id: int|null, share_user_information: bool|null} $row */
        foreach (
            \OmegaUp\MySQLConnection::getInstance()->GetAll(
                $sql,
                [$groupId]
            ) as $row
        ) {
            $groupsIdentities[] = new \OmegaUp\DAO\VO\GroupsIdentities(
                $row
            );
        }
        return $groupsIdentities;
    }

    /**
     * @return list<string>
     */
    final public static function getUsernamesByGroupId(int $groupId): array {
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
                [$groupId]
            ) as $row
        ) {
            $identities[] = $row['username'];
        }
        return $identities;
    }

    /**
     * @param \OmegaUp\DAO\VO\Identities $identity
     * @param list<\OmegaUp\DAO\VO\Groups> $groups
     */
    public static function existsByGroupId($identity, $groups): bool {
        $placeholders = array_fill(0, count($groups), '?');
        $placeholders = join(',', $placeholders);
        $sql = "SELECT
                    COUNT(*)
                FROM
                    `Groups_Identities`
                WHERE
                    (
                        `group_id` IN ({$placeholders}) AND
                        `identity_id` = ?
                    );";
        $params = array_map(fn ($group) => $group->group_id, $groups);
        $params[] = $identity->identity_id;
        /** @var int */
        $count = \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, $params);
        return $count > 0;
    }
}
