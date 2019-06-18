<?php
require_once 'libs/dao/User_Rank.dao.php';

/**
 * BadgesController
 *
 * @author carlosabcs
 */
class BadgesController extends Controller {
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
            $badges = UsersBadgesDAO::getAllBadges($user->user_id);
        } catch (UnauthorizedException $e) {
            // Just show badges
            $badges = UsersBadgesDAO::getAllBadges();
        }
        return [
            'total_badges' => sizeof($badges),
            'results' => $badges
        ];
    }
    // TODO: apiListProfileBadges, apiUserHasBadge
}