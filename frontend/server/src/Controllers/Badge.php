<?php

namespace OmegaUp\Controllers;

/**
 * BadgesController
 *
 * @author carlosabcs
 */
class Badge extends \OmegaUp\Controllers\Controller {
    const OMEGAUP_BADGES_ROOT = OMEGAUP_ROOT . '/badges';

    public static function getAllBadges(): array {
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
     * Returns a list of existing badges
     *
     * @param \OmegaUp\Request $r
     * @return array
     */
    public static function apiList(\OmegaUp\Request $r) {
        return self::getAllBadges();
    }

    /**
     * Returns a list of badges owned by current user
     *
     * @param \OmegaUp\Request $r
     * @return array
     */
    public static function apiMyList(\OmegaUp\Request $r) {
        $r->ensureIdentity();
        return [
            'status' => 'ok',
            'badges' => is_null($r->user) ?
                [] :
                \OmegaUp\DAO\UsersBadges::getUserOwnedBadges($r->user),
        ];
    }

    /**
     * Returns a list of badges owned by a certain user
     *
     * @param \OmegaUp\Request $r
     * @return array
     */
    public static function apiUserList(\OmegaUp\Request $r) {
        \OmegaUp\Validators::validateValidUsername(
            $r['target_username'],
            'target_username'
        );
        $user = \OmegaUp\DAO\Users::FindByUsername($r['target_username']);
        if (is_null($user)) {
            throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
        }
        return [
            'status' => 'ok',
            'badges' => \OmegaUp\DAO\UsersBadges::getUserOwnedBadges($user),
        ];
    }

    /**
     * Returns a the assignation timestamp of a badge
     * for current user.
     *
     * @param \OmegaUp\Request $r
     * @return array
     */
    public static function apiMyBadgeAssignationTime(\OmegaUp\Request $r) {
        $r->ensureIdentity();
        \OmegaUp\Validators::validateValidAlias($r['badge_alias'], 'badge_alias');
        \OmegaUp\Validators::validateBadgeExists($r['badge_alias'], self::getAllBadges());
        return [
            'status' => 'ok',
            'assignation_time' => is_null($r->user) ?
                null :
                \OmegaUp\DAO\UsersBadges::getUserBadgeAssignationTime($r->user, $r['badge_alias']),
        ];
    }

    /**
     * Returns the number of owners and the first
     * assignation timestamp for a certain badge
     *
     * @param \OmegaUp\Request $r
     * @return array
     */
    public static function apiBadgeDetails(\OmegaUp\Request $r) {
        \OmegaUp\Validators::validateValidAlias($r['badge_alias'], 'badge_alias');
        \OmegaUp\Validators::validateBadgeExists($r['badge_alias'], self::getAllBadges());
        $totalUsers = max(\OmegaUp\DAO\Users::getUsersCount(), 1);
        $ownersCount = \OmegaUp\DAO\UsersBadges::getBadgeOwnersCount($r['badge_alias']);
        $firstAssignation = \OmegaUp\DAO\UsersBadges::getBadgeFirstAssignationTime($r['badge_alias']);
        return [
            'status' => 'ok',
            'first_assignation' => $firstAssignation,
            'owners_percentage' => (($ownersCount / $totalUsers) * 100)
        ];
    }
}
