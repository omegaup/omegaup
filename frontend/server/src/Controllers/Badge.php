<?php

namespace OmegaUp\Controllers;

/**
 * BadgesController

 * @psalm-type Badge=array{assignation_time: \OmegaUp\Timestamp|null, badge_alias: string, first_assignation: \OmegaUp\Timestamp|null, owners_count: int, total_users: int}
 * @psalm-type BadgeDetailsPayload=array{badge: Badge}
 * @psalm-type BadgeListPayload=array{badges: list<string>, ownedBadges: list<Badge>}
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
     * @return array{badges: list<Badge>}
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
     * @omegaup-request-param mixed $target_username
     *
     * @return array{badges: list<Badge>}
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
            'badges' => \OmegaUp\DAO\UsersBadges::getUserOwnedBadges($user),
        ];
    }

    /**
     * Returns a the assignation timestamp of a badge
     * for current user.
     *
     * @omegaup-request-param mixed $badge_alias
     *
     * @return array{assignation_time: \OmegaUp\Timestamp|null}
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
     * @omegaup-request-param mixed $badge_alias
     *
     * @return Badge
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
        return self::getBadgeDetails($r['badge_alias']);
    }

    /**
     * Returns the number of owners and the first
     * assignation timestamp for a certain badge
     *
     * @return Badge
     */
    private static function getBadgeDetails(string $badgeAlias): array {
        $totalUsers = max(\OmegaUp\DAO\Users::getUsersCount(), 1);
        $ownersCount = \OmegaUp\DAO\UsersBadges::getBadgeOwnersCount(
            $badgeAlias
        );
        $firstAssignation = \OmegaUp\DAO\UsersBadges::getBadgeFirstAssignationTime(
            $badgeAlias
        );
        return [
            'assignation_time' => null,
            'badge_alias' => $badgeAlias,
            'first_assignation' => $firstAssignation,
            'total_users' => $totalUsers,
            'owners_count' => $ownersCount,
        ];
    }
    /**
     * @return array{smartyProperties: array{payload: BadgeListPayload, title: string}, entrypoint: string}
     */
    public static function getBadgeListForSmarty(\OmegaUp\Request $r) {
        $r->ensureIdentity();
        $badges = self::apiList($r);
        $ownedBadges = self::apiMyList($r);
        return [
            'smartyProperties' => [
                'payload' => [
                    'badges' => $badges,
                    'ownedBadges' => $ownedBadges['badges']
                ],
                'title' => new \OmegaUp\TranslationString('omegaupTitleBadges')
            ],
            'entrypoint' => 'badge_list',
        ];
    }

    /**
     * @omegaup-request-param mixed $badge_alias
     *
     * @return array{smartyProperties: array{payload: BadgeDetailsPayload, title: string}, entrypoint: string}
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

        $details = self::getBadgeDetails($r['badge_alias']);
        if (!is_null($r->user)) {
            $details['assignation_time'] = \OmegaUp\DAO\UsersBadges::getUserBadgeAssignationTime(
                $r->user,
                $r['badge_alias']
            );
        }
        return [
            'smartyProperties' => [
                'payload' => [
                    'badge' => $details,
                ],
                'title' => new \OmegaUp\TranslationString('omegaupTitleBadges')
            ],
            'entrypoint' => 'badge_details',
        ];
    }
}
