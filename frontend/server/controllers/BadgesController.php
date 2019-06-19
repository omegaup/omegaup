<?php
require_once 'libs/dao/User_Rank.dao.php';

/**
 * BadgesController
 *
 * @author carlosabcs
 */
class BadgesController extends Controller {
    const OMEGAUP_BADGES_ROOT = OMEGAUP_ROOT . '/badges';

    /**
     * Returns a list of existing badges
     *
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     */
    public static function apiList(Request $r) {
        // Check who is visiting, a not logged user can still
        // view the list of badges.
        try {
            self::authenticateRequest($r);
            $user = self::resolveTargetUser($r);
            $badges = self::getAllBadges(new Users(['user_id' => $user->user_id]));
        } catch (UnauthorizedException $e) {
            // Just show badges
            $badges = UsersBadgesDAO::getAllBadges(null);
        }
        return [
            'results' => $badges
        ];
    }

    private static function getTimeForBadge(array $queryResults, string $badge) {
        foreach ($queryResults as $result) {
            if ($result['alias'] === $alias) {
                return $result['assignationTime'];
            }
        }
        return null;
    }

    private static function getAllBadges(Users $user) {
        $aliases = array_diff(scandir(static::OMEGAUP_BADGES_ROOT), ['..', '.', 'default_icon.svg']);
        $results = [];
        $ownedBadges = $user ? UsersBadgesDAO::getUserOwnedBadges($user->user_id) : [];
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
    // TODO: apiListProfileBadges, apiUserHasBadge
}
