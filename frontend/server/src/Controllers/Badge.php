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
        /**
         * @psalm-suppress MixedArgument OMEGAUP_BADGES_ROOT is really a string.
         * @var array<string> $aliases
         */
        $aliases = array_diff(
            scandir(static::OMEGAUP_BADGES_ROOT),
            ['..', '.', 'default_icon.svg']
        );
        $results = [];
        foreach ($aliases as $alias) {
            /** @psalm-suppress MixedOperand OMEGAUP_BADGES_ROOT is really a string. */
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
            'badges' => $r->user === null ?
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
        if ($user === null) {
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
     * @return array{assignation_time: \OmegaUp\Timestamp|null}
     *
     * @omegaup-request-param null|string $badge_alias
     */
    public static function apiMyBadgeAssignationTime(\OmegaUp\Request $r): array {
        $r->ensureIdentity();
        $badgeAlias = $r->ensureString(
            'badge_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        \OmegaUp\Validators::validateBadgeExists(
            $badgeAlias,
            self::getAllBadges()
        );
        return [
            'assignation_time' => $r->user === null ?
                null :
                \OmegaUp\DAO\UsersBadges::getUserBadgeAssignationTime(
                    $r->user,
                    $badgeAlias
                ),
        ];
    }

    /**
     * Returns the number of owners and the first
     * assignation timestamp for a certain badge
     *
     * @return Badge
     *
     * @omegaup-request-param null|string $badge_alias
     */
    public static function apiBadgeDetails(\OmegaUp\Request $r): array {
        $badgeAlias = $r->ensureString(
            'badge_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        \OmegaUp\Validators::validateBadgeExists(
            $badgeAlias,
            self::getAllBadges()
        );
        return self::getBadgeDetails($badgeAlias);
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
     * @return array{templateProperties: array{payload: BadgeListPayload, title: \OmegaUp\TranslationString}, entrypoint: string}
     */
    public static function getBadgeListForTypeScript(\OmegaUp\Request $r) {
        $r->ensureIdentity();
        $badges = self::apiList($r);
        $ownedBadges = self::apiMyList($r);
        return [
            'templateProperties' => [
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
     * @return array{templateProperties: array{payload: BadgeDetailsPayload, title: \OmegaUp\TranslationString}, entrypoint: string}
     *
     * @omegaup-request-param string $badge_alias
     */
    public static function getDetailsForTypeScript(\OmegaUp\Request $r) {
        $r->ensureIdentity();
        $badgeAlias = $r->ensureString(
            'badge_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );

        \OmegaUp\Validators::validateBadgeExists(
            $badgeAlias,
            \OmegaUp\Controllers\Badge::getAllBadges()
        );

        $details = self::getBadgeDetails($badgeAlias);
        if ($r->user !== null) {
            $details['assignation_time'] = \OmegaUp\DAO\UsersBadges::getUserBadgeAssignationTime(
                $r->user,
                $badgeAlias
            );
        }
        return [
            'templateProperties' => [
                'payload' => [
                    'badge' => $details,
                ],
                'title' => new \OmegaUp\TranslationString('omegaupTitleBadges')
            ],
            'entrypoint' => 'badge_details',
        ];
    }
}
