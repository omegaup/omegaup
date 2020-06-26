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
    /**
     * @return list<array{assignation_time: \OmegaUp\Timestamp, badge_alias: string, first_assignation: null, owners_count: int, total_users: int}>
     */
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
        /** @var list<array{assignation_time: \OmegaUp\Timestamp, badge_alias: string}> */
        $result = \OmegaUp\MySQLConnection::getInstance()->GetAll($sql, $args);
        $badges = [];
        foreach ($result as $badge) {
            $badges[] = [
                'assignation_time' => $badge['assignation_time'],
                'badge_alias' => $badge['badge_alias'],
                'first_assignation' => null,
                'owners_count' => 0,
                'total_users' => 0,
            ];
        }
        return $badges;
    }

    public static function getUserBadgeAssignationTime(
        \OmegaUp\DAO\VO\Users $user,
        string $badge
    ): ?\OmegaUp\Timestamp {
        $sql = 'SELECT
                    ub.assignation_time
                FROM
                    Users_Badges ub
                WHERE
                    ub.user_id = ? AND ub.badge_alias = ?;';
        $args = [$user->user_id, $badge];
        /** @var \OmegaUp\Timestamp|null */
        return \OmegaUp\MySQLConnection::getInstance()->getOne($sql, $args);
    }

    public static function getBadgeOwnersCount(string $badge): int {
        $sql = 'SELECT
                    COUNT(*)
                FROM
                    Users_Badges
                WHERE
                    badge_alias = ?;';
        $args = [$badge];
        /** @var int */
        return \OmegaUp\MySQLConnection::getInstance()->getOne($sql, $args);
    }

    public static function getBadgeFirstAssignationTime(string $badge): ?\OmegaUp\Timestamp {
        $sql = 'SELECT
                    MIN(ub.assignation_time)
                FROM
                    Users_Badges ub
                WHERE
                    ub.badge_alias = ?
                LIMIT 1;';
        $args = [$badge];
        /** @var \OmegaUp\Timestamp|null */
        return \OmegaUp\MySQLConnection::getInstance()->getOne($sql, $args);
    }
}
