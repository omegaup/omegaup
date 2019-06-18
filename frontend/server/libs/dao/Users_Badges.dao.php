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
    const OMEGAUP_BADGES_ROOT = OMEGAUP_ROOT . '/badges';

    private static function getTimeForBadge(array $queryResults, string $badge) {
        foreach ($queryResults as $result) {
            if ($result['alias'] === $alias) {
                return $result['assignationTime'];
            }
        }
        return null;
    }

    private static function getUserOwnedBadges(string $userId) {
        global $conn;
        $sql = 'SELECT ub.badge_alias, ub.assignation_time FROM Users_Badges ub WHERE ub.user_id = ? ORDER BY ub.assignation_time ASC;';
        $args = [$userId];
        return $conn->GetAll($sql, $args);
    }

    public static function getAllBadges($userId = null) {
        $aliases = array_diff(scandir(static::OMEGAUP_BADGES_ROOT), ['..', '.', 'default_icon.svg']);
        $results = [];
        $ownedBadges = $userId ? self::getUserOwnedBadges($userId) : [];
        foreach ($aliases as $alias) {
            if (!is_dir(static::OMEGAUP_BADGES_ROOT . "/${alias}")) {
                continue;
            }
            $results[] = [
                'alias' => $alias,
                'assignationTime' => self::getTimeForBadge($ownedBadges, $alias),
            ];
        }
        return $results;
    }

    public static function getUserProfileBadges(string $profileOwnerId, string $visitorId) {
        // Solo se listarán las badges del usuario
        $profileBadges = self::getUserOwnedBadges($profileOwnerId);
        $visitorBadges = $visitorId ? self::getUserOwnedBadges($visitorId) : [];
        $results = [];
        foreach ($ownedBadges as $badge) {
            $results[] = [
                'alias' => $ownedBadges['badge_alias'],
                'assignationTime' => $ownedBadges['assignation_time'],
                'owned' => !!self::getTimeForBadge($ownedBadges, $alias),
            ];
        }
        return $results;
    }

    public static function userHasBadge(string $userId, string $badgeAlias) {
        global $conn;
        $sql = 'SELECT ub.assignation_time FROM Users_Badges ub WHERE ub.user_id = ? AND ub.badge_alias = ?;';
        $args = [$userId, $badgeAlias];
        $rs = $conn->GetRow($sql, $params);
        if (empty($rs)) {
            return [
                'hasBadge' => false,
            ];
        }
        return [
            'hasBadge' => true,
            'assignationTime' => $rs['assignation_time'],
        ];
    }
}
