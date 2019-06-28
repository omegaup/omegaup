<?php

/**
 * BadgesController
 *
 * @author carlosabcs
 */
class BadgeController extends Controller {
    const OMEGAUP_BADGES_ROOT = OMEGAUP_ROOT . '/badges';

    /**
     * Returns a list of existing badges
     *
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     */
    public static function apiList(Request $r) {
        $aliases = array_diff(scandir(static::OMEGAUP_BADGES_ROOT), ['..', '.', 'default_icon.svg']);
        $results = [];
        foreach ($aliases as $alias) {
            if (!is_dir(static::OMEGAUP_BADGES_ROOT . "/${alias}")) {
                continue;
            }
            $results[] = $alias;
        }
        return $results;
    }

    /**
     * Returns a list of badges owned by current user
     *
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     */
    public static function apiMyList(Request $r) {
        self::authenticateRequest($r);
        return [
            'status' => 'ok',
            'badges' => is_null($r->user) ?
                [] :
                UsersBadgesDAO::getUserOwnedBadges($r->user),
        ];
    }

    /**
     * Returns a list of badges owned by a certain user
     *
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     */
    public static function apiUserList(Request $r) {
        try {
            $user = UsersDAO::FindByUsername($r['target_username']);
            if (is_null($user)) {
                throw new NotFoundException('userNotExist');
            }
            return [
                'status' => 'ok',
                'badges' => UsersBadgesDAO::getUserOwnedBadges($user),
            ];
        } catch (ApiException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }
    }

    /**
     * Returns a the assignation timestamp of a badge
     * for current user.
     *
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     */
    public static function apiMyBadgeAssignationTime(Request $r) {
        self::authenticateRequest($r);
        try {
            if (is_null($r->user)) {
                throw new NotFoundException('userNotExist');
            }
            $allBadges = BadgeController::apiList($r);
            $badge = $r['badge_alias'];
            if (!in_array($badge, $allBadges)) {
                throw new notFoundException('badgeNotExist');
            }
            return [
                'status' => 'ok',
                'assignation_time' => UsersBadgesDAO::getUserBadgeAssignationTime($r->user, $badge),
            ];
        } catch (ApiException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }
    }
}
