<?php

namespace OmegaUp\DAO;

/**
 * UsersBadges Data Access Object (DAO).
 *
 * Esta clase contiene toda la manipulacion de bases de datos que se necesita
 * para almacenar de forma permanente y recuperar instancias de objetos
 * {@link \OmegaUp\DAO\VO\UsersBadges}.
 *
 * @author alanboy
 * @access public
 * @package docs
 */
class UsersBadges extends \OmegaUp\DAO\Base\UsersBadges {
    public static function getUserOwnedBadges(\OmegaUp\DAO\VO\Users $user): array {
        $sql = 'SELECT
                    ub.badge_alias, ub.assignation_time
                FROM
                    Users_Badges ub
                WHERE
                    ub.user_id = ?
                ORDER BY
                    ub.assignation_time ASC;';
        $args = [$user->user_id];
        return \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $args);
    }

    public static function getUserBadgeAssignationTime(\OmegaUp\DAO\VO\Users $user, string $badge): ?int {
        $sql = 'SELECT
                    UNIX_TIMESTAMP(ub.assignation_time)
                FROM
                    Users_Badges ub
                WHERE
                    ub.user_id = ? AND ub.badge_alias = ?;';
        $args = [$user->user_id, $badge];
        return \OmegaUp\MySQLConnection::getInstance()->getOne($sql, $args);
    }

    public static function getBadgeOwnersCount(string $badge) {
        $sql = 'SELECT
                    COUNT(*)
                FROM
                    Users_Badges
                WHERE
                    badge_alias = ?;';
        $args = [$badge];
        return \OmegaUp\MySQLConnection::getInstance()->getOne($sql, $args);
    }

    public static function getBadgeFirstAssignationTime(string $badge) {
        $sql = 'SELECT
                    UNIX_TIMESTAMP(MIN(ub.assignation_time))
                FROM
                    Users_Badges ub
                WHERE
                    ub.badge_alias = ?
                LIMIT 1;';
        $args = [$badge];
        return \OmegaUp\MySQLConnection::getInstance()->getOne($sql, $args);
    }
}
