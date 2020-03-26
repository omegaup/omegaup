<?php

 namespace OmegaUp\Controllers;

/**
 * ContestController
 *
 */
class Contest extends \OmegaUp\Controllers\Controller {
    const SHOW_INTRO = true;
    const MAX_CONTEST_LENGTH_SECONDS = 2678400; // 31 days

    /**
     * Returns a list of contests
     *
     * @return array{number_of_results: int, results: list<array{admission_mode: string, alias: string, contest_id: int, description: string, finish_time: int, last_updated: int, original_finish_time: string, problemset_id: int, recommended: bool, rerun_id: int, start_time: int, title: string, window_length: int|null}>}
     */
    public static function apiList(\OmegaUp\Request $r): array {
        // Check who is visiting, but a not logged user can still view
        // the list of contests
        try {
            $r->ensureIdentity();
        } catch (\OmegaUp\Exceptions\UnauthorizedException $e) {
            // Do nothing.
            /** @var null $r->identity */
        }

        /** @var list<array{admission_mode: string, alias: string, contest_id: int, description: string, finish_time: int, last_updated: int, original_finish_time: string, problemset_id: int, recommended: bool, rerun_id: int, start_time: int, title: string, window_length: int|null}> */
        $contests = [];
        $r->ensureInt('page', null, null, false);
        $r->ensureInt('page_size', null, null, false);
        \OmegaUp\Validators::validateOptionalNumber($r['active'], 'active');
        \OmegaUp\Validators::validateOptionalNumber(
            $r['recommended'],
            'recommended'
        );
        \OmegaUp\Validators::validateOptionalNumber(
            $r['participating'],
            'participating'
        );

        $page = (isset($r['page']) ? intval($r['page']) : 1);
        $pageSize = (isset($r['page_size']) ? intval($r['page_size']) : 20);
        $activeContests = isset($r['active'])
            ? \OmegaUp\DAO\Enum\ActiveStatus::getIntValue(intval($r['active']))
            : \OmegaUp\DAO\Enum\ActiveStatus::ALL;
        // If the parameter was not set, the default should be ALL which is
        // a number and should pass this check.
        \OmegaUp\Validators::validateNumber($activeContests, 'active');
        $recommended = isset($r['recommended'])
            ? \OmegaUp\DAO\Enum\RecommendedStatus::getIntValue(
                intval($r['recommended'])
            )
            : \OmegaUp\DAO\Enum\RecommendedStatus::ALL;
        // Same as above.
        \OmegaUp\Validators::validateNumber($recommended, 'recommended');
        $participating = isset($r['participating'])
            ? \OmegaUp\DAO\Enum\ParticipatingStatus::getIntValue(
                intval($r['participating'])
            )
            : \OmegaUp\DAO\Enum\ParticipatingStatus::NO;
        \OmegaUp\Validators::validateOptionalInEnum(
            $r['admission_mode'],
            'admission_mode',
            [
                'public',
                'private',
                'registration',
            ]
        );

        // admission mode status in contest is public
        $public = (
            isset($r['admission_mode']) &&
            self::isPublic(strval($r['admission_mode']))
        );

        if (is_null($participating)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterInvalid',
                'participating'
            );
        }
        \OmegaUp\Validators::validateStringOfLengthInRange(
            $r['query'],
            'query',
            null,
            255,
            false /* not required */
        );
        $query = $r['query'];

        $contests = self::getContestList(
            $r->identity,
            $query,
            $page,
            $pageSize,
            $activeContests,
            $recommended,
            $public,
            $participating
        );

