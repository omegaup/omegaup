<?php

namespace OmegaUp\Controllers;

/**
 * ProblemBookmarkController
 *
 * @psalm-type BookmarkProblem=array{alias: string, attempted: bool, solved: bool, title: string}
 */
class ProblemBookmark extends \OmegaUp\Controllers\Controller {
    /**
     * Toggle a bookmark for the current user
     *
     * @omegaup-request-param null|string $contest_alias
     * @omegaup-request-param string $problem_alias
     * @omegaup-request-param int|null $problemset_id
     *
     * @return array{status: 'ok', bookmarked: bool}
     */
    public static function apiToggle(\OmegaUp\Request $r): array {
        $r->ensureIdentity();
        $problemAlias = $r->ensureString(
            'problem_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $contestAlias = $r->ensureOptionalString(
            'contest_alias',
            required: false,
            validator: fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $problemsetId = $r->ensureOptionalInt('problemset_id');

        $targetProblem = \OmegaUp\DAO\Problems::getByAlias($problemAlias);
        if (is_null($targetProblem) || is_null($targetProblem->problem_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        self::validateProblemAccess(
            $r->identity,
            $targetProblem,
            $contestAlias,
            $problemsetId
        );

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
     * @omegaup-request-param null|string $contest_alias
     * @omegaup-request-param string $problem_alias
     * @omegaup-request-param int|null $problemset_id
     *
     * @return array{bookmarked: bool}
     */
    public static function apiExists(\OmegaUp\Request $r): array {
        $r->ensureIdentity();
        $problemAlias = $r->ensureString(
            'problem_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $contestAlias = $r->ensureOptionalString(
            'contest_alias',
            required: false,
            validator: fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $problemsetId = $r->ensureOptionalInt('problemset_id');

        // Validate that the problem exists
        $targetProblem = \OmegaUp\DAO\Problems::getByAlias($problemAlias);
        if (is_null($targetProblem) || is_null($targetProblem->problem_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        self::validateProblemAccess(
            $r->identity,
            $targetProblem,
            $contestAlias,
            $problemsetId
        );

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

    /**
     * Validates if the identity has access to view/bookmark the target problem.
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     */
    public static function validateProblemAccess(
        \OmegaUp\DAO\VO\Identities $identity,
        \OmegaUp\DAO\VO\Problems $problem,
        ?string $contestAlias = null,
        ?int $problemsetId = null
    ): void {
        if (\OmegaUp\DAO\Problems::isVisible($problem)) {
            return;
        }

        if (\OmegaUp\Authorization::canEditProblem($identity, $problem)) {
            return;
        }

        if (!empty($contestAlias) || !is_null($problemsetId)) {
            \OmegaUp\Controllers\Problem::validateDetails(
                $identity,
                $contestAlias,
                strval($problem->alias),
                statementType: '',
                problemsetId: $problemsetId
            );
            return;
        }

        if (self::hasAccessViaAnyProblemset($identity, $problem)) {
            return;
        }

        throw new \OmegaUp\Exceptions\ForbiddenAccessException(
            'problemIsPrivate'
        );
    }

    private static function hasAccessViaAnyProblemset(
        \OmegaUp\DAO\VO\Identities $identity,
        \OmegaUp\DAO\VO\Problems $problem
    ): bool {
        if (is_null($problem->problem_id)) {
            return false;
        }
        $sql = 'SELECT problemset_id FROM Problemset_Problems WHERE problem_id = ?;';
        /** @var list<array{problemset_id: int}> */
        $problemsets = \OmegaUp\MySQLConnection::getInstance()->GetAll(
            $sql,
            [$problem->problem_id]
        );
        foreach ($problemsets as $ps) {
            try {
                \OmegaUp\Controllers\Problem::validateDetails(
                    $identity,
                    contestAlias: null,
                    problemAlias: strval($problem->alias),
                    statementType: '',
                    problemsetId: intval($ps['problemset_id'])
                );
                return true;
            } catch (\OmegaUp\Exceptions\ForbiddenAccessException | \OmegaUp\Exceptions\NotFoundException $e) {
                continue;
            }
        }
        return false;
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
