<?php

namespace OmegaUp\Controllers;

/**
 * BadgesController
 *
 * @author carlosabcs
 */
class Badge extends \OmegaUp\Controllers\Controller {
    /** @psalm-suppress MixedOperand OMEGAUP_ROOT is really a string. */
    const OMEGAUP_BADGES_ROOT = OMEGAUP_ROOT . '/badges';

    /**
     * @return list<string>
     */
    public static function getAllBadges(): array {
        $aliases = array_diff(
            scandir(
                strval(static::OMEGAUP_BADGES_ROOT)
            ),
            ['..', '.', 'default_icon.svg']
        );
        $results = [];
        foreach ($aliases as $alias) {
            if (!is_dir(strval(static::OMEGAUP_BADGES_ROOT) . "/${alias}")) {
                continue;
            }
            $results[] = $alias;
        }
        return $results;
    }

    /**
     * Returns a list of existing badges
     *
     * @return list<string>
     */
    public static function apiList(\OmegaUp\Request $r): array {
        return self::getAllBadges();
    }

    /**
     * Returns a list of badges owned by current user
     *
     * @return array{badges: list<array{assignation_time: string, badge_alias: string}>}
     */
    public static function apiMyList(\OmegaUp\Request $r): array {
        $r->ensureIdentity();
        return [
            'badges' => is_null($r->user) ?
                [] :
                \OmegaUp\DAO\UsersBadges::getUserOwnedBadges($r->user),
        ];
    }

    /**
     * Returns a list of badges owned by a certain user
     *
     * @return array{badges: list<array{assignation_time: string, badge_alias: string}>, status: string}
     */
    public static function apiUserList(\OmegaUp\Request $r): array {
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
     * @return array{assignation_time: int|null, status: string}
     */
    public static function apiMyBadgeAssignationTime(\OmegaUp\Request $r): array {
        $r->ensureIdentity();
        \OmegaUp\Validators::validateValidAlias(
            $r['badge_alias'],
            'badge_alias'
        );
        \OmegaUp\Validators::validateBadgeExists(
            $r['badge_alias'],
            self::getAllBadges()
        );
        return [
            'status' => 'ok',
            'assignation_time' => is_null($r->user) ?
                null :
                \OmegaUp\DAO\UsersBadges::getUserBadgeAssignationTime(
                    $r->user,
                    $r['badge_alias']
                ),
        ];
    }

    /**
     * Returns the number of owners and the first
     * assignation timestamp for a certain badge
     *
     * @return array{first_assignation: int|null, total_users: int, owners_count: int, status: string}
     */
    public static function apiBadgeDetails(\OmegaUp\Request $r): array {
        \OmegaUp\Validators::validateValidAlias(
            $r['badge_alias'],
            'badge_alias'
        );
        \OmegaUp\Validators::validateBadgeExists(
            $r['badge_alias'],
            self::getAllBadges()
        );
        $totalUsers = max(\OmegaUp\DAO\Users::getUsersCount(), 1);
        $ownersCount = \OmegaUp\DAO\UsersBadges::getBadgeOwnersCount(
            $r['badge_alias']
        );
        $firstAssignation = \OmegaUp\DAO\UsersBadges::getBadgeFirstAssignationTime(
            $r['badge_alias']
        );
        return [
            'status' => 'ok',
            'first_assignation' => $firstAssignation,
            'total_users' => $totalUsers,
            'owners_count' => $ownersCount,
        ];
    }

    /**
     * @return array{smartyProperties: array{badge_alias: string}, template: string}
     */
    public static function getDetailsForSmarty(\OmegaUp\Request $r) {
        $r->ensureIdentity();
        \OmegaUp\Validators::validateValidAlias(
            $r['badge_alias'],
            'badge_alias'
        );

        \OmegaUp\Validators::validateBadgeExists(
            $r['badge_alias'],
            \OmegaUp\Controllers\Badge::getAllBadges()
        );
        return [
            'smartyProperties' => [
                'badge_alias' => $r['badge_alias'],
            ],
            'template' => 'badge.details.tpl',
        ];
    }
}
