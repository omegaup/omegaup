<?php

require_once('base/Users_Badges.dao.base.php');
require_once('base/Users_Badges.vo.base.php');
/** UsersBadges Data Access Object (DAO).
  *
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para
  * almacenar de forma permanente y recuperar instancias de objetos {@link UsersBadges }.
  * @author alanboy
  * @access public
  * @package docs
  *
  */
class UsersBadgesDAO extends UsersBadgesDAOBase {
    public static function getUserOwnedBadges(Users $user) {
        global $conn;
        $sql = 'SELECT
                    ub.badge_alias, ub.assignation_time
                FROM
                    Users_Badges ub
                WHERE
                    ub.user_id = ?
                ORDER BY
                    ub.assignation_time ASC;';
        $args = [$user->user_id];
        return $conn->GetAll($sql, $args);
    }

    public static function getBadgeOwnersCount(string $badge) {
        global $conn;
        $sql = 'SELECT
                    COUNT(*) AS total
                FROM
                    Users_Badges
                WHERE
                    badge_alias = ?;';
        $args = [$badge];
        return $conn->GetRow($sql, $args)['total'];
    }

    public static function getBadgeFirstAssignationTime(string $badge) {
        global $conn;
        $sql = 'SELECT
                    ub.assignation_time
                FROM
                    Users_Badges ub
                WHERE
                    ub.badge_alias = ?
                ORDER BY
                    ub.assignation_time
                LIMIT 1;';
        $args = [$badge];
        $firstAssignation = $conn->getRow($sql, $args);
        return empty($firstAssignation) ? null : $firstAssignation['assignation_time'];
    }
}
