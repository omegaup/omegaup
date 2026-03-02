<?php

namespace OmegaUp\Controllers;

/**
 * ProblemBookmarkController
 *
 * @psalm-type BookmarkProblem=array{alias: string, title: string}
 */
class ProblemBookmark extends \OmegaUp\Controllers\Controller {
    /**
     * Toggle a bookmark for the current user
     *
     * @omegaup-request-param string $problem_alias
     *
     * @return array{status: 'ok', bookmarked: bool}
     */
    public static function apiToggle(\OmegaUp\Request $r): array {
        $r->ensureIdentity();
        $problemAlias = $r->ensureString(
            'problem_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );

        $targetProblem = \OmegaUp\DAO\Problems::getByAlias($problemAlias);
        if (is_null($targetProblem) || is_null($targetProblem->problem_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        $currentIdentityId = $r->identity->identity_id;
        $alreadyBookmarked = \OmegaUp\DAO\ProblemBookmarks::existsByPK(
            $currentIdentityId,
            $targetProblem->problem_id
        );

        if ($alreadyBookmarked) {
            $existingBookmark = \OmegaUp\DAO\ProblemBookmarks::getByPK(
                $currentIdentityId,
                $targetProblem->problem_id
            );
            if (!is_null($existingBookmark)) {
                self::removeBookmark(
                    $existingBookmark,
                    $r->identity->username
                );
            }

            return [
                'status' => 'ok',
                'bookmarked' => false,
            ];
        }

        self::createBookmark(
            $currentIdentityId,
            $targetProblem->problem_id,
            $r->identity->username
        );

        return [
            'status' => 'ok',
            'bookmarked' => true,
        ];
    }

    /**
     * Check if a problem is bookmarked by the current user
     *
     * @omegaup-request-param string $problem_alias
     *
     * @return array{bookmarked: bool}
     */
    public static function apiExists(\OmegaUp\Request $r): array {
        $r->ensureIdentity();
        $problemAlias = $r->ensureString(
            'problem_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );

        // Validate that the problem exists
        $targetProblem = \OmegaUp\DAO\Problems::getByAlias($problemAlias);
        if (is_null($targetProblem) || is_null($targetProblem->problem_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        $currentIdentityId = $r->identity->identity_id;
        $isBookmarked = \OmegaUp\DAO\ProblemBookmarks::existsByPK(
            $currentIdentityId,
            $targetProblem->problem_id
        );

        return ['bookmarked' => $isBookmarked];
    }

    /**
     * Get list of bookmarked problems for the current user
     *
     * @return array{problems: list<BookmarkProblem>, total: int}
     */
    public static function apiList(\OmegaUp\Request $r): array {
        $r->ensureIdentity();

        // Get all bookmarked problems
        $currentIdentityId = $r->identity->identity_id;
        $bookmarkedProblems = \OmegaUp\DAO\ProblemBookmarks::getAllBookmarkedProblems(
            $currentIdentityId
        );

        return [
            'problems' => $bookmarkedProblems,
            'total' => count($bookmarkedProblems),
        ];
    }

    private static function createBookmark(
        int $identityId,
        int $problemId,
        string $username
    ): void {
        \OmegaUp\DAO\ProblemBookmarks::create(
            new \OmegaUp\DAO\VO\ProblemBookmarks([
                'identity_id' => $identityId,
                'problem_id' => $problemId,
            ])
        );

        self::clearProfileCache($username);
    }

    private static function removeBookmark(
        \OmegaUp\DAO\VO\ProblemBookmarks $bookmark,
        string $username
    ): void {
        \OmegaUp\DAO\ProblemBookmarks::delete($bookmark);
        self::clearProfileCache($username);
    }

    private static function clearProfileCache(string $username): void {
        \OmegaUp\Cache::deleteFromCache(
            \OmegaUp\Cache::USER_PROFILE,
            "{$username}-extraProfileDetails"
        );
    }
}
