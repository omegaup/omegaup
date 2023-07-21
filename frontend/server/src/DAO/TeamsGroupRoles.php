<?php

namespace OmegaUp\DAO;

/**
 * TeamsGroupRoles Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\TeamsGroupRoles}.
 *
 * @access public
 * @package docs
 */
class TeamsGroupRoles extends \OmegaUp\DAO\Base\TeamsGroupRoles {
    public static function deleteAllTeamGroupsForAclId(int $aclId): int {
        $sql = '
            DELETE FROM
                `Teams_Group_Roles`
            WHERE
                `acl_id` = ?;';

        \OmegaUp\MySQLConnection::getInstance()->Execute($sql, [$aclId]);
        return \OmegaUp\MySQLConnection::getInstance()->Affected_Rows();
    }

    public static function isContestant(int $identityId, int $aclId): bool {
        $sql = '
            SELECT
                COUNT(*) > 0
            FROM
                Teams_Group_Roles tgr
            INNER JOIN
                Teams t ON t.team_group_id = tgr.team_group_id
            WHERE
                t.identity_id = ? AND tgr.role_id = ? AND tgr.acl_id = ?;';
        $params = [
            $identityId,
            \OmegaUp\Authorization::CONTESTANT_ROLE,
            $aclId,
        ];
        return boolval(
            /** @var int|null */
            \OmegaUp\MySQLConnection::getInstance()->GetOne($sql, $params)
        );
    }

    /**
     * @return array{alias: string, name: string}|null
     */
    public static function getTeamsGroup(int $problemsetId) {
        $sql = '
            SELECT
                tg.alias, tg.name
            FROM
                Problemsets p
            INNER JOIN
                Teams_Group_Roles tgr ON tgr.acl_id = p.acl_id
            INNER JOIN
                `Team_Groups` AS tg ON tg.team_group_id = tgr.team_group_id
            WHERE
                p.problemset_id = ? AND
                tgr.role_id = ?
            LIMIT 1;
        ';
        $params = [
            $problemsetId,
            \OmegaUp\Authorization::CONTESTANT_ROLE,
        ];

        /** @var array{alias: string, name: string}|null */
        return \OmegaUp\MySQLConnection::getInstance()->GetRow(
            $sql,
            $params
        );
    }
}