        return [
            'number_of_results' => count($contests),
            'results' => $contests,
        ];
    }

    /**
     * @return list<array{admission_mode: string, alias: string, contest_id: int, description: string, finish_time: int, last_updated: int, original_finish_time: string, problemset_id: int, recommended: bool, rerun_id: int, start_time: int, title: string, window_length: int|null}>
     */
    public static function getContestList(
        ?\OmegaUp\DAO\VO\Identities $identity,
        ?string $query,
        int $page,
        int $pageSize,
        int $activeContests,
        int $recommended,
        bool $public = false,
        ?int $participating = null
    ) {
        $cacheKey = "{$activeContests}-{$recommended}-{$page}-{$pageSize}";
        if (is_null($identity) || is_null($identity->identity_id)) {
            // Get all public contests
            $callback = /**
             * @return list<array{admission_mode: string, alias: string, contest_id: int, description: string, finish_time: int, last_updated: int, original_finish_time: string, problemset_id: int, recommended: bool, rerun_id: int, start_time: int, title: string, window_length: int|null}>
             */
            function () use (
                $page,
                $pageSize,
                $activeContests,
                $recommended,
                $query
            ): array {
                return \OmegaUp\DAO\Contests::getAllPublicContests(
                    $page,
                    $pageSize,
                    $activeContests,
                    $recommended,
                    $query
                );
            };
            if (empty($query)) {
                $contests = \OmegaUp\Cache::getFromCacheOrSet(
                    \OmegaUp\Cache::CONTESTS_LIST_PUBLIC,
                    $cacheKey,
                    $callback
                );
            } else {
                $contests = $callback();
            }
        } elseif ($participating === \OmegaUp\DAO\Enum\ParticipatingStatus::YES) {
            $contests = \OmegaUp\DAO\Contests::getContestsParticipating(
                $identity->identity_id,
                $page,
                $pageSize,
                $activeContests,
                $query
            );
        } elseif ($public) {
            $contests = \OmegaUp\DAO\Contests::getRecentPublicContests(
                $identity->identity_id,
                $page,
                $pageSize,
                $query
            );
        } elseif (\OmegaUp\Authorization::isSystemAdmin($identity)) {
            // Get all contests
            $callback = /**
             * @return list<array{admission_mode: string, alias: string, contest_id: int, description: string, finish_time: int, last_updated: int, original_finish_time: string, problemset_id: int, recommended: bool, rerun_id: int, start_time: int, title: string, window_length: int|null}>
             */
            function () use (
                $page,
                $pageSize,
                $activeContests,
                $recommended,
                $query
            ): array {
                return \OmegaUp\DAO\Contests::getAllContests(
                    $page,
                    $pageSize,
                    $activeContests,
                    $recommended,
                    $query
                );
            };
            if (empty($query)) {
                $contests = \OmegaUp\Cache::getFromCacheOrSet(
                    \OmegaUp\Cache::CONTESTS_LIST_SYSTEM_ADMIN,
                    $cacheKey,
                    $callback
                );
            } else {
                $contests = $callback();
            }
        } else {
            // Get all public+private contests
            $contests = \OmegaUp\DAO\Contests::getAllContestsForIdentity(
                $identity->identity_id,
                $page,
                $pageSize,
                $activeContests,
                $recommended,
                $query
            );
        }
        $addedContests = [];
        foreach ($contests as $contestInfo) {
            $contestInfo['duration'] = (is_null($contestInfo['window_length']) ?
                $contestInfo['finish_time'] - $contestInfo['start_time'] :
                ($contestInfo['window_length'] * 60)
            );

            $addedContests[] = $contestInfo;
        }
        return $addedContests;
    }

    /**
     * Returns a list of contests where current user has admin rights (or is
     * the director).
     *
     * @return array{contests: list<array{admission_mode: string, alias: string, finish_time: int, rerun_id: int, scoreboard_url: string, scoreboard_url_admin: string, start_time: int, title: string}>}
     */
    public static function apiAdminList(\OmegaUp\Request $r): array {
        $r->ensureIdentity();

        $r->ensureInt('page', null, null, false);
        $r->ensureInt('page_size', null, null, false);

        $page = (isset($r['page']) ? intval($r['page']) : 1);
        $pageSize = (isset($r['page_size']) ? intval($r['page_size']) : 1000);

        // Create array of relevant columns
        $contests = null;
        if (\OmegaUp\Authorization::isSystemAdmin($r->identity)) {
            $contests = \OmegaUp\DAO\Contests::getAllContestsWithScoreboard(
                $page,
                $pageSize,
                'contest_id',
                'DESC'
            );
        } else {
            $contests = \OmegaUp\DAO\Contests::getAllContestsAdminedByIdentity(
                $r->identity->identity_id,
                $page,
                $pageSize
            );
        }

        return [
            'contests' => $contests,
        ];
    }

    /**
     * Callback to get contests list, depending on a given method
     *
     * @param \OmegaUp\Request $r
     * @param Closure(int, int, int, null|string):list<array{acl_id?: int, admission_mode: string, alias: string, contest_id: int, description: string, feedback?: string, finish_time: int, languages?: null|string, last_updated: int, original_finish_time?: string, partial_score?: int, penalty?: int, penalty_calc_policy?: string, penalty_type?: string, points_decay_factor?: float, problemset_id: int, recommended: bool, rerun_id: int, scoreboard?: int, scoreboard_url: string, scoreboard_url_admin: string, show_scoreboard_after?: int, start_time: int, submissions_gap?: int, title: string, urgent?: int, window_length: int|null}> $callbackUserFunction
     *
     * @return array{contests: list<array{acl_id?: int, admission_mode: string, alias: string, contest_id: int, description: string, feedback?: string, finish_time: int, languages?: null|string, last_updated: int, original_finish_time?: string, partial_score?: int, penalty?: int, penalty_calc_policy?: string, penalty_type?: string, points_decay_factor?: float, problemset_id: int, recommended: bool, rerun_id: int, scoreboard?: int, scoreboard_url: string, scoreboard_url_admin: string, show_scoreboard_after?: int, start_time: int, submissions_gap?: int, title: string, urgent?: int, window_length: int|null}>}
     */
    private static function getContestListInternal(
        \OmegaUp\Request $r,
        $callbackUserFunction
    ): array {
        $r->ensureIdentity();
        $r->ensureInt('page', null, null, false);
        $r->ensureInt('page_size', null, null, false);

        $page = (isset($r['page']) ? intval($r['page']) : 1);
        $pageSize = (isset($r['page_size']) ? intval($r['page_size']) : 1000);
        $query = is_null($r['query']) ? null : strval($r['query']);
        $contests = $callbackUserFunction(
            $r->identity->identity_id,
            $page,
            $pageSize,
            $query
        );

        // Expire contest-list cache
        \OmegaUp\Cache::invalidateAllKeys(\OmegaUp\Cache::CONTESTS_LIST_PUBLIC);
        \OmegaUp\Cache::invalidateAllKeys(
            \OmegaUp\Cache::CONTESTS_LIST_SYSTEM_ADMIN
        );

        return [
            'contests' => $contests,
        ];
    }

    /**
     * Returns a list of contests where current user is the director
     *
     * @return array{contests: list<array{acl_id?: int, admission_mode: string, alias: string, contest_id: int, description: string, feedback?: string, finish_time: int, languages?: null|string, last_updated: int, original_finish_time?: string, partial_score?: int, penalty?: int, penalty_calc_policy?: string, penalty_type?: string, points_decay_factor?: float, problemset_id: int, recommended: bool, rerun_id: int, scoreboard?: int, scoreboard_url: string, scoreboard_url_admin: string, show_scoreboard_after?: int, start_time: int, submissions_gap?: int, title: string, urgent?: int, window_length: int|null}>}
     */
    public static function apiMyList(\OmegaUp\Request $r): array {
        $r->ensureMainUserIdentity();

        return self::getContestListInternal(
            $r,
            function (
                int $identityId,
                int $page,
                int $pageSize,
                ?string $query
            ) {
                return \OmegaUp\DAO\Contests::getAllContestsOwnedByUser(
                    $identityId,
                    $page,
                    $pageSize
                );
            }
        );
    }

    /**
     * Returns a list of contests where current user is participating in
     *
     * @return array{contests: list<array{acl_id?: int, admission_mode: string, alias: string, contest_id: int, description: string, feedback?: string, finish_time: int, languages?: null|string, last_updated: int, original_finish_time?: string, partial_score?: int, penalty?: int, penalty_calc_policy?: string, penalty_type?: string, points_decay_factor?: float, problemset_id: int, recommended: bool, rerun_id: int, scoreboard?: int, scoreboard_url: string, scoreboard_url_admin: string, show_scoreboard_after?: int, start_time: int, submissions_gap?: int, title: string, urgent?: int, window_length: int|null}>}
     */
    public static function apiListParticipating(\OmegaUp\Request $r): array {
        $r->ensureIdentity();
        return self::getContestListInternal(
            $r,
            function (
                int $identityId,
                int $page,
                int $pageSize,
                ?string $query
            ) {
                return \OmegaUp\DAO\Contests::getContestsParticipating(
                    $identityId,
                    $page,
                    $pageSize,
                    \OmegaUp\DAO\Enum\ActiveStatus::ALL,
                    $query
                );
            }
        );
    }

    /**
     * Checks if user can access contests: If the contest is private then the user
     * must be added to the contest (an entry ProblemsetIdentities must exists) OR the user
     * should be a Contest Admin.
     *
     * Expects $contest to contain the contest to check against.
     *
     * In case of access check failed, an exception is thrown.
     *
     * @throws \OmegaUp\Exceptions\ApiException
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     */
    private static function canAccessContest(
        \OmegaUp\DAO\VO\Contests $contest,
        \OmegaUp\DAO\VO\Identities $identity
    ): void {
        if (is_null($contest->problemset_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('contestNotFound');
        }
        if ($contest->admission_mode === 'private') {
            if (
                !is_null(\OmegaUp\DAO\ProblemsetIdentities::getByPK(
                    $identity->identity_id,
                    $contest->problemset_id
                ))
            ) {
                return;
            }
            if (
                \OmegaUp\Authorization::canSubmitToProblemset(
                    $identity,
                    \OmegaUp\DAO\Problemsets::getByPK(
                        $contest->problemset_id
                    )
                )
            ) {
                return;
            }
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'userNotAllowed'
            );
        } elseif (
            $contest->admission_mode === 'registration' &&
            !\OmegaUp\Authorization::isContestAdmin($identity, $contest)
        ) {
            $req = \OmegaUp\DAO\ProblemsetIdentityRequest::getByPK(
                $identity->identity_id,
                $contest->problemset_id
            );
            if (is_null($req) || !$req->accepted) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                    'contestNotRegistered'
                );
            }
        }
    }

    /**
     * Validate the basics of a contest request.
     *
     * @throws \OmegaUp\Exceptions\NotFoundException
     *
     * @return array{contest: \OmegaUp\DAO\VO\Contests, problemset: \OmegaUp\DAO\VO\Problemsets}
     */
    private static function validateBasicDetails(string $contestAlias): array {
        // If the contest is private, verify that our user is invited
        $contestProblemset = \OmegaUp\DAO\Contests::getByAliasWithExtraInformation(
            $contestAlias
        );
        if (is_null($contestProblemset)) {
            throw new \OmegaUp\Exceptions\NotFoundException('contestNotFound');
        }
        return [
            'contest' => new \OmegaUp\DAO\VO\Contests(
                array_intersect_key(
                    $contestProblemset,
                    \OmegaUp\DAO\VO\Contests::FIELD_NAMES
                )
            ),
            'problemset' => new \OmegaUp\DAO\VO\Problemsets(
                array_intersect_key(
                    $contestProblemset,
                    \OmegaUp\DAO\VO\Problemsets::FIELD_NAMES
                )
            ),
        ];
    }

    /**
     * Validate a contest with contest alias
     *
     * @return \OmegaUp\DAO\VO\Contests $contest
     * @throws \OmegaUp\Exceptions\NotFoundException
     */
    public static function validateContest(
        string $contestAlias
    ): \OmegaUp\DAO\VO\Contests {
        $contest = \OmegaUp\DAO\Contests::getByAlias($contestAlias);
        if (is_null($contest)) {
            throw new \OmegaUp\Exceptions\NotFoundException('contestNotFound');
        }
        return $contest;
    }

    /**
     * Validate if a contestant has explicit access to a contest.
     */
    private static function isInvitedToContest(
        \OmegaUp\DAO\VO\Contests $contest,
        \OmegaUp\DAO\VO\Identities $identity
    ): bool {
        if (is_null($identity->user_id)) {
            return false;
        }
        return self::isPublic($contest->admission_mode) ||
            !is_null(\OmegaUp\DAO\ProblemsetIdentities::getByPK(
                $identity->identity_id,
                $contest->problemset_id
            ));
    }

    /**
     * Get all the properties for smarty.
     *
     * @return array{inContest?: bool, smartyProperties: array{needsBasicInformation?: bool, requestsUserInformation?: false, privacyStatement?: array{markdown: string, statementType: string, gitObjectId?: string}, payload?: array{shouldShowFirstAssociatedIdentityRunWarning: bool}}, template: string}
     */
    public static function getContestDetailsForSmarty(
        \OmegaUp\Request $r
    ): array {
        $r->ensureBool('is_practice', false);

        \OmegaUp\Validators::validateStringNonEmpty(
            $r['contest_alias'],
            'contest_alias'
        );
        $contest = \OmegaUp\Controllers\Contest::validateContest(
            $r['contest_alias']
        );
        if (is_null($contest->problemset_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('contestNotFound');
        }

        try {
            $r->ensureIdentity();
        } catch (\OmegaUp\Exceptions\UnauthorizedException $e) {
            if ($contest->admission_mode === 'private') {
                throw $e;
            }
            // Request can proceed unauthenticated.
        }

        $isPractice = isset($r['is_practice']) && $r['is_practice'] === true;

        $shouldShowIntro = !$isPractice && \OmegaUp\Controllers\Contest::shouldShowIntro(
            $r->identity,
            $contest
        );

        // Half-authenticate, in case there is no session in place.
        $session = \OmegaUp\Controllers\Session::getCurrentSession($r);
        if (!$shouldShowIntro) {
            return [
                'smartyProperties' => [
                    'payload' => [
                        'shouldShowFirstAssociatedIdentityRunWarning' =>
                            !is_null($session['identity']) &&
                            !is_null($session['user']) &&
                            !\OmegaUp\Controllers\User::isMainIdentity(
                                $session['user'],
                                $session['identity']
                            ) &&
                            \OmegaUp\DAO\Problemsets::shouldShowFirstAssociatedIdentityRunWarning(
                                $session['user']
                            ),
                    ],
                ],
                'template' => $isPractice ?
                    'arena.contest.practice.tpl' :
                    'arena.contest.contestant.tpl',
                'inContest' => !$isPractice,
            ];
        }
        $result = [
            'needsBasicInformation' => false,
            'requestsUserInformation' => false,
        ];
        if (is_null($session['identity'])) {
            // No session, show the intro if public, so that they can login.

            return [
                'smartyProperties' => $result,
                'template' => 'arena.contest.intro.tpl',
            ];
        }

        [
            'needsBasicInformation' => $result['needsBasicInformation'],
            'requestsUserInformation' => $result['requestsUserInformation'],
        ] = \OmegaUp\DAO\Contests::getNeedsInformation($contest->problemset_id);
        $identity = $session['identity'];

        $result['needsBasicInformation'] =
            $result['needsBasicInformation'] && (
                !$identity->country_id || !$identity->state_id ||
                is_null($identity->current_identity_school_id)
        );

        // Privacy Statement Information
        $privacyStatementMarkdown = \OmegaUp\PrivacyStatement::getForProblemset(
            $identity->language_id,
            'contest',
            $result['requestsUserInformation']
        );
        if (!is_null($privacyStatementMarkdown)) {
            $statementType =
                "contest_{$result['requestsUserInformation']}_consent";
            $result['privacyStatement'] = [
                'markdown' => $privacyStatementMarkdown,
                'statementType' => $statementType
            ];
            $statement = \OmegaUp\DAO\PrivacyStatements::getLatestPublishedStatement(
                $statementType
            );
            if (!is_null($statement)) {
                $result['privacyStatement']['gitObjectId'] = $statement['git_object_id'];
            }
        }

        return [
            'smartyProperties' => $result,
            'template' => 'arena.contest.intro.tpl',
        ];
    }

    /**
     * @return array{smartyProperties: array{payload: array{contests: array{current: list<array{admission_mode: string, alias: string, contest_id: int, description: string, finish_time: int, last_updated: int, original_finish_time: string, problemset_id: int, recommended: bool, rerun_id: int, start_time: int, title: string, window_length: int|null}>, future: list<array{admission_mode: string, alias: string, contest_id: int, description: string, finish_time: int, last_updated: int, original_finish_time: string, problemset_id: int, recommended: bool, rerun_id: int, start_time: int, title: string, window_length: int|null}>, participating?: list<array{admission_mode: string, alias: string, contest_id: int, description: string, finish_time: int, last_updated: int, original_finish_time: string, problemset_id: int, recommended: bool, rerun_id: int, start_time: int, title: string, window_length: int|null}>, past: list<array{admission_mode: string, alias: string, contest_id: int, description: string, finish_time: int, last_updated: int, original_finish_time: string, problemset_id: int, recommended: bool, rerun_id: int, start_time: int, title: string, window_length: int|null}>, public: list<array{admission_mode: string, alias: string, contest_id: int, description: string, finish_time: int, last_updated: int, original_finish_time: string, problemset_id: int, recommended: bool, rerun_id: int, start_time: int, title: string, window_length: int|null}>, recommended_current: list<array{admission_mode: string, alias: string, contest_id: int, description: string, finish_time: int, last_updated: int, original_finish_time: string, problemset_id: int, recommended: bool, rerun_id: int, start_time: int, title: string, window_length: int|null}>, recommended_past: list<array{admission_mode: string, alias: string, contest_id: int, description: string, finish_time: int, last_updated: int, original_finish_time: string, problemset_id: int, recommended: bool, rerun_id: int, start_time: int, title: string, window_length: int|null}>}, isLogged: bool, query: string}}, template: string}
     */
    public static function getContestListDetailsForSmarty(
        \OmegaUp\Request $r
    ) {
        try {
            $r->ensureIdentity();
        } catch (\OmegaUp\Exceptions\UnauthorizedException $e) {
            // Do nothing.
            $r->identity = null;
        }
        $r->ensureInt('page', null, null, false);
        $r->ensureInt('page_size', null, null, false);

        $page = (isset($r['page']) ? intval($r['page']) : 1);
        $pageSize = (isset($r['page_size']) ? intval($r['page_size']) : 1000);
        \OmegaUp\Validators::validateStringOfLengthInRange(
            $r['query'],
            'query',
            /*$minLength=*/ 0,
            /*$maxLength=*/ 256,
            /*$required=*/ false
        );
        $contests = [];
        if (!is_null($r->identity)) {
            $contests['participating'] = self::getContestList(
                $r->identity,
                $r['query'],
                $page,
                $pageSize,
                \OmegaUp\DAO\Enum\ActiveStatus::ALL,
                \OmegaUp\DAO\Enum\RecommendedStatus::ALL,
                /*$public=*/ false,
                \OmegaUp\DAO\Enum\ParticipatingStatus::YES
            );
        }
        $contests['recommended_current'] = self::getContestList(
            $r->identity,
            $r['query'],
            $page,
            $pageSize,
            \OmegaUp\DAO\Enum\ActiveStatus::ACTIVE,
            \OmegaUp\DAO\Enum\RecommendedStatus::RECOMMENDED
        );
        $contests['current'] = self::getContestList(
            $r->identity,
            $r['query'],
            $page,
            $pageSize,
            \OmegaUp\DAO\Enum\ActiveStatus::ACTIVE,
            \OmegaUp\DAO\Enum\RecommendedStatus::NOT_RECOMMENDED
        );
        $contests['public'] = self::getContestList(
            $r->identity,
            $r['query'],
            $page,
            $pageSize,
            \OmegaUp\DAO\Enum\ActiveStatus::ACTIVE,
            \OmegaUp\DAO\Enum\RecommendedStatus::NOT_RECOMMENDED
        );
        $contests['future'] = self::getContestList(
            $r->identity,
            $r['query'],
            $page,
            $pageSize,
            \OmegaUp\DAO\Enum\ActiveStatus::FUTURE,
            \OmegaUp\DAO\Enum\RecommendedStatus::NOT_RECOMMENDED
        );
        $contests['recommended_past'] = self::getContestList(
            $r->identity,
            $r['query'],
            $page,
            $pageSize,
            \OmegaUp\DAO\Enum\ActiveStatus::PAST,
            \OmegaUp\DAO\Enum\RecommendedStatus::RECOMMENDED
        );
        $contests['past'] = self::getContestList(
            $r->identity,
            $r['query'],
            $page,
            $pageSize,
            \OmegaUp\DAO\Enum\ActiveStatus::PAST,
            \OmegaUp\DAO\Enum\RecommendedStatus::NOT_RECOMMENDED
        );

        return [
            'smartyProperties' => [
                'payload' => [
                    'query' => $r['query'],
                    'isLogged' => !is_null($r->identity),
                    'contests' => $contests,
                ],
            ],
            'template' => 'arena.index.tpl',
        ];
    }

    /**
     * @return array{smartyProperties: array{payload: array{contests: list<array{contest_id: int, problemset_id: int, acl_id?: int, title: string, description: string, original_finish_time?: string, start_time: int|null, finish_time: int|null, last_updated: int|null, window_length: null|int, rerun_id: int, admission_mode: string, alias: string, scoreboard?: int, points_decay_factor?: float, partial_score?: int, submissions_gap?: int, feedback?: string, penalty?: int, penalty_type?: string, penalty_calc_policy?: string, show_scoreboard_after?: int, urgent?: int, languages?: null|string, recommended: bool, scoreboard_url: string, scoreboard_url_admin: string}>}, privateContestsAlert: bool}, template: string}
     */
    public static function getContestListMineForSmarty(
        \OmegaUp\Request $r
    ): array {
        // Authenticate user
        $r->ensureMainUserIdentity();

        // If the user have private material (contests/problems), an alert is issued
        // suggesting to contribute to the community by releasing the material to
        // the public. This flag ensures that this alert is shown only once per
        // session, the first time the user visits the "My contests" page.
        $scopedSession = \OmegaUp\Controllers\Session::getSessionManagerInstance()->sessionStart();
        $privateContestsAlert = (
            !isset($_SESSION['private_contests_alert']) &&
            \OmegaUp\DAO\Contests::getPrivateContestsCount($r->user) > 0
        );
        if ($privateContestsAlert) {
            $_SESSION['private_contests_alert'] = true;
        }
        unset($scopedSession);

        return [
            'smartyProperties' => [
                'payload' => self::getContestListInternal(
                    $r,
                    function (
                        int $identityId,
                        int $page,
                        int $pageSize,
                        ?string $query
                    ) {
                        return \OmegaUp\DAO\Contests::getAllContestsOwnedByUser(
                            $identityId,
                            $page,
                            $pageSize
                        );
                    }
                ),
                'privateContestsAlert' => $privateContestsAlert,
            ],
            'template' => 'contest.mine.tpl',
        ];
    }

    /**
     * @return array{smartyProperties: array{LANGUAGES: list<string>, IS_UPDATE: bool}, template: string}
     */
    public static function getContestNewForSmarty(
        \OmegaUp\Request $r
    ): array {
        $r->ensureMainUserIdentity();
        return [
            'smartyProperties' => [
                'LANGUAGES' => array_keys(
                    \OmegaUp\Controllers\Run::SUPPORTED_LANGUAGES
                ),
                'IS_UPDATE' => false,
            ],
            'template' => 'contest.new.tpl',
        ];
    }

    /**
     * @return array{smartyProperties: array{LANGUAGES: list<string>, IS_UPDATE: bool}, template: string}
     */
    public static function getContestEditForSmarty(
        \OmegaUp\Request $r
    ): array {
        $r->ensureMainUserIdentity();
        return [
            'smartyProperties' => [
                'LANGUAGES' => array_keys(
                    \OmegaUp\Controllers\Run::SUPPORTED_LANGUAGES
                ),
                'IS_UPDATE' => true,
            ],
            'template' => 'contest.edit.tpl',
        ];
    }

    /**
     * Show the contest intro unless you are admin, or you already started this
     * contest.
     */
    public static function shouldShowIntro(
        ?\OmegaUp\DAO\VO\Identities $identity,
        \OmegaUp\DAO\VO\Contests $contest
    ): bool {
        try {
            if (is_null($identity)) {
                // No session, show the intro (if public), so that they can login.
                return self::isPublic($contest->admission_mode);
            }
            self::canAccessContest($contest, $identity);
        } catch (\Exception $e) {
            // Could not access contest. Private contests must not be leaked, so
            // unless they were manually added beforehand, show them a 404 error.
            if (
                is_null($identity) ||
                !self::isInvitedToContest($contest, $identity)
            ) {
                throw $e;
            }
            self::$log->error('Exception while trying to verify access: ' . $e);
            return \OmegaUp\Controllers\Contest::SHOW_INTRO;
        }

        // You already started the contest.
        $contestOpened = \OmegaUp\DAO\ProblemsetIdentities::getByPK(
            $identity->identity_id,
            $contest->problemset_id
        );
        if (!is_null($contestOpened) && !is_null($contestOpened->access_time)) {
            self::$log->debug(
                'No intro because you already started the contest'
            );
            return !\OmegaUp\Controllers\Contest::SHOW_INTRO;
        }
        return \OmegaUp\Controllers\Contest::SHOW_INTRO;
    }

    /**
     * Validate request of a details contest
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     * @throws \OmegaUp\Exceptions\PreconditionFailedException
     *
     * @return array{contest: \OmegaUp\DAO\VO\Contests, contest_admin: bool, contest_alias: string}
     */
    public static function validateDetails(\OmegaUp\Request $r): array {
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['contest_alias'],
            'contest_alias'
        );
        [
            'contest' => $contest,
            'problemset' => $problemset
        ] = self::validateBasicDetails(
            $r['contest_alias']
        );

        $contestAdmin = false;
        $contestAlias = '';

        // If the contest has not started, user should not see it, unless it i
        // admin or has a token.
        if (is_null($r['token'])) {
            // Crack the request to get the current user
            $r->ensureIdentity();
            self::canAccessContest($contest, $r->identity);

            $contestAdmin = \OmegaUp\Authorization::isContestAdmin(
                $r->identity,
                $contest
            );
            if (
                !\OmegaUp\DAO\Contests::hasStarted($contest) &&
                !$contestAdmin
            ) {
                $exception = new \OmegaUp\Exceptions\PreconditionFailedException(
                    'contestNotStarted'
                );
                $exception->addCustomMessageToArray(
                    'start_time',
                    $contest->start_time
                );

                throw $exception;
            }
        } else {
            if ($r['token'] === $problemset->scoreboard_url_admin) {
                $contestAdmin = true;
                /** @var string */
                $contestAlias = $contest->alias;
            } elseif ($r['token'] !== $problemset->scoreboard_url) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                    'invalidScoreboardUrl'
                );
            }
        }
        return [
            'contest' => $contest,
            'contest_admin' => $contestAdmin,
            'contest_alias' => $contestAlias,
        ];
    }

    /**
     * @return array{admission_mode: string, alias: string, description: string, feedback: string, finish_time: int, languages: string, partial_score: bool, penalty: int, penalty_calc_policy: string, penalty_type: string, points_decay_factor: float, problemset_id: int, rerun_id: int, scoreboard: int, show_scoreboard_after: bool, start_time: int, submissions_gap: int, title: string, window_length: int|null, user_registration_requested?: bool, user_registration_answered?: bool, user_registration_accepted?: bool|null}
     */
    public static function apiPublicDetails(\OmegaUp\Request $r): array {
        try {
            $r->ensureIdentity();
        } catch (\OmegaUp\Exceptions\UnauthorizedException $e) {
            // Do nothing.
            $r->identity = null;
        }
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['contest_alias'],
            'contest_alias'
        );

        $contest = \OmegaUp\Controllers\Contest::validateContest(
            $r['contest_alias']
        );

        // Initialize response to be the contest information
        /** @var array{admission_mode: string, alias: string, description: string, feedback: string, finish_time: int, languages: string, partial_score: bool, penalty: int, penalty_calc_policy: string, penalty_type: string, points_decay_factor: float, problemset_id: int, rerun_id: int, scoreboard: int, show_scoreboard_after: bool, start_time: int, submissions_gap: int, title: string, window_length: int|null} */
        $result = $contest->asFilteredArray([
            'admission_mode',
            'alias',
            'description',
            'feedback',
            'finish_time',
            'languages',
            'partial_score',
            'penalty',
            'penalty_calc_policy',
            'penalty_type',
            'points_decay_factor',
            'problemset_id',
            'rerun_id',
            'scoreboard',
            'show_scoreboard_after',
            'start_time',
            'submissions_gap',
            'time_start',
            'title',
            'window_length',
        ]);

        // Whether the contest is private, verify that our user is invited
        if (
            !is_null($r->identity) &&
            $result['admission_mode'] === 'registration'
        ) {
            $registration = \OmegaUp\DAO\ProblemsetIdentityRequest::getByPK(
                $r->identity->identity_id,
                $contest->problemset_id
            );

            $result['user_registration_requested'] = !is_null($registration);

            if (is_null($registration)) {
                $result['user_registration_answered'] = false;
            } else {
                $result['user_registration_answered'] = !is_null(
                    $registration->accepted
                );
                $result['user_registration_accepted'] = $registration->accepted;
            }
        }

        $result['start_time'] = intval(
            \OmegaUp\DAO\DAO::fromMySQLTimestamp($result['start_time'])
        );
        $result['finish_time'] = intval(
            \OmegaUp\DAO\DAO::fromMySQLTimestamp($result['finish_time'])
        );

        return $result;
    }

    /**
     * @return array{status: string}
     */
    public static function apiRegisterForContest(\OmegaUp\Request $r): array {
        // Authenticate request
        $r->ensureIdentity();

        \OmegaUp\Validators::validateStringNonEmpty(
            $r['contest_alias'],
            'contest_alias'
        );
        $contest = self::validateContest($r['contest_alias']);

        \OmegaUp\DAO\ProblemsetIdentityRequest::create(new \OmegaUp\DAO\VO\ProblemsetIdentityRequest([
            'identity_id' => $r->identity->identity_id,
            'problemset_id' => $contest->problemset_id,
            'request_time' => \OmegaUp\Time::get(),
        ]));

        return ['status' => 'ok'];
    }

    /**
     * Joins a contest - explicitly adds a identity to a contest.
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return array{status: string}
     */
    public static function apiOpen(\OmegaUp\Request $r): array {
        // Authenticate request
        $r->ensureIdentity();

        $response = self::validateDetails($r);
        if (is_null($response['contest']->problemset_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('contestNotFound');
        }
        [
            'needsBasicInformation' => $needsInformation,
            'requestsUserInformation' => $requestsUserInformation
        ] = \OmegaUp\DAO\Contests::getNeedsInformation(
            $response['contest']->problemset_id
        );

        if (
            $needsInformation
              && (is_null($r->identity->country_id)
                  || is_null($r->identity->state_id)
                  || is_null($r->identity->current_identity_school_id))
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'contestBasicInformationNeeded'
            );
        }

        $r->ensureBool('share_user_information', false);
        \OmegaUp\DAO\DAO::transBegin();
        try {
            \OmegaUp\DAO\ProblemsetIdentities::checkAndSaveFirstTimeAccess(
                $r->identity,
                $response['contest'],
                /*$grantAccess=*/true,
                boolval($r['share_user_information'])
            );

            // Insert into PrivacyStatement_Consent_Log whether request
            // user info is optional or required
            if ($requestsUserInformation !== 'no') {
                \OmegaUp\Validators::validateStringNonEmpty(
                    $r['privacy_git_object_id'],
                    'privacy_git_object_id'
                );
                \OmegaUp\Validators::validateStringNonEmpty(
                    $r['statement_type'],
                    'statement_type'
                );
                $privacyStatementId = \OmegaUp\DAO\PrivacyStatements::getId(
                    $r['privacy_git_object_id'],
                    $r['statement_type']
                );

                if (is_null($privacyStatementId)) {
                    throw new \OmegaUp\Exceptions\NotFoundException(
                        'privacyStatementNotFound'
                    );
                }
                $privacyStatementConsentId = \OmegaUp\DAO\PrivacyStatementConsentLog::getId(
                    $r->identity->identity_id,
                    $privacyStatementId
                );
                if (is_null($privacyStatementConsentId)) {
                    $privacyStatementConsentId = \OmegaUp\DAO\PrivacyStatementConsentLog::saveLog(
                        $r->identity->identity_id,
                        $privacyStatementId
                    );
                }

                \OmegaUp\DAO\ProblemsetIdentities::updatePrivacyStatementConsent(new \OmegaUp\DAO\VO\ProblemsetIdentities([
                    'identity_id' => $r->identity->identity_id,
                    'problemset_id' => $response['contest']->problemset_id,
                    'privacystatement_consent_id' => $privacyStatementConsentId,
                ]));
            }

            \OmegaUp\DAO\DAO::transEnd();
        } catch (\Exception $e) {
            \OmegaUp\DAO\DAO::transRollback();
            throw $e;
        }

        self::$log->info(
            "User '{$r->identity->username}' joined contest '{$response['contest']->alias}'"
        );
        return ['status' => 'ok'];
    }

    /**
     * Returns details of a Contest. This is shared between apiDetails and
     * apiAdminDetails.
     *
     * @return array{admission_mode: string, alias: string, description: string, director: null|string, feedback: string, finish_time: int, languages: list<string>, needs_basic_information: bool, partial_score: bool, original_contest_alias: null|string, original_problemset_id: int|null, penalty: int, penalty_calc_policy: string, penalty_type: string, problems: list<array{accepted: int, alias: string, commit: string, difficulty: float, languages: string, letter: string, order: int, points: float, problem_id: int, submissions: int, title: string, version: string, visibility: int, visits: int}>, points_decay_factor: float, problemset_id: int, requests_user_information: string, rerun_id: int, scoreboard: int, scoreboard_url: string, scoreboard_url_admin: string, show_scoreboard_after: bool, start_time: int, submissions_gap: int, title: string, window_length: int|null}
     */
    private static function getCachedDetails(
        string $contestAlias,
        \OmegaUp\DAO\VO\Contests $contest
    ) {
        return \OmegaUp\Cache::getFromCacheOrSet(
            \OmegaUp\Cache::CONTEST_INFO,
            $contestAlias,
            /** @return array{admission_mode: string, alias: string, description: string, director: null|string, feedback: string, finish_time: int, languages: list<string>, needs_basic_information: bool, partial_score: bool, original_contest_alias: null|string, original_problemset_id: int|null, penalty: int, penalty_calc_policy: string, penalty_type: string, problems: list<array{accepted: int, alias: string, commit: string, difficulty: float, languages: string, letter: string, order: int, points: float, problem_id: int, submissions: int, title: string, version: string, visibility: int, visits: int}>, points_decay_factor: float, problemset_id: int, requests_user_information: string, rerun_id: int, scoreboard: int, scoreboard_url: string, scoreboard_url_admin: string, show_scoreboard_after: bool, start_time: int, submissions_gap: int, title: string, window_length: int|null} */
            function () use ($contest, &$result) {
                // Initialize response to be the contest information
                /** @var array{admission_mode: string, alias: string, description: string, feedback: string, finish_time: int, languages: string, partial_score: bool, penalty: int, penalty_calc_policy: string, penalty_type: string, points_decay_factor: float, problemset_id: int, rerun_id: int, scoreboard: int, scoreboard_url: string, scoreboard_url_admin: string, show_scoreboard_after: bool, start_time: int, submissions_gap: int, title: string, window_length: int|null} */
                $result = $contest->asFilteredArray([
                    'admission_mode',
                    'alias',
                    'description',
                    'feedback',
                    'finish_time',
                    'languages',
                    'partial_score',
                    'penalty',
                    'penalty_calc_policy',
                    'penalty_type',
                    'points_decay_factor',
                    'problemset_id',
                    'rerun_id',
                    'scoreboard',
                    'scoreboard_url',
                    'scoreboard_url_admin',
                    'show_scoreboard_after',
                    'start_time',
                    'submissions_gap',
                    'title',
                    'window_length',
                ]);

                $result['start_time'] = intval(
                    \OmegaUp\DAO\DAO::fromMySQLTimestamp(
                        $result['start_time']
                    )
                );
                $result['finish_time'] = intval(
                    \OmegaUp\DAO\DAO::fromMySQLTimestamp(
                        $result['finish_time']
                    )
                );
                $result['original_contest_alias'] = null;
                $result['original_problemset_id'] = null;
                if ($result['rerun_id'] != 0) {
                    $originalContest = \OmegaUp\DAO\Contests::getByPK(
                        $result['rerun_id']
                    );
                    if (!is_null($originalContest)) {
                        $result['original_contest_alias'] = $originalContest->alias;
                        $result['original_problemset_id'] = $originalContest->problemset_id;
                    }
                }

                if (
                    is_null($contest->acl_id) ||
                    is_null($contest->problemset_id)
                ) {
                    throw new \OmegaUp\Exceptions\NotFoundException(
                        'contestNotFound'
                    );
                }
                $acl = \OmegaUp\DAO\ACLs::getByPK($contest->acl_id);
                if (is_null($acl) || is_null($acl->owner_id)) {
                    throw new \OmegaUp\Exceptions\NotFoundException();
                }
                $director = \OmegaUp\DAO\Identities::findByUserId(
                    $acl->owner_id
                );
                if (is_null($director)) {
                    throw new \OmegaUp\Exceptions\NotFoundException(
                        'userNotFound'
                    );
                }
                $result['director'] = $director->username;

                $problemsInContest = \OmegaUp\DAO\ProblemsetProblems::getProblemsByProblemset(
                    $contest->problemset_id
                );

                // Add info of each problem to the contest
                $problemsResponseArray = [];

                $letter = 0;

                foreach ($problemsInContest as $problem) {
                    // Add the 'points' value that is stored in the ContestProblem relationship
                    $problem['letter'] = \OmegaUp\Controllers\Contest::columnName(
                        $letter++
                    );
                    if (!empty($result['languages'])) {
                        $problem['languages'] = join(',', array_intersect(
                            explode(',', $result['languages']),
                            explode(',', $problem['languages'])
                        ));
                    }

                    // Save our array into the response
                    $problemsResponseArray[] = $problem;
                }

                // Add problems to response
                $result['problems'] = $problemsResponseArray;
                $result['languages'] = explode(',', $result['languages']);
                [
                    'needsBasicInformation' => $needsBasicInformation,
                    'requestsUserInformation' => $requestsUserInformation,
                ] = \OmegaUp\DAO\Contests::getNeedsInformation(
                    $contest->problemset_id
                );
                $result['needs_basic_information'] = $needsBasicInformation;
                $result['requests_user_information'] = $requestsUserInformation;
                return $result;
            },
            APC_USER_CACHE_CONTEST_INFO_TIMEOUT
        );
    }

    /**
     * Returns details of a Contest. Requesting the details of a contest will
     * not start the current user into that contest. In order to participate
     * in the contest, \OmegaUp\Controllers\Contest::apiOpen() must be used.
     *
     * @return array{admin?: bool, admission_mode: string, alias: string, description: string, director: null|string, feedback: string, finish_time: int, languages: list<string>, needs_basic_information: bool, opened: bool, partial_score: bool, original_contest_alias: null|string, original_problemset_id: int|null, penalty: int, penalty_calc_policy: string, penalty_type: string, problems: list<array{accepted: int, alias: string, commit: string, difficulty: float, languages: string, letter: string, order: int, points: float, problem_id: int, submissions: int, title: string, version: string, visibility: int, visits: int}>, points_decay_factor: float, problemset_id: int, requests_user_information: string, scoreboard: int, show_scoreboard_after: bool, start_time: int, submissions_gap: int, submission_deadline?: int, title: string, window_length: int|null}
     */
    public static function apiDetails(\OmegaUp\Request $r): array {
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['contest_alias'],
            'contest_alias'
        );
        $response = self::validateDetails($r);

        $result = self::getCachedDetails(
            $r['contest_alias'],
            $response['contest']
        );
        $result['opened'] = true;
        unset($result['scoreboard_url']);
        unset($result['scoreboard_url_admin']);
        unset($result['rerun_id']);
        if (!is_null($r['token'])) {
            $result['admin'] = $response['contest_admin'];
            return $result;
        }
        $r->ensureIdentity();

        // Adding timer info separately as it depends on the current user and we
        // don't want this to get generally cached for everybody
        // Save the time of the first access
        $problemsetIdentity = \OmegaUp\DAO\ProblemsetIdentities::checkAndSaveFirstTimeAccess(
            $r->identity,
            $response['contest']
        );
        $problemsetIdentity->access_time = $problemsetIdentity->access_time ?: 0;

        // Add time left to response
        if (is_null($response['contest']->window_length)) {
            $result['submission_deadline'] = $response['contest']->finish_time;
        } else {
            $result['submission_deadline'] = min(
                $response['contest']->finish_time,
                $problemsetIdentity->access_time + $response['contest']->window_length * 60
            );
        }
        $result['admin'] = \OmegaUp\Authorization::isContestAdmin(
            $r->identity,
            $response['contest']
        );

        // Log the operation.
        \OmegaUp\DAO\ProblemsetAccessLog::create(new \OmegaUp\DAO\VO\ProblemsetAccessLog([
            'identity_id' => $r->identity->identity_id,
            'problemset_id' => $response['contest']->problemset_id,
            'ip' => ip2long(strval($_SERVER['REMOTE_ADDR'])),
        ]));

        return $result;
    }

    /**
     * Returns details of a Contest, for administrators. This differs from
     * apiDetails in the sense that it does not attempt to calculate the
     * remaining time from the contest, or register the opened time.
     *
     * @return array{admin: bool, admission_mode: string, alias: string, available_languages: array<string, string>, description: string, director: null|string, feedback: string, finish_time: int, languages: list<string>, needs_basic_information: bool, partial_score: bool, opened: bool, original_contest_alias: null|string, original_problemset_id: int|null, penalty: int, penalty_calc_policy: string, penalty_type: string, problems: list<array{accepted: int, alias: string, commit: string, difficulty: float, languages: string, letter: string, order: int, points: float, problem_id: int, submissions: int, title: string, version: string, visibility: int, visits: int}>, points_decay_factor: float, problemset_id: int, requests_user_information: string, rerun_id: int, scoreboard: int, scoreboard_url: string, scoreboard_url_admin: string, show_scoreboard_after: bool, start_time: int, submissions_gap: int, title: string, window_length: int|null}
     */
    public static function apiAdminDetails(\OmegaUp\Request $r): array {
        $r->ensureMainUserIdentity();
        [
            'contest' => $contest,
        ] = self::validateDetails($r);
        if (is_null($contest->alias) || is_null($contest->problemset_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('contestNotFound');
        }

        if (
            !\OmegaUp\Authorization::isContestAdmin(
                $r->identity,
                $contest
            )
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        $result = self::getCachedDetails(
            $contest->alias,
            $contest
        );
        $result['opened'] = \OmegaUp\DAO\ProblemsetIdentities::checkProblemsetOpened(
            $r->identity->identity_id,
            $contest->problemset_id
        );
        $result['available_languages'] = \OmegaUp\Controllers\Run::SUPPORTED_LANGUAGES;
        $result['admin'] = true;
        return $result;
    }

    /**
     * Returns a report with all user activity for a contest.
     *
     * @return array{events: list<array{username: string, ip: int, time: int, classname?: string, alias?: string}>}
     */
    public static function apiActivityReport(\OmegaUp\Request $r): array {
        $response = self::validateDetails($r);

        if (!$response['contest_admin']) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }
        if (is_null($response['contest']->problemset_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('contestNotFound');
        }

        $accesses = \OmegaUp\DAO\ProblemsetAccessLog::GetAccessForProblemset(
            $response['contest']->problemset_id
        );
        $submissions = \OmegaUp\DAO\SubmissionLog::GetSubmissionsForProblemset(
            $response['contest']->problemset_id
        );

        return [
            'events' => \OmegaUp\ActivityReport::getActivityReport(
                $accesses,
                $submissions
            ),
        ];
    }

    /**
     * Returns a "column name" for the $idx (think Excel column names).
     */
    public static function columnName(int $idx): string {
        $name = chr(ord('A') + $idx % 26);
        while ($idx >= 26) {
            $idx /= 26;
            $idx--;
            $name = chr(ord('A') + $idx % 26) . $name;
        }
        return $name;
    }

    /**
     * Clone a contest
     *
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     * @throws \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException
     *
     * @return array{alias: string}
     */
    public static function apiClone(\OmegaUp\Request $r): array {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        // Authenticate user
        $r->ensureMainUserIdentity();

        \OmegaUp\Validators::validateStringNonEmpty(
            $r['contest_alias'],
            'contest_alias'
        );
        \OmegaUp\Validators::validateOptionalStringNonEmpty(
            $r['auth_token'],
            'auth_token'
        );
        $originalContest = self::validateContestAdmin(
            $r['contest_alias'],
            $r->identity
        );
        if (is_null($originalContest->problemset_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('contestNotFound');
        }

        // Validates form
        \OmegaUp\Validators::validateValidAlias($r['alias'], 'alias', true);
        \OmegaUp\Validators::validateStringNonEmpty($r['title'], 'title');
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['description'],
            'description'
        );
        \OmegaUp\Validators::validateNumber($r['start_time'], 'start_time');
        $startTime = $r['start_time'];

        $length = $originalContest->finish_time - $originalContest->start_time;

        $auth_token = isset($r['auth_token']) ? $r['auth_token'] : null;

        $problemset = new \OmegaUp\DAO\VO\Problemsets([
            'needs_basic_information' => false,
            'requests_user_information' => 'no',
        ]);

        $contest = new \OmegaUp\DAO\VO\Contests([
            'title' => $r['title'],
            'description' => $r['description'],
            'alias' => $r['alias'],
            'start_time' => $startTime,
            'finish_time' => $startTime + $length,
            'scoreboard' => $originalContest->scoreboard,
            'points_decay_factor' => $originalContest->points_decay_factor,
            'submissions_gap' => $originalContest->submissions_gap,
            'penalty_calc_policy' => $originalContest->penalty_calc_policy,
            'rerun_id' => $originalContest->rerun_id,
            'feedback' => $originalContest->feedback,
            'penalty_type' => $originalContest->penalty_type,
            'admission_mode' => 'private', // Cloned contests start in private
                                           // admission_mode
        ]);

        \OmegaUp\DAO\DAO::transBegin();
        try {
            // Create the contest
            self::createContest($problemset, $contest, $r->user->user_id);
            if (is_null($contest->problemset_id)) {
                throw new \OmegaUp\Exceptions\NotFoundException(
                    'contestNotFound'
                );
            }

            $problemsetProblems = \OmegaUp\DAO\ProblemsetProblems::getProblemsByProblemset(
                $originalContest->problemset_id
            );
            foreach ($problemsetProblems as $problemsetProblem) {
                $problem = new \OmegaUp\DAO\VO\Problems([
                    'problem_id' => $problemsetProblem['problem_id'],
                    'alias' => $problemsetProblem['alias'],
                    'visibility' => $problemsetProblem['visibility'],
                ]);
                \OmegaUp\Controllers\Problemset::addProblem(
                    $contest->problemset_id,
                    $problem,
                    $problemsetProblem['commit'],
                    $problemsetProblem['version'],
                    $r->identity,
                    $problemsetProblem['points'],
                    $problemsetProblem['order'] ?: 1
                );
            }
            \OmegaUp\DAO\DAO::transEnd();
        } catch (\Exception $e) {
            \OmegaUp\DAO\DAO::transRollback();
            throw $e;
        }

        return [
            'alias' => $r['alias'],
        ];
    }

    /**
     * @return array{alias: string}
     */
    public static function apiCreateVirtual(\OmegaUp\Request $r): array {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        // Authenticate user
        $r->ensureMainUserIdentity();

        \OmegaUp\Validators::validateValidAlias($r['alias'], 'alias', true);
        $originalContest = \OmegaUp\DAO\Contests::getByAlias($r['alias']);
        if (is_null($originalContest)) {
            throw new \OmegaUp\Exceptions\NotFoundException('contestNotFound');
        }

        if ($originalContest->finish_time > \OmegaUp\Time::get()) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'originalContestHasNotEnded'
            );
        }

        $virtualContestAlias = \OmegaUp\DAO\Contests::generateAlias(
            $originalContest
        );

        $contestLength = $originalContest->finish_time - $originalContest->start_time;

        $r->ensureInt('start_time', null, null, false);
        $startTime = (
            !is_null($r['start_time']) ?
            intval($r['start_time']) :
            \OmegaUp\Time::get()
        );

        // Initialize contest
        $contest = new \OmegaUp\DAO\VO\Contests([
            'title' => $originalContest->title,
            'description' => $originalContest->description,
            'window_length' => $originalContest->window_length,
            'start_time' => $startTime,
            'finish_time' => $startTime + $contestLength,
            'scoreboard' => 100, // Always show scoreboard in virtual contest
            'alias' => $virtualContestAlias,
            'points_decay_factor' => $originalContest->points_decay_factor,
            'submissions_gap' => $originalContest->submissions_gap,
            'partial_score' => $originalContest->partial_score,
            'feedback' => $originalContest->feedback,
            'penalty' => $originalContest->penalty,
            'penalty_type' => $originalContest->penalty_type,
            'penalty_calc_policy' => $originalContest->penalty_calc_policy,
            'show_scoreboard_after' => true,
            'languages' => $originalContest->languages,
            'rerun_id' => $originalContest->contest_id,
        ]);

        $problemset = new \OmegaUp\DAO\VO\Problemsets([
            'needs_basic_information' => false,
            'requests_user_information' => 'no',
            'access_mode' => 'private', // Virtual contest must be private
        ]);

        self::createContest(
            $problemset,
            $contest,
            $r->user->user_id,
            $originalContest->problemset_id
        );

        return [
            'alias' => strval($contest->alias),
        ];
    }

    /**
     * It retrieves a Problemset and a Contest objects to store them in the
     * database
     */
    private static function createContest(
        \OmegaUp\DAO\VO\Problemsets $problemset,
        \OmegaUp\DAO\VO\Contests $contest,
        int $currentUserId,
        ?int $originalProblemsetId = null
    ): void {
        $acl = new \OmegaUp\DAO\VO\ACLs();
        $acl->owner_id = $currentUserId;
        // Push changes
        try {
            // Begin a new transaction
            \OmegaUp\DAO\DAO::transBegin();

            \OmegaUp\DAO\ACLs::create($acl);
            $problemset->acl_id = $acl->acl_id;
            $problemset->type = 'Contest';
            $problemset->scoreboard_url = \OmegaUp\SecurityTools::randomString(
                30
            );
            $problemset->scoreboard_url_admin = \OmegaUp\SecurityTools::randomString(
                30
            );
            $contest->acl_id = $acl->acl_id;

            // Save the problemset object with data sent by user to the database
            \OmegaUp\DAO\Problemsets::create($problemset);

            $contest->problemset_id = $problemset->problemset_id;
            $contest->penalty_calc_policy = $contest->penalty_calc_policy ?: 'sum';
            $contest->rerun_id = $contest->rerun_id ?: 0;
            if (!is_null($originalProblemsetId)) {
                if (is_null($contest->problemset_id)) {
                    throw new \OmegaUp\Exceptions\NotFoundException(
                        'contestNotFound'
                    );
                }
                \OmegaUp\DAO\ProblemsetProblems::copyProblemset(
                    $contest->problemset_id,
                    $originalProblemsetId
                );
            }

            // Save the contest object with data sent by user to the database
            \OmegaUp\DAO\Contests::create($contest);

            // Update contest_id in problemset object
            $problemset->contest_id = $contest->contest_id;
            \OmegaUp\DAO\Problemsets::update($problemset);

            // End transaction transaction
            \OmegaUp\DAO\DAO::transEnd();
        } catch (\Exception $e) {
            // Operation failed in the data layer, rollback transaction
            \OmegaUp\DAO\DAO::transRollback();
            if (\OmegaUp\DAO\DAO::isDuplicateEntryException($e)) {
                throw new \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException(
                    'titleInUse',
                    $e
                );
            }
            throw $e;
        }

        // Expire contest-list cache
        \OmegaUp\Cache::invalidateAllKeys(\OmegaUp\Cache::CONTESTS_LIST_PUBLIC);
        \OmegaUp\Cache::invalidateAllKeys(
            \OmegaUp\Cache::CONTESTS_LIST_SYSTEM_ADMIN
        );

        self::$log->info("New Contest Created: {$contest->alias}");
    }

    /**
     * Creates a new contest
     *
     * @throws \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException
     *
     * @return array{status: string}
     */
    public static function apiCreate(\OmegaUp\Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        // Authenticate user
        $r->ensureMainUserIdentity();

        // Validate request
        self::validateCreate($r, $r->identity);

        // Set private contest by default if is not sent in request
        if (
            !is_null($r['admission_mode']) &&
            $r['admission_mode'] !== 'private'
        ) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'contestMustBeCreatedInPrivateMode'
            );
        }

        $r->ensureBool('basic_information', false);

        $problemset = new \OmegaUp\DAO\VO\Problemsets([
            'needs_basic_information' => boolval($r['basic_information']),
            'requests_user_information' => $r['requests_user_information'],
        ]);

        $languages = (
            empty($r['languages']) || !is_array($r['languages']) ?
            null :
            join(',', $r['languages'])
        );
        $contest = new \OmegaUp\DAO\VO\Contests([
            'admission_mode' => 'private',
            'title' => $r['title'],
            'description' => $r['description'],
            'start_time' => $r['start_time'],
            'finish_time' => $r['finish_time'],
            'window_length' => $r['window_length'] ?: null,
            'alias' => $r['alias'],
            'scoreboard' => $r['scoreboard'],
            'points_decay_factor' => $r['points_decay_factor'],
            'partial_score' => $r['partial_score'] ?? true,
            'submissions_gap' => $r['submissions_gap'],
            'feedback' => $r['feedback'],
            'penalty_calc_policy' => $r['penalty_calc_policy'],
            'penalty' => max(0, intval($r['penalty'])),
            'penalty_type' => $r['penalty_type'],
            'languages' => $languages,
            'show_scoreboard_after' => $r['show_scoreboard_after'] ?? true,
        ]);

        self::createContest($problemset, $contest, $r->user->user_id);

        return ['status' => 'ok'];
    }

    /**
     * Validates that Request contains expected data to create or update a contest
     * In case of update, everything is optional except the contest_alias
     * In case of error, this function throws.
     *
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     */
    private static function validateCommonCreateOrUpdate(
        \OmegaUp\Request $r,
        \OmegaUp\DAO\VO\Identities $identity,
        ?\OmegaUp\DAO\VO\Contests $contest = null,
        bool $isRequired = true
    ): void {
        \OmegaUp\Validators::validateOptionalStringNonEmpty(
            $r['title'],
            'title',
            $isRequired
        );
        \OmegaUp\Validators::validateOptionalStringNonEmpty(
            $r['description'],
            'description',
            $isRequired
        );
        $r->ensureInt('start_time', null, null, $isRequired);
        $r->ensureInt('finish_time', null, null, $isRequired);
        $currentStartTime = null;
        $currentFinishTime = null;
        if (!is_null($contest)) {
            $currentStartTime = \OmegaUp\DAO\DAO::fromMySQLTimestamp(
                $contest->start_time
            );
            $currentFinishTime = \OmegaUp\DAO\DAO::fromMySQLTimestamp(
                $contest->finish_time
            );
        }

        // Get the actual start and finish time of the contest, considering that
        // in case of update, parameters can be optional
        $startTime = (
            !is_null($r['start_time']) ?
            intval($r['start_time']) :
            $currentStartTime
        );
        $finishTime = (
            !is_null($r['finish_time']) ?
            intval($r['finish_time']) :
            $currentFinishTime
        );

        // Validate start & finish time
        if ($startTime > $finishTime) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'contestNewInvalidStartTime'
            );
        }

        // Calculate the actual contest length
        $contestLength = null;
        if (!is_null($finishTime) && !is_null($startTime)) {
            $contestLength = $finishTime - $startTime;
        }

        // Validate max contest length
        if ($contestLength > \OmegaUp\Controllers\Contest::MAX_CONTEST_LENGTH_SECONDS) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'contestLengthTooLong'
            );
        }

        // Window_length is optional
        if (!empty($r['window_length'])) {
            $r->ensureInt(
                'window_length',
                0,
                is_null($contestLength) ? null : intval($contestLength / 60),
                false
            );
        }

        \OmegaUp\Validators::validateOptionalInEnum(
            $r['admission_mode'],
            'admission_mode',
            [
                'public',
                'private',
                'registration',
            ]
        );
        \OmegaUp\Validators::validateValidAlias(
            $r['alias'],
            'alias',
            $isRequired
        );
        $r->ensureFloat('scoreboard', 0, 100, $isRequired);
        $r->ensureFloat('points_decay_factor', 0, 1, $isRequired);
        $r->ensureBool('partial_score', false);
        $r->ensureInt('submissions_gap', 0, null, $isRequired);
        // Validate the submission_gap in minutes so that the error message
        // matches what is displayed in the UI.
        \OmegaUp\Validators::validateNumberInRange(
            (
                is_null($r['submissions_gap']) ?
                null :
                floor(intval($r['submissions_gap']) / 60)
            ),
            'submissions_gap',
            1,
            is_null($contestLength) ? null : floor($contestLength / 60),
            $isRequired
        );

        \OmegaUp\Validators::validateOptionalInEnum(
            $r['feedback'],
            'feedback',
            ['no', 'yes', 'partial'],
            $isRequired
        );
        \OmegaUp\Validators::validateOptionalInEnum(
            $r['penalty_type'],
            'penalty_type',
            ['contest_start', 'problem_open', 'runtime', 'none'],
            $isRequired
        );
        \OmegaUp\Validators::validateOptionalInEnum(
            $r['penalty_calc_policy'],
            'penalty_calc_policy',
            ['sum', 'max']
        );
        \OmegaUp\Validators::validateOptionalStringNonEmpty(
            $r['problems'],
            'problems'
        );

        // Problems is optional
        if (!is_null($r['problems'])) {
            /** @var list<array{problem: string, points: int}>|null */
            $requestProblems = json_decode($r['problems'], /*$assoc=*/true);
            if (!is_array($requestProblems)) {
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'invalidParameters',
                    'problems'
                );
            }

            $problems = [];

            foreach ($requestProblems as $requestProblem) {
                $problem = \OmegaUp\DAO\Problems::getByAlias(
                    $requestProblem['problem']
                );
                if (is_null($problem)) {
                    throw new \OmegaUp\Exceptions\InvalidParameterException(
                        'parameterNotFound',
                        'problems'
                    );
                }
                \OmegaUp\Controllers\Problemset::validateAddProblemToProblemset(
                    $problem,
                    $identity
                );
                array_push($problems, [
                    'id' => $problem->problem_id,
                    'alias' => $requestProblem['problem'],
                    'points' => $requestProblem['points']
                ]);
            }

            $r['problems'] = $problems;
        }

        // Show scoreboard is always optional
        $r->ensureBool('show_scoreboard_after', false);

        // languages is always optional
        if (!empty($r['languages'])) {
            foreach ($r['languages'] as $language) {
                \OmegaUp\Validators::validateOptionalInEnum(
                    $language,
                    'languages',
                    array_keys(\OmegaUp\Controllers\Run::SUPPORTED_LANGUAGES)
                );
            }
        }
    }

    /**
     * Validates that Request contains expected data to create a contest
     * In case of error, this function throws.
     *
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     */
    private static function validateCreate(
        \OmegaUp\Request $r,
        \OmegaUp\DAO\VO\Identities $identity
    ): void {
        self::validateCommonCreateOrUpdate($r, $identity);
    }

    /**
     * Validates that Request contains expected data to update a contest
     * everything is optional except the contest_alias
     * In case of error, this function throws.
     *
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     *
     * @return \OmegaUp\DAO\VO\Contests
     */
    private static function validateUpdate(
        \OmegaUp\Request $r,
        \OmegaUp\DAO\VO\Identities $identity,
        string $contestAlias
    ): \OmegaUp\DAO\VO\Contests {
        $contest = self::validateContestAdmin(
            $contestAlias,
            $identity
        );

        self::validateCommonCreateOrUpdate(
            $r,
            $identity,
            $contest,
            false /* is required*/
        );

        // Prevent date changes if a contest already has runs
        if (
            !is_null($r['start_time']) &&
            $r['start_time'] != \OmegaUp\DAO\DAO::fromMySQLTimestamp(
                $contest->start_time
            )
        ) {
            $runCount = 0;

            $runCount = \OmegaUp\DAO\Submissions::countTotalSubmissionsOfProblemset(
                intval($contest->problemset_id)
            );

            if ($runCount > 0) {
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'contestUpdateAlreadyHasRuns'
                );
            }
        }
        return $contest;
    }

    /**
     * Function created to be called for all the API's that only can access
     * admins or contest organizers.
     *
     * @throws \OmegaUp\Exceptions\NotFoundException
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return \OmegaUp\DAO\VO\Contests
     */
    private static function validateContestAdmin(
        string $contestAlias,
        \OmegaUp\DAO\VO\Identities $identity,
        string $message = 'userNotAllowed'
    ): \OmegaUp\DAO\VO\Contests {
        $contest = \OmegaUp\DAO\Contests::getByAlias($contestAlias);
        if (is_null($contest)) {
            throw new \OmegaUp\Exceptions\NotFoundException('contestNotFound');
        }

        if (!\OmegaUp\Authorization::isContestAdmin($identity, $contest)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException($message);
        }
        return $contest;
    }

    /**
     * This function is used to restrict API in virtual contest
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return void
     */
    private static function forbiddenInVirtual(\OmegaUp\DAO\VO\Contests $contest): void {
        if (\OmegaUp\DAO\Contests::isVirtual($contest)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'forbiddenInVirtualContest'
            );
        }
    }

    /**
     * Gets the problems from a contest
     *
     * @return array{problems: list<array{accepted: int, alias: string, commit: string, difficulty: float, languages: string, order: int, points: float, problem_id: int, submissions: int, title: string, version: string, visibility: int, visits: int}>}
     */
    public static function apiProblems(\OmegaUp\Request $r): array {
        // Authenticate user
        $r->ensureIdentity();

        \OmegaUp\Validators::validateStringNonEmpty(
            $r['contest_alias'],
            'contest_alias'
        );

        // Only director is allowed to create problems in contest
        $contest = self::validateContestAdmin(
            $r['contest_alias'],
            $r->identity,
            'cannotAddProb'
        );
        if (is_null($contest->problemset_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'contestNotFound'
            );
        }

        $problemset = \OmegaUp\DAO\Problemsets::getByPK(
            $contest->problemset_id
        );
        if (is_null($problemset) || is_null($problemset->problemset_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'problemsetNotFound'
            );
        }
        $problems = \OmegaUp\DAO\ProblemsetProblems::getProblemsByProblemset(
            $problemset->problemset_id
        );
        foreach ($problems as &$problem) {
            unset($problem['problem_id']);
        }

        return [
            'problems' => $problems,
        ];
    }

    /**
     * Adds a problem to a contest
     *
     * @return array{status: string}
     */
    public static function apiAddProblem(\OmegaUp\Request $r): array {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        // Authenticate user
        $r->ensureMainUserIdentity();
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['contest_alias'],
            'contest_alias'
        );
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['problem_alias'],
            'problem_alias'
        );
        \OmegaUp\Validators::validateNumberInRange(
            $r['points'],
            'points',
            /*$lowerBound=*/ 0,
            /*$upperBound=*/ INF
        );
        \OmegaUp\Validators::validateNumberInRange(
            $r['order_in_contest'],
            'order_in_contest',
            /*$lowerBound=*/ 0,
            /*$upperBound=*/ null,
            /*$required=*/ false
        );
        $r->ensureFloat('points', 0, INF);
        $r->ensureInt('order_in_contest', 0, null, false);

        // Validate the request and get the problem and the contest in an array
        $params = self::validateAddToContestRequest(
            $r->identity,
            $r['contest_alias'],
            $r['problem_alias']
        );

        self::forbiddenInVirtual($params['contest']);

        /** @var int $params['contest']->problemset_id */
        $problemset = \OmegaUp\DAO\Problemsets::getByPK(
            $params['contest']->problemset_id
        );
        if (is_null($problemset)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'problemsetNotFound'
            );
        }

        if (
            \OmegaUp\DAO\ProblemsetProblems::countProblemsetProblems(
                $problemset
            )
                >= MAX_PROBLEMS_IN_CONTEST
        ) {
            throw new \OmegaUp\Exceptions\PreconditionFailedException(
                'contestAddproblemTooManyProblems'
            );
        }

        \OmegaUp\Validators::validateStringOfLengthInRange(
            $r['commit'],
            'commit',
            1,
            40,
            false
        );
        [$masterCommit, $currentVersion] = \OmegaUp\Controllers\Problem::resolveCommit(
            $params['problem'],
            $r['commit']
        );

        \OmegaUp\Controllers\Problemset::addProblem(
            $params['contest']->problemset_id,
            $params['problem'],
            $masterCommit,
            $currentVersion,
            $r->identity,
            floatval($r['points']),
            !empty($r['order_in_contest']) ? intval($r['order_in_contest']) : 1
        );

        // Invalidar cache
        \OmegaUp\Cache::deleteFromCache(
            \OmegaUp\Cache::CONTEST_INFO,
            $r['contest_alias']
        );
        \OmegaUp\Scoreboard::invalidateScoreboardCache(
            \OmegaUp\ScoreboardParams::fromContest(
                $params['contest']
            )
        );

        return ['status' => 'ok'];
    }

    /**
     * Validates the request for AddToContest and returns an array with
     * the problem and contest DAOs
     *
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return array{contest: \OmegaUp\DAO\VO\Contests, problem: \OmegaUp\DAO\VO\Problems}
     */
    private static function validateAddToContestRequest(
        \OmegaUp\DAO\VO\Identities $identity,
        string $contestAlias,
        string $problemAlias
    ): array {
        // Only director is allowed to create problems in contest
        $contest = \OmegaUp\DAO\Contests::getByAlias($contestAlias);
        if (is_null($contest)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterNotFound',
                'contest_alias'
            );
        }
        // Only contest admin is allowed to create problems in contest
        if (!\OmegaUp\Authorization::isContestAdmin($identity, $contest)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'cannotAddProb'
            );
        }

        $problem = \OmegaUp\DAO\Problems::getByAlias($problemAlias);
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterNotFound',
                'problem_alias'
            );
        }

        if (
            $problem->visibility == \OmegaUp\ProblemParams::VISIBILITY_PRIVATE_BANNED
            || $problem->visibility == \OmegaUp\ProblemParams::VISIBILITY_PUBLIC_BANNED
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'problemIsBanned'
            );
        }
        if (
            !\OmegaUp\DAO\Problems::isVisible($problem) &&
            !\OmegaUp\Authorization::isProblemAdmin(
                $identity,
                $problem
            )
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'problemIsPrivate'
            );
        }

        return [
            'contest' => $contest,
            'problem' => $problem,
        ];
    }

    /**
     * Removes a problem from a contest
     *
     * @return array{status: string}
     */
    public static function apiRemoveProblem(\OmegaUp\Request $r) {
        // Authenticate user
        $r->ensureMainUserIdentity();

        \OmegaUp\Validators::validateStringNonEmpty(
            $r['contest_alias'],
            'contest_alias'
        );
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['problem_alias'],
            'problem_alias'
        );

        // Validate the request and get the problem and the contest in an array
        $params = self::validateRemoveFromContestRequest(
            $r['contest_alias'],
            $r['problem_alias'],
            $r->identity
        );

        self::forbiddenInVirtual($params['contest']);

        \OmegaUp\DAO\ProblemsetProblems::delete(new \OmegaUp\DAO\VO\ProblemsetProblems([
            'problemset_id' => $params['contest']->problemset_id,
            'problem_id' => $params['problem']->problem_id
        ]));

        // Invalidar cache
        \OmegaUp\Cache::deleteFromCache(
            \OmegaUp\Cache::CONTEST_INFO,
            $r['contest_alias']
        );
        \OmegaUp\Scoreboard::invalidateScoreboardCache(
            \OmegaUp\ScoreboardParams::fromContest(
                $params['contest']
            )
        );

        return ['status' => 'ok'];
    }

    /**
     * Validates the request for RemoveFromContest and returns an array with
     * the problem and contest DAOs
     *
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return array{contest: \OmegaUp\DAO\VO\Contests, problem: \OmegaUp\DAO\VO\Problems}
     */
    private static function validateRemoveFromContestRequest(
        string $contestAlias,
        string $problemAlias,
        \OmegaUp\DAO\VO\Identities $identity
    ): array {
        $contest = \OmegaUp\DAO\Contests::getByAlias($contestAlias);
        if (is_null($contest) || is_null($contest->problemset_id)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterNotFound',
                'contest_alias'
            );
        }
        // Only contest admin is allowed to remove problems in contest
        if (!\OmegaUp\Authorization::isContestAdmin($identity, $contest)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'cannotRemoveProblem'
            );
        }

        $problem = \OmegaUp\DAO\Problems::getByAlias($problemAlias);
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterNotFound',
                'problem_alias'
            );
        }

        // Disallow removing problem from contest if it already has runs within the contest
        if (
            \OmegaUp\DAO\Submissions::countTotalRunsOfProblemInProblemset(
                intval($problem->problem_id),
                intval($contest->problemset_id)
            ) > 0
            && !\OmegaUp\Authorization::isSystemAdmin($identity)
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'cannotRemoveProblemWithSubmissions'
            );
        }

        if (self::isPublic($contest->admission_mode)) {
            // Check that contest has at least 2 problems
            $problemset = \OmegaUp\DAO\Problemsets::getByPK(
                $contest->problemset_id
            );
            if (is_null($problemset)) {
                throw new \OmegaUp\Exceptions\NotFoundException(
                    'problemsetNotFound'
                );
            }
            $problemsInContest = \OmegaUp\DAO\ProblemsetProblems::GetRelevantProblems(
                $problemset
            );
            if (count($problemsInContest) < 2) {
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'contestPublicRequiresProblem'
                );
            }
        }

        return [
            'contest' => $contest,
            'problem' => $problem,
        ];
    }

    /**
     * Return a report of which runs would change due to a version change.
     *
     * @return array{diff: list<array{guid: string, new_score: float|null, new_status: null|string, new_verdict: null|string, old_score: float|null, old_status: null|string, old_verdict: null|string, problemset_id: int|null, username: string}>}
     */
    public static function apiRunsDiff(\OmegaUp\Request $r): array {
        $r->ensureIdentity();

        \OmegaUp\Validators::validateValidAlias(
            $r['problem_alias'],
            'problem_alias'
        );
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['contest_alias'],
            'contest_alias'
        );
        \OmegaUp\Validators::validateStringNonEmpty($r['version'], 'version');

        $contest = self::validateContestAdmin(
            $r['contest_alias'],
            $r->identity
        );

        $problem = \OmegaUp\DAO\Problems::getByAlias($r['problem_alias']);
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        $problemsetProblem = \OmegaUp\DAO\ProblemsetProblems::getByPK(
            intval($contest->problemset_id),
            intval($problem->problem_id)
        );
        if (
            is_null($problemsetProblem)
            || is_null($problemsetProblem->version)
        ) {
            throw new \OmegaUp\Exceptions\NotFoundException('recordNotFound');
        }

        return [
            'diff' => \OmegaUp\DAO\Runs::getRunsDiffsForVersion(
                $problem,
                intval($contest->problemset_id),
                $problemsetProblem->version,
                $r['version']
            ),
        ];
    }

    /**
     * Validates add/remove user request
     *
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return array{0: \OmegaUp\DAO\VO\Identities, 1: \OmegaUp\DAO\VO\Contests}
     */
    private static function validateAddRemoveUser(
        string $contestAlias,
        string $usernameOrEmail,
        \OmegaUp\DAO\VO\Identities $identity
    ): array {
        $identityToRemove = \OmegaUp\Controllers\Identity::resolveIdentity(
            $usernameOrEmail
        );
        $contest = self::validateContestAdmin($contestAlias, $identity);
        return [$identityToRemove, $contest];
    }

    /**
     * Adds a user to a contest.
     * By default, any user can view details of public contests.
     * Only users added through this API can view private contests
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return array{status: string}
     */
    public static function apiAddUser(\OmegaUp\Request $r): array {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        // Authenticate logged user
        $r->ensureMainUserIdentity();
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['contest_alias'],
            'contest_alias'
        );
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['usernameOrEmail'],
            'usernameOrEmail'
        );
        [$identity, $contest] = self::validateAddRemoveUser(
            $r['contest_alias'],
            $r['usernameOrEmail'],
            $r->identity
        );

        // Save the contest to the DB
        \OmegaUp\DAO\ProblemsetIdentities::replace(new \OmegaUp\DAO\VO\ProblemsetIdentities([
            'problemset_id' => $contest->problemset_id,
            'identity_id' => $identity->identity_id,
            'access_time' => null,
            'end_time' => null,
            'score' => 0,
            'time' => 0,
            'is_invited' => true,
        ]));

        return ['status' => 'ok'];
    }

    /**
     * Remove a user from a private contest
     *
     * @return array{status: string}
     */
    public static function apiRemoveUser(\OmegaUp\Request $r): array {
        // Authenticate logged user
        $r->ensureMainUserIdentity();
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['contest_alias'],
            'contest_alias'
        );
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['usernameOrEmail'],
            'usernameOrEmail'
        );
        [$identity, $contest] = self::validateAddRemoveUser(
            $r['contest_alias'],
            $r['usernameOrEmail'],
            $r->identity
        );

        \OmegaUp\DAO\ProblemsetIdentities::delete(new \OmegaUp\DAO\VO\ProblemsetIdentities([
            'problemset_id' => $contest->problemset_id,
            'identity_id' => $identity->identity_id,
        ]));

        return ['status' => 'ok'];
    }

    /**
     * Adds an group to a contest
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return array{status: string}
     */
    public static function apiAddGroup(\OmegaUp\Request $r): array {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        // Authenticate logged user
        $r->ensureIdentity();

        // Check contest_alias
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['contest_alias'],
            'contest_alias'
        );
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['group'],
            'group'
        );

        $group = \OmegaUp\DAO\Groups::findByAlias($r['group']);
        if (is_null($group)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'invalidParameters'
            );
        }

        $contest = self::validateContestAdmin(
            $r['contest_alias'],
            $r->identity
        );
        $problemset = \OmegaUp\DAO\Problemsets::getByPK(
            intval($contest->problemset_id)
        );
        if (is_null($problemset)) {
            throw new \OmegaUp\Exceptions\NotFoundException('contestNotFound');
        }
        \OmegaUp\DAO\GroupRoles::create(
            new \OmegaUp\DAO\VO\GroupRoles([
                'acl_id' => $problemset->acl_id,
                'group_id' => $group->group_id,
                'role_id' => \OmegaUp\Authorization::CONTESTANT_ROLE,
            ])
        );

        return ['status' => 'ok'];
    }

    /**
     * Removes a group from a contest
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return array{status: string}
     */
    public static function apiRemoveGroup(\OmegaUp\Request $r): array {
        // Authenticate logged user
        $r->ensureIdentity();

        // Check contest_alias
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['contest_alias'],
            'contest_alias'
        );
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['group'],
            'group'
        );

        $group = \OmegaUp\DAO\Groups::findByAlias($r['group']);
        if (is_null($group)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'invalidParameters'
            );
        }

        $contest = self::validateContestAdmin(
            $r['contest_alias'],
            $r->identity
        );
        $problemset = \OmegaUp\DAO\Problemsets::getByPK(
            intval($contest->problemset_id)
        );
        if (is_null($problemset)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'problemsetNotFound'
            );
        }
        \OmegaUp\DAO\GroupRoles::delete(
            new \OmegaUp\DAO\VO\GroupRoles([
                'acl_id' => $problemset->acl_id,
                'group_id' => $group->group_id,
                'role_id' => \OmegaUp\Authorization::CONTESTANT_ROLE,
            ])
        );

        return ['status' => 'ok'];
    }

    /**
     * Adds an admin to a contest
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return array{status: string}
     */
    public static function apiAddAdmin(\OmegaUp\Request $r): array {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        // Authenticate logged user
        $r->ensureMainUserIdentity();

        // Check contest_alias
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['contest_alias'],
            'contest_alias'
        );
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['usernameOrEmail'],
            'usernameOrEmail'
        );

        $user = \OmegaUp\Controllers\User::resolveUser($r['usernameOrEmail']);

        $contest = self::validateContestAdmin(
            $r['contest_alias'],
            $r->identity
        );
        if (is_null($contest->acl_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('contestNotFound');
        }

        \OmegaUp\Controllers\ACL::addUser(
            $contest->acl_id,
            intval($user->user_id)
        );

        return ['status' => 'ok'];
    }

    /**
     * Removes an admin from a contest
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return array{status: string}
     */
    public static function apiRemoveAdmin(\OmegaUp\Request $r): array {
        // Authenticate logged user
        $r->ensureMainUserIdentity();

        // Check contest_alias
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['contest_alias'],
            'contest_alias'
        );
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['usernameOrEmail'],
            'usernameOrEmail'
        );

        $identity = \OmegaUp\Controllers\Identity::resolveIdentity(
            $r['usernameOrEmail']
        );
        if (is_null($identity->user_id)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'invalidUser'
            );
        }

        $contest = self::validateContestAdmin(
            $r['contest_alias'],
            $r->identity
        );

        // Check if admin to delete is actually an admin
        if (!\OmegaUp\Authorization::isContestAdmin($identity, $contest)) {
            throw new \OmegaUp\Exceptions\NotFoundException();
        }

        \OmegaUp\Controllers\ACL::removeUser(
            intval($contest->acl_id),
            $identity->user_id
        );

        return ['status' => 'ok'];
    }

    /**
     * Adds an group admin to a contest
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return array{status: string}
     */
    public static function apiAddGroupAdmin(\OmegaUp\Request $r): array {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        // Authenticate logged user
        $r->ensureMainUserIdentity();

        // Check contest_alias
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['contest_alias'],
            'contest_alias'
        );
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['group'],
            'group'
        );

        $group = \OmegaUp\DAO\Groups::findByAlias($r['group']);
        if (is_null($group)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'invalidParameters'
            );
        }

        $contest = self::validateContestAdmin(
            $r['contest_alias'],
            $r->identity
        );

        \OmegaUp\Controllers\ACL::addGroup(
            intval($contest->acl_id),
            intval($group->group_id)
        );

        return ['status' => 'ok'];
    }

    /**
     * Removes a group admin from a contest
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return array{status: string}
     */
    public static function apiRemoveGroupAdmin(\OmegaUp\Request $r): array {
        // Authenticate logged user
        $r->ensureMainUserIdentity();

        // Check contest_alias
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['contest_alias'],
            'contest_alias'
        );
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['group'],
            'group'
        );

        $group = \OmegaUp\DAO\Groups::findByAlias($r['group']);
        if (is_null($group)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'invalidParameters'
            );
        }

        $contest = self::validateContestAdmin(
            $r['contest_alias'],
            $r->identity
        );

        \OmegaUp\Controllers\ACL::removeGroup(
            intval($contest->acl_id),
            intval($group->group_id)
        );

        return ['status' => 'ok'];
    }

    /**
     * Validate the Clarifications request
     */
    private static function validateClarifications(\OmegaUp\Request $r): \OmegaUp\DAO\VO\Contests {
        // Check contest_alias
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['contest_alias'],
            'contest_alias'
        );

        $contest = \OmegaUp\DAO\Contests::getByAlias($r['contest_alias']);
        if (is_null($contest)) {
            throw new \OmegaUp\Exceptions\NotFoundException('contestNotFound');
        }

        $r->ensureInt('offset', null, null, false /* optional */);
        $r->ensureInt('rowcount', null, null, false /* optional */);

        return $contest;
    }

    /**
     * Get clarifications of a contest
     *
     * @return array{clarifications: list<array{answer: null|string, author: string, clarification_id: int, message: string, problem_alias: string, public: bool, receiver: null|string, time: int}>}
     */
    public static function apiClarifications(\OmegaUp\Request $r): array {
        $r->ensureIdentity();
        $r->ensureInt('offset', null, null, false /* optional */);
        $r->ensureInt('rowcount', null, null, false /* optional */);
        $contest = self::validateClarifications($r);

        $isContestDirector = \OmegaUp\Authorization::isContestAdmin(
            $r->identity,
            $contest
        );

        $clarifications = \OmegaUp\DAO\Clarifications::GetProblemsetClarifications(
            intval($contest->problemset_id),
            $isContestDirector,
            $r->identity->identity_id,
            empty($r['offset']) ? null : intval($r['offset']),
            empty($r['rowcount']) ? 1000 : intval($r['rowcount'])
        );

        foreach ($clarifications as &$clar) {
            $clar['time'] = intval($clar['time']);
        }

        return [
            'clarifications' => $clarifications,
        ];
    }

    /**
     * Returns the Scoreboard events
     *
     * @throws \OmegaUp\Exceptions\NotFoundException
     *
     * @return array{events: list<array{country: null|string, delta: float, is_invited: bool, total: array{points: float, penalty: float}, name: null|string, username: string, problem: array{alias: string, points: float, penalty: float}}>}
     */
    public static function apiScoreboardEvents(\OmegaUp\Request $r): array {
        // Get the current user
        $response = self::validateDetails($r);

        $params = \OmegaUp\ScoreboardParams::fromContest($response['contest']);
        $params->admin = (
            !is_null($r->identity) &&
            \OmegaUp\Authorization::isContestAdmin(
                $r->identity,
                $response['contest']
            ) &&
            !\OmegaUp\DAO\Contests::isVirtual($response['contest'])
        );
        $params->show_all_runs = !\OmegaUp\DAO\Contests::isVirtual(
            $response['contest']
        );
        $scoreboard = new \OmegaUp\Scoreboard($params);

        // Push scoreboard data in response
        return [
            'events' => $scoreboard->events(),
        ];
    }

    /**
     * Returns the Scoreboard
     *
     * @throws \OmegaUp\Exceptions\NotFoundException
     *
     * @return array{finish_time: int|null, problems: list<array{alias: string, order: int}>, ranking: list<array{country: null|string, is_invited: bool, name: string|null, place?: int, problems: list<array{alias: string, penalty: float, percent: float, place?: int, points: float, run_details?: array{cases?: list<array{contest_score: float, max_score: float, meta: array{status: string}, name: string|null, out_diff: string, score: float, verdict: string}>, details: array{groups: list<array{cases: list<array{meta: array{memory: float, time: float, wall_time: float}}>}>}}, runs: int}>, total: array{penalty: float, points: float}, username: string}>, start_time: int, time: int, title: string}
     */
    public static function apiScoreboard(\OmegaUp\Request $r): array {
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['contest_alias'],
            'contest_alias'
        );
        [
            'contest' => $contest,
            'problemset' => $problemset
        ] = self::validateBasicDetails(
            $r['contest_alias']
        );
        \OmegaUp\Validators::validateOptionalStringNonEmpty(
            $r['token'],
            'token'
        );
        try {
            $r->ensureIdentity();
        } catch (\OmegaUp\Exceptions\UnauthorizedException $e) {
            // Do nothing.
            $r->identity = null;
        }
        return self::getScoreboard(
            $contest,
            $problemset,
            $r->identity,
            $r['token']
        );
    }

    /**
     * @return array{finish_time: int|null, problems: list<array{alias: string, order: int}>, ranking: list<array{country: null|string, is_invited: bool, name: string|null, place?: int, problems: list<array{alias: string, penalty: float, percent: float, place?: int, points: float, run_details?: array{cases?: list<array{contest_score: float, max_score: float, meta: array{status: string}, name: string|null, out_diff: string, score: float, verdict: string}>, details: array{groups: list<array{cases: list<array{meta: array{memory: float, time: float, wall_time: float}}>}>}}, runs: int}>, total: array{penalty: float, points: float}, username: string}>, start_time: int, time: int, title: string}
     */
    private static function getScoreboard(
        \OmegaUp\DAO\VO\Contests $contest,
        \OmegaUp\DAO\VO\Problemsets $problemset,
        ?\OmegaUp\DAO\VO\Identities $identity,
        ?string $token = null
    ) {
        // If true, will override Scoreboard Pertentage to 100%
        $showAllRuns = false;

        if (is_null($token)) {
            // User should be logged
            if (is_null($identity)) {
                throw new \OmegaUp\Exceptions\UnauthorizedException();
            }

            self::canAccessContest($contest, $identity);

            if (
                \OmegaUp\Authorization::isContestAdmin(
                    $identity,
                    $contest
                )
            ) {
                $showAllRuns = true;
            }
        } else {
            if ($token === $problemset->scoreboard_url) {
                $showAllRuns = false;
            } elseif ($token === $problemset->scoreboard_url_admin) {
                $showAllRuns = true;
            } else {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                    'invalidScoreboardUrl'
                );
            }
        }

        // Create scoreboard
        $params = \OmegaUp\ScoreboardParams::fromContest($contest);
        $params->admin = $showAllRuns;
        $scoreboard = new \OmegaUp\Scoreboard($params);

        return $scoreboard->generate();
    }

    /**
     * Gets the accomulative scoreboard for an array of contests
     *
     * @return array{ranking: list<array{name: null|string, username: string, contests: array<string, array{points: float, penalty: float}>, total: array{points: float, penalty: float}}>}
     */
    public static function apiScoreboardMerge(\OmegaUp\Request $r): array {
        // Get the current user
        $r->ensureIdentity();

        \OmegaUp\Validators::validateStringNonEmpty(
            $r['contest_aliases'],
            'contest_aliases'
        );
        $contestAliases = explode(',', $r['contest_aliases']);

        \OmegaUp\Validators::validateOptionalStringNonEmpty(
            $r['usernames_filter'],
            'usernames_filter'
        );
        /** @var list<string> */
        $usernamesFilter = [];
        if (isset($r['usernames_filter'])) {
            $usernamesFilter = explode(',', $r['usernames_filter']);
        }

        if (isset($r['contest_params']) && is_array($r['contest_params'])) {
            /** @var array<string, array{only_ac: bool, weight: float}> */
            $contestParams = $r['contest_params'];
        } else {
            /** @var array<string, array{only_ac: bool, weight: float}> */
            $contestParams = [];
        }

        return [
            'ranking' => self::getMergedScoreboard(
                $contestAliases,
                $usernamesFilter,
                $contestParams
            ),
        ];
    }

    /**
     * @param list<string> $contestAliases
     * @param list<string> $usernamesFilter
     * @param array<string, array{only_ac: bool, weight: float}> $contestParams
     *
     * @return list<array{name: null|string, username: string, contests: array<string, array{points: float, penalty: float}>, total: array{points: float, penalty: float}}>
     */
    public static function getMergedScoreboard(
        array $contestAliases,
        array $usernamesFilter,
        array $contestParams
    ): array {
        // Validate all contest alias
        $contests = [];
        foreach ($contestAliases as $contestAlias) {
            $contest = \OmegaUp\DAO\Contests::getByAlias($contestAlias);
            if (is_null($contest)) {
                throw new \OmegaUp\Exceptions\NotFoundException(
                    'contestNotFound'
                );
            }

            $contests[] = $contest;
        }

        // Get all scoreboards
        $scoreboards = [];
        foreach ($contests as $contest) {
            if (is_null($contest->alias)) {
                throw new \OmegaUp\Exceptions\NotFoundException(
                    'contestNotFound'
                );
            }
            // Set defaults for contests params
            if (!isset($contestParams[$contest->alias])) {
                $contestParams[$contest->alias] = [
                    'only_ac' => false,
                    'weight' => 1.0,
                ];
            }
            $params = \OmegaUp\ScoreboardParams::fromContest($contest);
            $params->only_ac = $contestParams[strval(
                $contest->alias
            )]['only_ac'];
            $s = new \OmegaUp\Scoreboard($params);

            $scoreboards[strval($contest->alias)] = $s->generate();
        }

        /** @var array<string, array{name: null|string, username: string, contests: array<string, array{points: float, penalty: float}>, total: array{points: float, penalty: float}}> */
        $mergedScoreboard = [];

        // Merge
        /** @var string $contestAlias */
        foreach ($scoreboards as $contestAlias => $scoreboard) {
            foreach ($scoreboard['ranking'] as $userResults) {
                $username = $userResults['username'];
                // If user haven't been added to the merged scoredboard, add them.
                if (!isset($mergedScoreboard[$username])) {
                    $mergedScoreboard[$username] = [
                        'name' => $userResults['name'],
                        'username' => $username,
                        'contests' => [],
                        'total' => [
                            'points' => 0.0,
                            'penalty' => 0.0,
                        ],
                    ];
                }

                $mergedScoreboard[$username]['contests'][$contestAlias] = [
                    'points' => ($userResults['total']['points'] * $contestParams[$contestAlias]['weight']),
                    'penalty' => $userResults['total']['penalty'],
                ];

                $mergedScoreboard[$username]['total']['points'] += (
                    $userResults['total']['points'] * $contestParams[$contestAlias]['weight']
                );
                $mergedScoreboard[$username]['total']['penalty'] += (
                    $userResults['total']['penalty']
                );
            }
        }

        // Remove users not in filter
        if (!empty($usernamesFilter)) {
            foreach ($mergedScoreboard as $username => $entry) {
                if (array_search($username, $usernamesFilter) === false) {
                    unset($mergedScoreboard[$username]);
                }
            }
        }

        // Normalize user["contests"] entries so all contain the same contests
        foreach ($mergedScoreboard as $username => $entry) {
            foreach ($contests as $contest) {
                if (isset($entry['contests'][$contest->alias]['points'])) {
                    continue;
                }
                /** @var string $contest->alias */
                $mergedScoreboard[$username]['contests'][$contest->alias] = [
                    'points' => 0.0,
                    'penalty' => 0.0,
                ];
            }
        }

        // Sort mergedScoreboard
        usort(
            $mergedScoreboard,
            /**
             * @param array{name: null|string, username: string, contests: array<string, array{points: float, penalty: float}>, total: array{points: float, penalty: float}} $a
             * @param array{name: null|string, username: string, contests: array<string, array{points: float, penalty: float}>, total: array{points: float, penalty: float}} $b
             */
            function ($a, $b): int {
                if ($a['total']['points'] == $b['total']['points']) {
                    if ($a['total']['penalty'] == $b['total']['penalty']) {
                        return 0;
                    }

                    return ($a['total']['penalty'] > $b['total']['penalty']) ? 1 : -1;
                }

                return ($a['total']['points'] < $b['total']['points']) ? 1 : -1;
            }
        );

        /** @var list<array{name: null|string, username: string, contests: array<string, array{points: float, penalty: float}>, total: array{points: float, penalty: float}}> */
        return $mergedScoreboard;
    }

    /**
     * @return array{users: list<array{accepted: bool|null, admin?: array{username?: null|string}, country: null|string, last_update: null|string, request_time: string, username: string}>, contest_alias: string}
     */
    public static function apiRequests(\OmegaUp\Request $r): array {
        // Authenticate request
        $r->ensureIdentity();

        \OmegaUp\Validators::validateStringNonEmpty(
            $r['contest_alias'],
            'contest_alias'
        );

        $contest = self::validateContestAdmin(
            $r['contest_alias'],
            $r->identity
        );

        if (is_null($contest->problemset_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('contestNotFound');
        }

        $resultAdmins =
            \OmegaUp\DAO\ProblemsetIdentityRequest::getFirstAdminForProblemsetRequest(
                $contest->problemset_id
            );
        $resultRequests =
            \OmegaUp\DAO\ProblemsetIdentityRequest::getRequestsForProblemset(
                $contest->problemset_id
            );

        $admins = [];
        $requestsAdmins = [];
        $result = [
            'contest_alias' => $r['contest_alias'],
            'users' => [],
        ];
        foreach ($resultAdmins as $result) {
            $adminId = $result['admin_id'];
            if (!empty($adminId) && !array_key_exists($adminId, $admins)) {
                $admin = [];
                $data = \OmegaUp\DAO\Identities::findByUserId($adminId);
                if (!is_null($data)) {
                    $admin = [
                        'username' => $data->username,
                    ];
                }
                $requestsAdmins[$result['identity_id']] = $admin;
            }
        }

        $usersRequests = array_map(function ($request) use ($requestsAdmins) {
            if (isset($requestsAdmins[$request['identity_id']])) {
                $request['admin'] = $requestsAdmins[$request['identity_id']];
            }
            unset($request['identity_id']);
            return $request;
        }, $resultRequests);

        return [
            'users' => $usersRequests,
            'contest_alias' => $r['contest_alias'],
        ];
    }

    /**
     * @return array{status: string}
     */
    public static function apiArbitrateRequest(\OmegaUp\Request $r): array {
        $r->ensureMainUserIdentity();

        \OmegaUp\Validators::validateStringNonEmpty(
            $r['contest_alias'],
            'contest_alias'
        );
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['username'],
            'username'
        );
        \OmegaUp\Validators::validateOptionalStringNonEmpty(
            $r['note'],
            'note'
        );

        if (is_null($r['resolution'])) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'invalidParameters'
            );
        }

        $contest = self::validateContestAdmin(
            $r['contest_alias'],
            $r->identity
        );

        $targetIdentity = \OmegaUp\DAO\Identities::findByUsername(
            $r['username']
        );
        if (is_null($targetIdentity) || is_null($targetIdentity->username)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'userNotFound'
            );
        }

        $request = \OmegaUp\DAO\ProblemsetIdentityRequest::getByPK(
            $targetIdentity->identity_id,
            $contest->problemset_id
        );

        if (is_null($request)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'userNotInListOfRequests'
            );
        }

        if (is_bool($r['resolution'])) {
            $resolution = $r['resolution'];
        } else {
            $resolution = $r['resolution'] === 'true';
        }

        $request->accepted = $resolution;
        $request->extra_note = $r['note'];
        $request->last_update = \OmegaUp\Time::get();

        \OmegaUp\DAO\ProblemsetIdentityRequest::update($request);

        // Save this action in the history
        \OmegaUp\DAO\ProblemsetIdentityRequestHistory::create(new \OmegaUp\DAO\VO\ProblemsetIdentityRequestHistory([
            'identity_id' => $request->identity_id,
            'problemset_id' => $contest->problemset_id,
            'time' => $request->last_update,
            'admin_id' => intval($r->user->user_id),
            'accepted' => $request->accepted,
        ]));

        self::$log->info(
            'Arbitrated contest for user, new accepted username='
            . $targetIdentity->username . ', state=' . $resolution
        );

        return ['status' => 'ok'];
    }

    /**
     * Returns ALL identities participating in a contest
     *
     * @return array{users: list<array{access_time: int|null, country_id: null|string, end_time: int|null, is_owner: int|null, username: string}>, groups: list<array{alias: string, name: string}>}
     */
    public static function apiUsers(\OmegaUp\Request $r): array {
        // Authenticate request
        $r->ensureIdentity();

        \OmegaUp\Validators::validateStringNonEmpty(
            $r['contest_alias'],
            'contest_alias'
        );
        $contest = self::validateContestAdmin(
            $r['contest_alias'],
            $r->identity
        );

        return [
            'users' => \OmegaUp\DAO\ProblemsetIdentities::getWithExtraInformation(
                intval($contest->problemset_id)
            ),
            'groups' => \OmegaUp\DAO\GroupRoles::getContestantGroups(
                intval($contest->problemset_id)
            ),
        ];
    }

    /**
     * Returns all contest administrators
     *
     * @return array{admins: list<array{role: string, username: string}>, group_admins: list<array{alias: string, name: string, role: string}>}
     */
    public static function apiAdmins(\OmegaUp\Request $r): array {
        // Authenticate request
        $r->ensureIdentity();

        \OmegaUp\Validators::validateStringNonEmpty(
            $r['contest_alias'],
            'contest_alias'
        );

        $contest = self::validateContestAdmin(
            $r['contest_alias'],
            $r->identity
        );

        return [
            'admins' => \OmegaUp\DAO\UserRoles::getContestAdmins($contest),
            'group_admins' => \OmegaUp\DAO\GroupRoles::getContestAdmins(
                $contest
            )
        ];
    }

    /**
     * Enforces rules to avoid having invalid/unactionable public contests
     */
    private static function validateContestCanBePublic(\OmegaUp\DAO\VO\Contests $contest): void {
        $problemset = \OmegaUp\DAO\Problemsets::getByPK(
            intval($contest->problemset_id)
        );
        if (is_null($problemset)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'problemsetNotFound'
            );
        }
        // Check that contest has some problems at least 1 problem
        $problemsInProblemset = \OmegaUp\DAO\ProblemsetProblems::getRelevantProblems(
            $problemset
        );
        if (count($problemsInProblemset) < 1) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'contestPublicRequiresProblem'
            );
        }
    }

    /**
     * Update a Contest
     *
     * @return array{status: string}
     */
    public static function apiUpdate(\OmegaUp\Request $r): array {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        // Authenticate request
        $r->ensureMainUserIdentity();

        // Validate request
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['contest_alias'],
            'contest_alias'
        );
        $contest = self::validateUpdate($r, $r->identity, $r['contest_alias']);
        \OmegaUp\Validators::validateOptionalInEnum(
            $r['requests_user_information'],
            'requests_user_information',
            [
                'no',
                'optional',
                'required',
            ]
        );
        $r->ensureBool('basic_information', /*$required=*/ false);

        self::forbiddenInVirtual($contest);

        $updateProblemset = true;
        // Update contest DAO
        if (!is_null($r['admission_mode'])) {
            \OmegaUp\Validators::validateOptionalInEnum(
                $r['admission_mode'],
                'admission_mode',
                [
                    'public',
                    'private',
                    'registration',
                ]
            );
            // If going public
            if (self::isPublic(strval($r['admission_mode']))) {
                self::validateContestCanBePublic($contest);
            }

            $contest->admission_mode = $r['admission_mode'];
            // Problemset does not update when admission mode change
            $updateProblemset = false;
        }

        $valueProperties = [
            'title',
            'description',
            'start_time',
            'finish_time',
            'window_length' => ['transform' => function (?int $value): ?int {
                return empty($value) ? null : $value;
            }],
            'scoreboard',
            'points_decay_factor',
            'partial_score',
            'submissions_gap',
            'feedback',
            'penalty' => ['transform' => function (string $value): int {
                return max(0, intval($value));
            }],
            'penalty_type',
            'penalty_calc_policy',
            'show_scoreboard_after' => [
                'transform' => function (string $value): bool {
                    return filter_var($value, FILTER_VALIDATE_BOOLEAN);
                }
            ],
            'languages' => [
                'transform' =>
                    /** @param list<string>|string $value */
                    function ($value): string {
                        if (!is_array($value)) {
                            return $value;
                        }
                        return join(',', $value);
                    }
            ],
            'admission_mode',
        ];
        self::updateValueProperties($r, $contest, $valueProperties);

        $originalContest = \OmegaUp\DAO\Contests::getByPK(
            intval($contest->contest_id)
        );
        if (is_null($originalContest)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'contestNotFound'
            );
        }

        // Push changes
        try {
            // Begin a new transaction
            \OmegaUp\DAO\DAO::transBegin();

            // Save the contest object with data sent by user to the database
            self::updateContest($contest, $originalContest, $r->identity);

            if ($updateProblemset) {
                // Save the problemset object with data sent by user to the database
                $problemset = \OmegaUp\DAO\Problemsets::getByPK(
                    intval($contest->problemset_id)
                );
                if (is_null($problemset)) {
                    throw new \OmegaUp\Exceptions\NotFoundException(
                        'problemsetNotFound'
                    );
                }
                $problemset->needs_basic_information = boolval(
                    $r['basic_information']
                );
                $problemset->requests_user_information = $r['requests_user_information'] ?? 'no';
                \OmegaUp\DAO\Problemsets::update($problemset);
            }

            // End transaction
            \OmegaUp\DAO\DAO::transEnd();
        } catch (\Exception $e) {
            // Operation failed in the data layer, rollback transaction
            \OmegaUp\DAO\DAO::transRollback();

            throw $e;
        }

        // Expire contest-info cache
        \OmegaUp\Cache::deleteFromCache(
            \OmegaUp\Cache::CONTEST_INFO,
            $r['contest_alias']
        );

        // Expire contest scoreboard cache
        \OmegaUp\Scoreboard::invalidateScoreboardCache(
            \OmegaUp\ScoreboardParams::fromContest(
                $contest
            )
        );

        // Expire contest-list cache
        \OmegaUp\Cache::invalidateAllKeys(\OmegaUp\Cache::CONTESTS_LIST_PUBLIC);
        \OmegaUp\Cache::invalidateAllKeys(
            \OmegaUp\Cache::CONTESTS_LIST_SYSTEM_ADMIN
        );

        // Happy ending
        $response = [];
        $response['status'] = 'ok';

        self::$log->info('Contest updated (alias): ' . $r['contest_alias']);

        return $response;
    }

    /**
     * Update Contest end time for an identity when window_length
     * option is turned on
     *
     * @throws \OmegaUp\Exceptions\NotFoundException
     *
     * @return array{status: string}
     */
    public static function apiUpdateEndTimeForIdentity(\OmegaUp\Request $r): array {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        $r->ensureMainUserIdentity();
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['contest_alias'],
            'contest_alias'
        );
        \OmegaUp\Validators::validateNumber($r['end_time'], 'end_time');
        $contest = self::validateContestAdmin(
            $r['contest_alias'],
            $r->identity
        );

        \OmegaUp\Validators::validateStringNonEmpty($r['username'], 'username');
        $r->ensureInt('end_time');

        $identity = \OmegaUp\Controllers\Identity::resolveIdentity(
            $r['username']
        );

        $problemsetIdentity = \OmegaUp\DAO\ProblemsetIdentities::getByPK(
            $identity->identity_id,
            $contest->problemset_id
        );
        if (is_null($problemsetIdentity)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'problemsetIdentityNotFound'
            );
        }
        $problemsetIdentity->end_time = intval($r['end_time']);
        \OmegaUp\DAO\ProblemsetIdentities::update($problemsetIdentity);

        return [
            'status' => 'ok',
        ];
    }

    /**
     * This function reviews changes in penalty type, admission mode, finish
     * time and window length to recalcualte information previously stored
     */
    private static function updateContest(
        \OmegaUp\DAO\VO\Contests $contest,
        \OmegaUp\DAO\VO\Contests $originalContest,
        \OmegaUp\DAO\VO\Identities $identity
    ): void {
        if ($originalContest->admission_mode !== $contest->admission_mode) {
            $timestamp = \OmegaUp\Time::get();
            \OmegaUp\DAO\ContestLog::create(new \OmegaUp\DAO\VO\ContestLog([
                'contest_id' => $contest->contest_id,
                'user_id' => $identity->user_id,
                'from_admission_mode' => $originalContest->admission_mode,
                'to_admission_mode' => $contest->admission_mode,
                'time' => $timestamp,
            ]));
            $contest->last_updated = $timestamp;
        }
        if (
            ($originalContest->finish_time !== $contest->finish_time) ||
            ($originalContest->window_length !== $contest->window_length)
        ) {
            if (!is_null($contest->window_length)) {
                // When window length is enabled, end time value is access time + window length
                \OmegaUp\DAO\ProblemsetIdentities::recalculateEndTimeForProblemsetIdentities(
                    $contest
                );
            } else {
                \OmegaUp\DAO\ProblemsetIdentities::recalculateEndTimeAsFinishTime(
                    $contest
                );
            }
        }

        \OmegaUp\DAO\Contests::update($contest);
        if ($originalContest->penalty_type == $contest->penalty_type) {
            return;
        }
        \OmegaUp\DAO\Runs::recalculatePenaltyForContest($contest);
    }

    /**
     * Validates runs API
     *
     * @throws \OmegaUp\Exceptions\NotFoundException
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return array{0: \OmegaUp\DAO\VO\Contests, 1: \OmegaUp\DAO\VO\Problems|null, 2: \OmegaUp\DAO\VO\Identities|null}
     */
    private static function validateRuns(\OmegaUp\Request $r): array {
        $r->ensureIdentity();
        // Defaults for offset and rowcount
        if (!isset($r['offset'])) {
            $r['offset'] = 0;
        }
        if (!isset($r['rowcount'])) {
            $r['rowcount'] = 100;
        }

        \OmegaUp\Validators::validateStringNonEmpty(
            $r['contest_alias'],
            'contest_alias'
        );
        \OmegaUp\Validators::validateOptionalStringNonEmpty(
            $r['username'],
            'username'
        );

        $contest = self::validateContestAdmin(
            $r['contest_alias'],
            $r->identity
        );

        $r->ensureInt('offset', null, null, false);
        $r->ensureInt('rowcount', null, null, false);
        \OmegaUp\Validators::validateOptionalInEnum(
            $r['status'],
            'status',
            ['new', 'waiting', 'compiling', 'running', 'ready']
        );
        \OmegaUp\Validators::validateOptionalInEnum(
            $r['verdict'],
            'verdict',
            \OmegaUp\Controllers\Run::VERDICTS
        );

        $problem = null;
        // Check filter by problem, is optional
        if (!is_null($r['problem_alias'])) {
            \OmegaUp\Validators::validateStringNonEmpty(
                $r['problem_alias'],
                'problem'
            );

            $problem = \OmegaUp\DAO\Problems::getByAlias($r['problem_alias']);
            if (is_null($problem)) {
                throw new \OmegaUp\Exceptions\NotFoundException(
                    'problemNotFound'
                );
            }
        }

        \OmegaUp\Validators::validateOptionalInEnum(
            $r['language'],
            'language',
            array_keys(\OmegaUp\Controllers\Run::SUPPORTED_LANGUAGES)
        );

        // Get user if we have something in username
        $identity = null;
        if (!is_null($r['username'])) {
            $identity = \OmegaUp\Controllers\Identity::resolveIdentity(
                $r['username']
            );
        }
        return [$contest, $problem, $identity];
    }

    /**
     * Returns all runs for a contest
     *
     * @return array{runs: list<array{run_id: int, guid: string, language: string, status: string, verdict: string, runtime: int, penalty: int, memory: int, score: float, contest_score: float, judged_by: null|string, time: int, submit_delay: int, type: null|string, username: string, alias: string, country_id: null|string, contest_alias: null|string}>}
     */
    public static function apiRuns(\OmegaUp\Request $r): array {
        // Authenticate request
        $r->ensureIdentity();
        \OmegaUp\Validators::validateOptionalInEnum(
            $r['status'],
            'status',
            \OmegaUp\Controllers\Run::STATUS
        );
        \OmegaUp\Validators::validateOptionalInEnum(
            $r['verdict'],
            'verdict',
            \OmegaUp\Controllers\Run::VERDICTS
        );
        \OmegaUp\Validators::validateOptionalInEnum(
            $r['language'],
            'language',
            array_keys(\OmegaUp\Controllers\Run::SUPPORTED_LANGUAGES)
        );
        \OmegaUp\Validators::validateOptionalNumber($r['offset'], 'offset');
        \OmegaUp\Validators::validateOptionalNumber($r['rowcount'], 'rowcount');

        // Validate request
        [$contest, $problem, $identity] = self::validateRuns($r);

        // Get our runs
        $runs = \OmegaUp\DAO\Runs::getAllRuns(
            $contest->problemset_id,
            $r['status'],
            $r['verdict'],
            !is_null($problem) ? $problem->problem_id : null,
            $r['language'],
            !is_null($identity) ? $identity->identity_id : null,
            intval($r['offset']),
            intval($r['rowcount'])
        );

        $result = [];

        foreach ($runs as $run) {
            $run['time'] = intval($run['time']);
            $run['score'] = round(floatval($run['score']), 4);
            $run['contest_score'] = round(floatval($run['contest_score']), 2);
            $result[] = $run;
        }

        return [
            'runs' => $result,
        ];
    }

    /**
     * Validates that request contains contest_alias and the api is contest-admin only
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     */
    private static function validateStats(
        ?string $contestAlias,
        \OmegaUp\DAO\VO\Identities $identity
    ): \OmegaUp\DAO\VO\Contests {
        \OmegaUp\Validators::validateStringNonEmpty(
            $contestAlias,
            'contest_alias'
        );

        return self::validateContestAdmin($contestAlias, $identity);
    }

    /**
     * Stats of a contest
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return array{total_runs: int, pending_runs: list<string>, max_wait_time: int, max_wait_time_guid: null|string, verdict_counts: array<string, int>, distribution: array<int, int>, size_of_bucket: float, total_points: float}
     */
    public static function apiStats(\OmegaUp\Request $r): array {
        // Get user
        $r->ensureIdentity();
        \OmegaUp\Validators::validateValidAlias(
            $r['contest_alias'],
            'contest_alias'
        );
        $contest = self::validateStats($r['contest_alias'], $r->identity);
        return self::getStats($contest, $r->identity);
    }

    /**
     * @return array{smartyProperties: array{payload: array{alias: string, entity_type: string, total_runs: int, pending_runs: list<string>, max_wait_time: int, max_wait_time_guid: null|string, verdict_counts: array<string, int>, distribution: array<int, int>, size_of_bucket: float, total_points: float}}, template: string}
     */
    public static function getStatsDataForSmarty(\OmegaUp\Request $r) {
        // Get user
        $r->ensureIdentity();
        \OmegaUp\Validators::validateValidAlias(
            $r['contest_alias'],
            'contest_alias'
        );
        $contest = self::validateStats($r['contest_alias'], $r->identity);
        return [
            'smartyProperties' => [
                'payload' => array_merge(
                    [
                        'alias' => $r['contest_alias'],
                        'entity_type' => 'contest',
                    ],
                    self::getStats($contest, $r->identity)
                ),
            ],
            'template' => 'contest.stats.tpl',
        ];
    }

    /**
     * @return array{total_runs: int, pending_runs: list<string>, max_wait_time: int, max_wait_time_guid: null|string, verdict_counts: array<string, int>, distribution: array<int, int>, size_of_bucket: float, total_points: float}
     */
    private static function getStats(
        \OmegaUp\DAO\VO\Contests $contest,
        \OmegaUp\DAO\VO\Identities $identity
    ) {
        $pendingRunGuids = \OmegaUp\DAO\Runs::getPendingRunGuidsOfProblemset(
            intval(
                $contest->problemset_id
            )
        );

        // Count of pending runs (int)
        $totalRunsCount = \OmegaUp\DAO\Submissions::countTotalSubmissionsOfProblemset(
            intval($contest->problemset_id)
        );

        // Wait time
        $waitTimeArray = \OmegaUp\DAO\Runs::getLargestWaitTimeOfProblemset(
            intval(
                $contest->problemset_id
            )
        );

        // List of verdicts
        $verdictCounts = [];

        foreach (\OmegaUp\Controllers\Run::VERDICTS as $verdict) {
            $verdictCounts[$verdict] = \OmegaUp\DAO\Runs::countTotalRunsOfProblemsetByVerdict(
                intval($contest->problemset_id),
                $verdict
            );
        }

        // Get max points posible for contest
        $totalPoints = \OmegaUp\DAO\ProblemsetProblems::getMaxPointsByProblemset(
            intval($contest->problemset_id)
        );

        // Get scoreboard to calculate distribution
        $distribution = [];
        for ($i = 0; $i < 101; $i++) {
            $distribution[$i] = 0;
        }

        $sizeOfBucket = $totalPoints / 100;
        if ($sizeOfBucket > 0) {
            $problemset = \OmegaUp\DAO\Problemsets::getByPK(
                intval($contest->problemset_id)
            );
            if (is_null($problemset)) {
                throw new \OmegaUp\Exceptions\NotFoundException(
                    'problemsetNotFound'
                );
            }
            $scoreboardResponse = self::getScoreboard(
                $contest,
                $problemset,
                $identity
            );
            foreach ($scoreboardResponse['ranking'] as $results) {
                $distribution[intval(
                    $results['total']['points'] / $sizeOfBucket
                )]++;
            }
        }

        return [
            'total_runs' => $totalRunsCount,
            'pending_runs' => $pendingRunGuids,
            'max_wait_time' => empty(
                $waitTimeArray
            ) ? 0 : $waitTimeArray['time'],
            'max_wait_time_guid' => empty(
                $waitTimeArray
            ) ? null : $waitTimeArray['guid'],
            'verdict_counts' => $verdictCounts,
            'distribution' => $distribution,
            'size_of_bucket' => $sizeOfBucket,
            'total_points' => $totalPoints,
        ];
    }

    /**
     * Returns a detailed report of the contest
     *
     * @return array{finish_time: int|null, problems: list<array{alias: string, order: int}>, ranking: list<array{country: null|string, is_invited: bool, name: null|string, place?: int, problems: list<array{alias: string, penalty: float, percent: float, place?: int, points: float, run_details?: array{cases?: list<array{contest_score: float, max_score: float, meta: array{status: string}, name: null|string, out_diff: string, score: float, verdict: string}>, details: array{groups: list<array{cases: list<array{meta: array{memory: float, time: float, wall_time: float}}>}>}}, runs: int}>, total: array{penalty: float, points: float}, username: string}>, start_time: int, time: int, title: string}
     */
    public static function apiReport(\OmegaUp\Request $r): array {
        return self::getContestReportDetails($r);
    }

    /**
     * Returns a detailed report of the contest. Only Admins can get the report
     *
     * @return array{finish_time: int|null, problems: list<array{alias: string, order: int}>, ranking: list<array{country: null|string, is_invited: bool, name: string|null, place?: int, problems: list<array{alias: string, penalty: float, percent: float, place?: int, points: float, run_details?: array{cases?: list<array{contest_score: float, max_score: float, meta: array{status: string}, name: string|null, out_diff: string, score: float, verdict: string}>, details: array{groups: list<array{cases: list<array{meta: array{memory: float, time: float, wall_time: float}}>}>}}, runs: int}>, total: array{penalty: float, points: float}, username: string}>, start_time: int, time: int, title: string}
     */
    private static function getContestReportDetails(\OmegaUp\Request $r): array {
        $r->ensureIdentity();
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['contest_alias'],
            'contest_alias'
        );
        \OmegaUp\Validators::validateOptionalStringNonEmpty(
            $r['auth_token'],
            'auth_token'
        );

        $contest = self::validateStats($r['contest_alias'], $r->identity);

        $params = \OmegaUp\ScoreboardParams::fromContest($contest);
        $params->admin = true;
        $params->auth_token = $r['auth_token'];
        $scoreboard = new \OmegaUp\Scoreboard($params);

        // Check the filter if we have one
        \OmegaUp\Validators::validateOptionalStringNonEmpty(
            $r['filterBy'],
            'filterBy'
        );

        return $scoreboard->generate(
            true, // with run details for reporting
            true, // sort contestants by name,
            $r['filterBy']
        );
    }

    /**
     * Gets all details to show the report
     *
     * @return array{contestReport: list<array{country: null|string, is_invited: bool, name: null|string, place?: int, problems: list<array{alias: string, penalty: float, percent: float, place?: int, points: float, run_details?: array{cases?: list<array{contest_score: float, max_score: float, meta: array{status: string}, name: null|string, out_diff: string, score: float, verdict: string}>, details: array{groups: list<array{cases: list<array{meta: array{memory: float, time: float, wall_time: float}}>}>}}, runs: int}>, total: array{penalty: float, points: float}, username: string}>}
     */
    public static function getContestReportDetailsForSmarty(\OmegaUp\Request $r) {
        $contestReport = self::getContestReportDetails($r)['ranking'];
        foreach ($contestReport as &$user) {
            if (!isset($user['problems'])) {
                continue;
            }
            foreach ($user['problems'] as &$problem) {
                if (
                    !isset($problem['run_details']) ||
                    !isset($problem['run_details']['details']) ||
                    !isset($problem['run_details']['details']['groups'])
                ) {
                    continue;
                }
                foreach ($problem['run_details']['details']['groups'] as &$group) {
                    foreach ($group['cases'] as &$case) {
                        $case['meta']['memory'] =
                            floatval($case['meta']['memory']) / 1024.0 / 1024.0;
                    }
                }
            }
        }

        return [
            'contestReport' => $contestReport,
        ];
    }

    /**
     * Generates a CSV for contest report
     */
    public static function apiCsvReport(\OmegaUp\Request $r): void {
        $r->ensureIdentity();

        \OmegaUp\Validators::validateStringNonEmpty(
            $r['contest_alias'],
            'contest_alias'
        );
        $contest = self::validateStats($r['contest_alias'], $r->identity);

        // Get full Report API of the contest
        $contestReport = self::getContestReportDetails(new \OmegaUp\Request([
            'contest_alias' => $r['contest_alias'],
            'auth_token' => $r['auth_token'],
        ]));

        // Get problem stats for each contest problem so we can
        // have the full list of cases
        $problemStats = [];
        $problemAliasStats = [];
        $i = 0;
        foreach ($contestReport['problems'] as $entry) {
            $problemAlias = $entry['alias'];
            $problem = \OmegaUp\DAO\Problems::getByAlias($problemAlias);
            if (is_null($problem)) {
                throw new \OmegaUp\Exceptions\NotFoundException(
                    'problemNotFound'
                );
            }

            $problemStats[$i] = \OmegaUp\Controllers\Problem::getStats(
                $problem,
                $r->identity
            );
            $problemAliasStats[$problemAlias] = $problemStats[$i];

            $i++;
        }

        // Build a csv
        /** @var string[][] */
        $csvData = [];

        // Build titles
        $csvRow = [
            'username',
        ];
        foreach ($contestReport['problems'] as $entry) {
            foreach ($problemAliasStats[$entry['alias']]['cases_stats'] as $caseName => $counts) {
                $csvRow[] = strval($caseName);
            }
            $csvRow[] = "{$entry['alias']} total";
        }
        $csvRow[] = 'total';
        $csvData[] = $csvRow;

        foreach ($contestReport['ranking'] as $userData) {
            $csvRow = [
                $userData['username'],
            ];

            foreach ($userData['problems'] as $key => $problemData) {
                // If the user don't have these details then he didn't submit,
                // we need to fill the report with 0s for completeness
                if (
                    empty($problemData['run_details']['cases'])
                ) {
                    for (
                        $i = 0; $i < count(
                            $problemStats[$key]['cases_stats']
                        ); $i++
                    ) {
                        $csvRow[] = '0';
                    }

                    // And adding the total for this problem
                    $csvRow[] = '0';
                } else {
                    // for each case
                    foreach ($problemData['run_details']['cases'] as $caseData) {
                        // If case is correct
                        if (
                            strcmp($caseData['meta']['status'], 'OK') === 0 &&
                            strcmp($caseData['out_diff'], '') === 0
                        ) {
                            $csvRow[] = '1';
                        } else {
                            $csvRow[] = '0';
                        }
                    }

                    $csvRow[] = $problemData['points'];
                }
            }
            $csvRow[] = $userData['total']['points'];
            $csvData[] = $csvRow;
        }

        // Set headers to auto-download file
        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Type: application/force-download');
        header('Content-Type: application/octet-stream');
        header('Content-Type: application/download');
        header(
            "Content-Disposition: attachment;filename={$contest->alias}_report.csv"
        );
        header('Content-Transfer-Encoding: binary');

        // Write contents to a csv raw string
        $out = fopen('php://output', 'w');
        foreach ($csvData as $csvRow) {
            fputcsv($out, \OmegaUp\Controllers\Contest::escapeCsv($csvRow));
        }
        fclose($out);

        // X_X
        die();
    }

    /**
     * @param mixed[] $csvRow
     *
     * @return list<string>
     */
    private static function escapeCsv($csvRow): array {
        $escapedRow = [];
        /** @var mixed $field */
        foreach ($csvRow as $field) {
            if (is_string($field) && $field[0] == '=') {
                $escapedRow[] = "'" . $field;
            } else {
                $escapedRow[] = strval($field);
            }
        }
        return $escapedRow;
    }

    public static function apiDownload(\OmegaUp\Request $r): void {
        $r->ensureIdentity();

        $contest = self::validateStats(
            strval(
                $r['contest_alias']
            ),
            $r->identity
        );
        if (is_null($contest->problemset_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('contestNotFound');
        }
        include_once 'libs/third_party/ZipStream.php';
        $zip = new \ZipStream("{$r['contest_alias']}.zip");
        \OmegaUp\Controllers\Problemset::downloadRuns(
            $contest->problemset_id,
            $zip
        );
        $zip->finish();

        die();
    }

    /**
     * Given a contest_alias and user_id, returns the role of the user within
     * the context of a contest.
     *
     * @return array{admin: bool}
     */
    public static function apiRole(\OmegaUp\Request $r): array {
        if ($r['contest_alias'] == 'all-events') {
            $r->ensureIdentity();
            if (\OmegaUp\Authorization::isSystemAdmin($r->identity)) {
                return [
                    'status' => 'ok',
                    'admin' => true
                ];
            }
        }

        $response = self::validateDetails($r);

        return [
            'admin' => $response['contest_admin']
        ];
    }

    /**
     * Given a contest_alias, sets the recommended flag on/off.
     * Only omegaUp admins can call this API.
     *
     * @return array{status: string}
     */
    public static function apiSetRecommended(\OmegaUp\Request $r): array {
        $r->ensureIdentity();

        if (!\OmegaUp\Authorization::isSystemAdmin($r->identity)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'userNotAllowed'
            );
        }

        // Validate & get contest_alias
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['contest_alias'],
            'contest_alias'
        );
        $contest = \OmegaUp\DAO\Contests::getByAlias($r['contest_alias']);
        if (is_null($contest)) {
            throw new \OmegaUp\Exceptions\NotFoundException('contestNotFound');
        }

        // Validate value param
        $r->ensureBool('value');

        $contest->recommended = boolval($r['value']);
        \OmegaUp\DAO\Contests::update($contest);

        return ['status' => 'ok'];
    }

    /**
     * Return users who participate in a contest, as long as contest admin
     * has chosen to ask for users information and contestants have
     * previously agreed to share their information.
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return array{contestants: list<array{name: null|string, username: string, email: null|string, state: null|string, country: null|string, school: null|string}>}
     */
    public static function apiContestants(\OmegaUp\Request $r): array {
        $r->ensureIdentity();

        \OmegaUp\Validators::validateStringNonEmpty(
            $r['contest_alias'],
            'contest_alias'
        );
        $contest = self::validateStats($r['contest_alias'], $r->identity);

        if (is_null($contest->contest_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('contestNotFound');
        }
        if (
            !\OmegaUp\DAO\Contests::requestsUserInformation(
                $contest->contest_id
            )
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'contestInformationNotRequired'
            );
        }

        // Get contestants info
        /** @var int $contest->contest_id */
        $contestants = \OmegaUp\DAO\Contests::getContestantsInfo(
            $contest->contest_id
        );

        return [
            'status' => 'ok',
            'contestants' => $contestants,
        ];
    }

    public static function isPublic(string $admissionMode): bool {
        return $admissionMode !== 'private';
    }
}
