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
                    `user_id`
                FROM
                    `Team_Users`
                WHERE
                    `team_id` = ?
                LIMIT 100;';
        /** @var list<array{team_id: int, user_id: int}> */
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
        $sql = "REPLACE INTO Team_Users (team_id, user_id)
                SELECT ? AS team_id, user_id FROM Identities
                WHERE username IN ($placeholders) AND user_id IS NOT NULL;";

        \OmegaUp\MySQLConnection::getInstance()->Execute(
            $sql,
            array_merge([$teamId], $usernames)
        );
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    /**
     * @return array{pageNumber: int, teamsUsers: list<array{classname: string, name: null|string, team_alias: string, team_name: null|string, username: string}>, totalRows: int}
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
                                            ur.user_id = tu.user_id
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
                    i.user_id = tu.user_id
                INNER JOIN
                    Identities it
                ON
                    it.identity_id = t.identity_id
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

        /** @var list<array{classname: string, name: null|string, team_alias: string, team_name: null|string, username: string}> $teamsUsers */
        $teamsUsers = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql . $sqlLimit,
            [$teamsGroupId, $offset, $pageSize]
        );

        return [
            'pageNumber' => $page,
            'teamsUsers' => $teamsUsers,
            'totalRows' => $totalRows,
        ];
    }
}
