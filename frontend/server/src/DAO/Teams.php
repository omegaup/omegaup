<?php

namespace OmegaUp\DAO;

/**
 * Teams Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\Teams}.
 *
 * @author juan.pablo
 * @access public
 * @package docs
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
        $row = \OmegaUp\MySQLConnection::getInstance()->GetRow($sql, $params);
        if (empty($row)) {
            return null;
        }
        return new \OmegaUp\DAO\VO\Teams($row);
    }
}
