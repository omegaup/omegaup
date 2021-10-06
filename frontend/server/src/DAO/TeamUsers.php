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
     * @param list<string> $usernames
     */
    public static function createTeamUsersBulk(
        int $teamId,
        array $usernames
    ): int {
        $placeholders = array_fill(0, count($usernames), '?');
        $placeholders = join(',', $placeholders);
        $sql = "REPLACE INTO Team_Users (team_id, identity_id)
                SELECT ? AS team_id, identity_id FROM Identities
                WHERE username IN ($placeholders);";
        $params = array_merge([$teamId], $usernames);
        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, $params);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * @return array{pageNumber: int, teamsUsers: list<array{classname: string, isMainUserIdentity: bool, name: null|string, team_alias: string, team_name: null|string, username: string}>, totalRows: int}
     */
    public static function getByTeamGroupId(
        int $teamsGroupId,
        int $page = 1,
        int $pageSize = 100
    ): array {
        $offset = ($page - 1) * $pageSize;

        $sql = 'SELECT
                    i.username,
                    i.name,
                    it.username AS team_alias,
                    it.name AS team_name,
                    CAST(IFNULL(i.user_id, FALSE) AS UNSIGNED) AS isMainUserIdentity,
                    IFNULL(
                        (
                            SELECT urc.classname FROM
                                User_Rank_Cutoffs urc
                            WHERE
                                urc.score <= (
                                        SELECT
                                            ur.score
                                        FROM
                                            User_Rank ur
                                        WHERE
                                            ur.user_id = u.user_id
                                    )
                            ORDER BY
                                urc.percentile ASC
                            LIMIT
                                1
                        ),
                        \'user-rank-unranked\'
                    ) AS classname
                FROM
                    Team_Users tu
                INNER JOIN
                    Teams t
                ON
                    t.team_id = tu.team_id
                INNER JOIN
                    Identities i
                ON
                    i.identity_id = tu.identity_id
                INNER JOIN
                    Identities it
                ON
                    it.identity_id = t.identity_id
                LEFT JOIN
                    Users u
                ON
                    i.user_id = u.user_id
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
            [$teamsGroupId, $offset, $pageSize]
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
