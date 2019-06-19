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
    private static function getUserOwnedBadges(str $userId) {
        global $conn;
        $sql = 'SELECT
                    ub.badge_alias, ub.assignation_time
                FROM
                    Users_Badges ub
                WHERE
                    ub.user_id = ?
                ORDER BY
                    ub.assignation_time ASC;';
        $args = [$userId];
        return $conn->GetAll($sql, $args);
    }

    public static function getBadgeAssignationTime(Users $user, string $badgeAlias) {
        global $conn;
        $sql = 'SELECT
                    ub.assignation_time
                FROM
                    Users_Badges ub
                WHERE
                    ub.user_id = ? AND ub.badge_alias = ?;';
        $args = [$user->user_id, $badgeAlias];
        $rs = $conn->GetRow($sql, $params);
        if (empty($rs)) {
            return null;
        }
        return $rs['assignation_time'];
    }
}
