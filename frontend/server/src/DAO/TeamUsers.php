<?php

namespace OmegaUp\DAO;

/**
 * Teams Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\TeamUsers}.
 *
 * @access public
 * @package docs
 */
class TeamUsers extends \OmegaUp\DAO\Base\TeamUsers {
    /**
     * @return list<\OmegaUp\DAO\VO\TeamUsers>
     */
    public static function getByTeamId(int $teamId): array {
        $sql = 'SELECT
                    `team_id`,
                    `identity_id`
                FROM
                    `Team_Users`
                WHERE
                    `team_id` = ?
                LIMIT 100;';
        /** @var list<array{identity_id: int, team_id: int}> */
        $rs = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, [$teamId]);
        $usersTeams = [];
        foreach ($rs as $row) {
            $usersTeams[] = new \OmegaUp\DAO\VO\TeamUsers($row);
        }
        return $usersTeams;
    }

    /**
     * Links identities to a team in bulk without clearing an existing origin flag.
     *
     * @param string[] $usernames
     */
    public static function createTeamUsersBulk(
        int $teamId,
        array $usernames,
        bool $isOrigin = false
    ): int {
        if (empty($usernames)) {
            return 0;
        }
        $originFlag = $isOrigin ? 1 : 0;
        $placeholders = join(',', array_fill(0, count($usernames), '?'));
        $sql = "INSERT INTO Team_Users (team_id, identity_id, is_origin)
                SELECT ?, identity_id, ? FROM Identities
                WHERE username IN ($placeholders)
                ON DUPLICATE KEY UPDATE
                    is_origin = GREATEST(is_origin, ?);";
        $params = array_merge(
            [$teamId, $originFlag],
            $usernames,
            [$originFlag]
        );
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * Returns whether $identityId was originally created as a member of a team
     * in $teamGroupId.
     */
    public static function isOriginIdentityInTeamGroup(
        int $identityId,
        int $teamGroupId
    ): bool {
        $sql = '
            SELECT
                COUNT(*)
            FROM
                Team_Users AS tu
            INNER JOIN
                Teams AS t ON t.team_id = tu.team_id
            WHERE
                tu.identity_id = ?
                AND tu.is_origin = 1
                AND t.team_group_id = ?;';
        /** @var int */
        $count = \OmegaUp\MySQLConnection::getInstance()->GetOne(
            $sql,
            [$identityId, $teamGroupId]
        );
        return $count > 0;
    }

    /**
     * @return array{pageNumber: int, teamsUsers: list<array{classname: string, isMainUserIdentity: bool, name: null|string, team_alias: string, team_name: null|string, username: string}>, totalRows: int}
     */
    public static function getByTeamGroupId(
        int $teamsGroupId,
        int $page = 1,
        int $pageSize = 100
    ): array {
        $sql = 'SELECT
                    i.username,
                    i.name,
                    it.username AS team_alias,
                    it.name AS team_name,
                    CAST(IFNULL(i.user_id, FALSE) AS UNSIGNED) AS isMainUserIdentity,
                    IFNULL(ur.classname, "user-rank-unranked") AS classname
                FROM
                    Team_Users tu
                INNER JOIN
                    Teams t ON t.team_id = tu.team_id
                INNER JOIN
                    Identities i ON i.identity_id = tu.identity_id
                LEFT JOIN
                    User_Rank ur ON ur.user_id = i.user_id
                INNER JOIN
                    Identities it ON it.identity_id = t.identity_id
                LEFT JOIN
                    Users u ON i.user_id = u.user_id
                WHERE
                    team_group_id = ?
                ';

        $sqlCount = "
        SELECT
            COUNT(*)
        FROM
            ({$sql}) AS total";

        $sqlLimit = 'LIMIT ?, ?;';

        /** @var int */
        $totalRows = \OmegaUp\MySQLConnection::getInstance()->GetOne(
            $sqlCount,
            [$teamsGroupId]
        );

        /** @var list<array{classname: string, isMainUserIdentity: int, name: null|string, team_alias: string, team_name: null|string, username: string}> */
        $teamsUsers = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql . $sqlLimit,
            [
                $teamsGroupId,
                max(0, $page - 1) * $pageSize,
                $pageSize,
            ]
        );
        $result = [];
        foreach ($teamsUsers as $row) {
            $row['isMainUserIdentity'] = boolval($row['isMainUserIdentity']);
            $result[] = $row;
        }

        return [
            'pageNumber' => $page,
            'teamsUsers' => $result,
            'totalRows' => $totalRows,
        ];
    }
}
