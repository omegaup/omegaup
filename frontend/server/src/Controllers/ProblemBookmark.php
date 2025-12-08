<?php

namespace OmegaUp\Controllers;

/**
 * ProblemBookmarkController
 *
 * @psalm-type ProblemListItem=array{accepted: int, alias: string, can_be_removed?: bool, difficulty: float|null, difficulty_histogram: list<int>, points: float, problem_id: int, quality: float|null, quality_histogram: list<int>, quality_seal: bool, ratio: float, score: float, submissions: int, tags: list<array{name: string, source: string}>, title: string, visibility: int}
 * @psalm-type BookmarkCreateResponse=array{success: bool}
 * @psalm-type BookmarkDeleteResponse=array{success: bool}
 * @psalm-type BookmarkExistsResponse=array{bookmarked: bool}
 * @psalm-type BookmarkListResponse=array{problems: list<ProblemListItem>, total: int}
 */
class ProblemBookmark extends \OmegaUp\Controllers\Controller {
    /**
     * Bookmark a problem for the current user
     *
     * @omegaup-request-param string $problem_alias
     *
     * @return array{success: bool}
     */
    public static function apiCreate(\OmegaUp\Request $r): array {
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

        // Check if already bookmarked
        $currentUserId = $r->identity->identity_id;
        if (
            \OmegaUp\DAO\ProblemBookmarks::isProblemBookmarked(
                $currentUserId,
                $targetProblem->problem_id
            )
        ) {
            // Already bookmarked, return success
            return ['success' => true];
        }

        // Create bookmark
        $newBookmark = new \OmegaUp\DAO\VO\ProblemBookmarks();
        $newBookmark->identity_id = $currentUserId;
        $newBookmark->problem_id = $targetProblem->problem_id;
        $newBookmark->created_at = new \OmegaUp\Timestamp(\OmegaUp\Time::get());

        \OmegaUp\DAO\ProblemBookmarks::create($newBookmark);

        // Invalidate extraProfileDetails cache to reflect the new bookmark
        if (!is_null($r->identity->username)) {
            \OmegaUp\Cache::deleteFromCache(
                \OmegaUp\Cache::USER_PROFILE,
                "{$r->identity->username}-extraProfileDetails"
            );
        }

        return ['success' => true];
    }

    /**
     * Remove a bookmark for the current user
     *
     * @omegaup-request-param string $problem_alias
     *
     * @return array{success: bool}
     */
    public static function apiDelete(\OmegaUp\Request $r): array {
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

        // Check if bookmarked
        $currentUserId = $r->identity->identity_id;
        $existingBookmark = \OmegaUp\DAO\ProblemBookmarks::getByPK(
            $currentUserId,
            $targetProblem->problem_id
        );

        if (is_null($existingBookmark)) {
            // Not bookmarked, return success anyway
            return ['success' => true];
        }

        // Delete bookmark
        \OmegaUp\DAO\ProblemBookmarks::delete($existingBookmark);

        // Invalidate extraProfileDetails cache to reflect the removed bookmark
        if (!is_null($r->identity->username)) {
            \OmegaUp\Cache::deleteFromCache(
                \OmegaUp\Cache::USER_PROFILE,
                "{$r->identity->username}-extraProfileDetails"
            );
        }

        return ['success' => true];
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

        $currentUserId = $r->identity->identity_id;
        $isBookmarked = \OmegaUp\DAO\ProblemBookmarks::isProblemBookmarked(
            $currentUserId,
            $targetProblem->problem_id
        );

        return ['bookmarked' => $isBookmarked];
    }

    /**
     * Get list of bookmarked problems for the current user
     *
     * @return array{problems: list<ProblemListItem>, total: int}
     */
    public static function apiList(\OmegaUp\Request $r): array {
        $r->ensureIdentity();

        // Get all bookmarked problems
        $currentUserId = $r->identity->identity_id;
        $bookmarkedProblems = \OmegaUp\DAO\ProblemBookmarks::getAllBookmarkedProblems(
            $currentUserId
        );

        $totalCount = count($bookmarkedProblems);

        $shouldHideTags = \OmegaUp\DAO\Users::getHideTags(
            $currentUserId
        );

        $problemList = [];
        foreach ($bookmarkedProblems as $problemItem) {
            /** @var ProblemListItem */
            $problemData = $problemItem->asFilteredArray([
                'accepted',
                'alias',
                'difficulty',
                'difficulty_histogram',
                'points',
                'problem_id',
                'quality',
                'quality_histogram',
                'ratio',
                'score',
                'submissions',
                'tags',
                'title',
                'visibility',
                'quality_seal',
            ]);
            $problemData['tags'] = $shouldHideTags ? [] : \OmegaUp\DAO\Problems::getTagsForProblem(
                $problemItem,
                public: false,
                showUserTags: $problemItem->allow_user_add_tags
            );
            $problemList[] = $problemData;
        }

        return [
            'problems' => $problemList,
            'total' => $totalCount,
        ];
    }
}
