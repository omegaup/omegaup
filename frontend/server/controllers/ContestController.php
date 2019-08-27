<?php

require_once 'libs/ActivityReport.php';
require_once 'libs/PrivacyStatement.php';
require_once 'libs/dao/Contests.dao.php';

/**
 * ContestController
 *
 */
class ContestController extends Controller {
    const SHOW_INTRO = true;
    const MAX_CONTEST_LENGTH_SECONDS = 2678400; // 31 days

    /**
     * Returns a list of contests
     *
     * @param \OmegaUp\Request $r
     * @return array
     */
    public static function apiList(\OmegaUp\Request $r) {
        // Check who is visiting, but a not logged user can still view
        // the list of contests
        try {
            self::authenticateRequest($r);
        } catch (\OmegaUp\Exceptions\UnauthorizedException $e) {
            // Do nothing.
        }

        $contests = [];
        $r->ensureInt('page', null, null, false);
        $r->ensureInt('page_size', null, null, false);

        $page = (isset($r['page']) ? intval($r['page']) : 1);
        $page_size = (isset($r['page_size']) ? intval($r['page_size']) : 20);
        $active_contests = isset($r['active'])
            ? ActiveStatus::getIntValue($r['active'])
            : ActiveStatus::ALL;
        // If the parameter was not set, the default should be ALL which is
        // a number and should pass this check.
        \OmegaUp\Validators::validateNumber($active_contests, 'active', true /* required */);
        $recommended = isset($r['recommended'])
            ? RecommendedStatus::getIntValue($r['recommended'])
            : RecommendedStatus::ALL;
        // Same as above.
        \OmegaUp\Validators::validateNumber($recommended, 'recommended', true /* required */);
        $participating = isset($r['participating'])
            ? ParticipatingStatus::getIntValue($r['participating'])
            : ParticipatingStatus::NO;
        \OmegaUp\Validators::validateInEnum($r['admission_mode'], 'admission_mode', [
            'public',
            'private',
            'registration'
        ], false);

        // admission mode status in contest is public
        $public = isset($r['admission_mode']) && self::isPublic($r['admission_mode']);

        if (is_null($participating)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException('parameterInvalid', 'participating');
        }
        $query = $r['query'];
        \OmegaUp\Validators::validateStringOfLengthInRange($query, 'query', null, 255, false /* not required */);
        $cacheKey = "{$active_contests}-{$recommended}-{$page}-{$page_size}";
        if (is_null($r->identity)) {
            // Get all public contests
            $contests = \OmegaUp\Cache::getFromCacheOrSet(
                \OmegaUp\Cache::CONTESTS_LIST_PUBLIC,
                $cacheKey,
                function () use ($page, $page_size, $active_contests, $recommended, $query) {
                    return ContestsDAO::getAllPublicContests(
                        $page,
                        $page_size,
                        $active_contests,
                        $recommended,
                        $query
                    );
                }
            );
        } elseif ($participating == ParticipatingStatus::YES) {
            $contests = ContestsDAO::getContestsParticipating($r->identity->identity_id, $page, $page_size, $query);
        } elseif ($public) {
            $contests = ContestsDAO::getRecentPublicContests($r->identity->identity_id, $page, $page_size, $query);
        } elseif (\OmegaUp\Authorization::isSystemAdmin($r->identity)) {
            // Get all contests
            $contests = \OmegaUp\Cache::getFromCacheOrSet(
                \OmegaUp\Cache::CONTESTS_LIST_SYSTEM_ADMIN,
                $cacheKey,
                function () use ($page, $page_size, $active_contests, $recommended, $query) {
                        return ContestsDAO::getAllContests($page, $page_size, $active_contests, $recommended, $query);
                }
            );
        } else {
            // Get all public+private contests
            $contests = ContestsDAO::getAllContestsForIdentity($r->identity->identity_id, $page, $page_size, $active_contests, $recommended, $query);
        }

        // Filter returned values by these columns
        $relevantColumns = [
            'contest_id',
            'problemset_id',
            'title',
            'description',
            'start_time',
            'finish_time',
            'admission_mode',
            'alias',
            'window_length',
            'recommended',
            'last_updated',
            'rerun_id'
            ];

        $addedContests = [];
        foreach ($contests as $contestInfo) {
            $contestInfo['duration'] = (is_null($contestInfo['window_length']) ?
                            $contestInfo['finish_time'] - $contestInfo['start_time'] :
                            ($contestInfo['window_length'] * 60));

            $addedContests[] = $contestInfo;
        }

        return [
            'number_of_results' => sizeof($addedContests),
            'results' => $addedContests
        ];
    }

    /**
     * Returns a list of contests where current user has admin rights (or is
     * the director).
     *
     * @param \OmegaUp\Request $r
     * @return array
     */
    public static function apiAdminList(\OmegaUp\Request $r) {
        self::authenticateRequest($r);

        $r->ensureInt('page', null, null, false);
        $r->ensureInt('page_size', null, null, false);

        $page = (isset($r['page']) ? intval($r['page']) : 1);
        $pageSize = (isset($r['page_size']) ? intval($r['page_size']) : 1000);

        // Create array of relevant columns
        $contests = null;
        if (\OmegaUp\Authorization::isSystemAdmin($r->identity)) {
            $contests = ContestsDAO::getAllContestsWithScoreboard(
                $page,
                $pageSize,
                'contest_id',
                'DESC'
            );
        } else {
            $contests = ContestsDAO::getAllContestsAdminedByIdentity(
                $r->identity->identity_id,
                $page,
                $pageSize
            );
        }

        return [
            'status' => 'ok',
            'contests' => $contests,
        ];
    }

    /**
     * Callback to get contests list, depending on a given method
     * @param \OmegaUp\Request $r
     * @param $callback_user_function
     * @return array
     */
    private static function getContestListInternal(\OmegaUp\Request $r, $callback_user_function) : Array {
        $r->ensureInt('page', null, null, false);
        $r->ensureInt('page_size', null, null, false);

        $page = (isset($r['page']) ? intval($r['page']) : 1);
        $pageSize = (isset($r['page_size']) ? intval($r['page_size']) : 1000);
        $query = $r['query'];
        $contests = null;
        $identity_id = $callback_user_function == 'ContestsDAO::getContestsParticipating'
          ? $r->identity->identity_id : $r->user->user_id;
        $contests = call_user_func(
            $callback_user_function,
            $identity_id,
            $page,
            $pageSize,
            $query
        );

        $addedContests = [];
        foreach ($contests as $contest) {
            $contest['start_time'] = \OmegaUp\DAO\DAO::fromMySQLTimestamp($contest['start_time']);
            $contest['finish_time'] = \OmegaUp\DAO\DAO::fromMySQLTimestamp($contest['finish_time']);
            $contest['last_updated'] = \OmegaUp\DAO\DAO::fromMySQLTimestamp($contest['last_updated']);
            $addedContests[] = $contest;
        }

        // Expire contest-list cache
        \OmegaUp\Cache::invalidateAllKeys(\OmegaUp\Cache::CONTESTS_LIST_PUBLIC);
        \OmegaUp\Cache::invalidateAllKeys(\OmegaUp\Cache::CONTESTS_LIST_SYSTEM_ADMIN);

        return [
            'status' => 'ok',
            'contests' => $addedContests,
        ];
    }

    /**
     * Returns a list of contests where current user is the director
     *
     * @param \OmegaUp\Request $r
     * @return array
     */
    public static function apiMyList(\OmegaUp\Request $r) {
        self::authenticateRequest($r, true /* requireMainUserIdentity */);
        return self::getContestListInternal($r, 'ContestsDAO::getAllContestsOwnedByUser');
    }

    /**
     * Returns a list of contests where current user is participating in
     *
     * @param \OmegaUp\Request $r
     * @return array
     */
    public static function apiListParticipating(\OmegaUp\Request $r) {
        self::authenticateRequest($r);
        return self::getContestListInternal($r, 'ContestsDAO::getContestsParticipating');
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
     * @param \OmegaUp\DAO\VO\Contests $contest
     * @param \OmegaUp\DAO\VO\Identities $identity
     * @throws \OmegaUp\Exceptions\ApiException
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     */
    private static function canAccessContest(
        \OmegaUp\DAO\VO\Contests $contest,
        \OmegaUp\DAO\VO\Identities $identity
    ) : void {
        if ($contest->admission_mode == 'private') {
            if (is_null(ProblemsetIdentitiesDAO::getByPK(
                $identity->identity_id,
                $contest->problemset_id
            )) && !\OmegaUp\Authorization::isContestAdmin($identity, $contest)
            ) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException('userNotAllowed');
            }
        } elseif ($contest->admission_mode == 'registration' &&
            !\OmegaUp\Authorization::isContestAdmin($identity, $contest)
        ) {
            $req = ProblemsetIdentityRequestDAO::getByPK(
                $identity->identity_id,
                $contest->problemset_id
            );
            if (is_null($req) || $req->accepted === '0') {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException('contestNotRegistered');
            }
        }
    }

    /**
     * Validate the basics of a contest request.
     *
     * @param string $contestAlias
     * @return [Contests, Problemsets]
     * @throws \OmegaUp\Exceptions\NotFoundException
     */
    private static function validateBasicDetails(?string $contestAlias) : array {
        \OmegaUp\Validators::validateStringNonEmpty($contestAlias, 'contest_alias');
        // If the contest is private, verify that our user is invited
        $contestProblemset = ContestsDAO::getByAliasWithExtraInformation($contestAlias);
        if (is_null($contestProblemset)) {
            throw new \OmegaUp\Exceptions\NotFoundException('contestNotFound');
        }
        return [
            new \OmegaUp\DAO\VO\Contests(
                array_intersect_key($contestProblemset, \OmegaUp\DAO\VO\Contests::FIELD_NAMES)
            ),
            new \OmegaUp\DAO\VO\Problemsets(
                array_intersect_key($contestProblemset, \OmegaUp\DAO\VO\Problemsets::FIELD_NAMES)
            ),
        ];
    }

    /**
     * Validate a contest with contest alias
     *
     * @param string $contestAlias
     * @return \OmegaUp\DAO\VO\Contests $contest
     * @throws \OmegaUp\Exceptions\NotFoundException
     */
    public static function validateContest(string $contestAlias) : \OmegaUp\DAO\VO\Contests {
        \OmegaUp\Validators::validateStringNonEmpty($contestAlias, 'contest_alias');
        $contest = ContestsDAO::getByAlias($contestAlias);
        if (is_null($contest)) {
            throw new \OmegaUp\Exceptions\NotFoundException('contestNotFound');
        }
        return $contest;
    }

    /**
     * Validate if a contestant has explicit access to a contest.
     *
     * @param \OmegaUp\DAO\VO\Contests $contest
     * @param \OmegaUp\DAO\VO\Identities $identity
     * @return bool
     */
    private static function isInvitedToContest(
        \OmegaUp\DAO\VO\Contests $contest,
        \OmegaUp\DAO\VO\Identities $identity
    ) : bool {
        if (is_null($identity->user_id)) {
            return false;
        }
        return self::isPublic($contest->admission_mode) ||
            !is_null(ProblemsetIdentitiesDAO::getByPK(
                $identity->identity_id,
                $contest->problemset_id
            ));
    }

    /**
     * Get all the properties for smarty.
     * @param \OmegaUp\Request $r
     * @param \OmegaUp\DAO\VO\Contests $contest
     * @return Array
     */
    public static function getContestDetailsForSmarty(
        \OmegaUp\Request $r,
        \OmegaUp\DAO\VO\Contests $contest,
        bool $shouldShowIntro
    ) : array {
        // Half-authenticate, in case there is no session in place.
        $session = SessionController::apiCurrentSession($r)['session'];
        if (!$shouldShowIntro) {
            return ['payload' => [
                'shouldShowFirstAssociatedIdentityRunWarning' =>
                    !is_null($session['user']) &&
                    !UserController::isMainIdentity(
                        $session['user'],
                        $session['identity']
                    )
                    && ProblemsetsDAO::shouldShowFirstAssociatedIdentityRunWarning(
                        $session['user']
                    ),
            ]];
        }
        $result = [
            'needsBasicInformation' => false,
            'requestsUserInformation' => false,
        ];
        if (!$session['valid'] || is_null($session['identity'])) {
            // No session, show the intro if public, so that they can login.
            return $result;
        }

        [
            'needsBasicInformation' => $result['needsBasicInformation'],
            'requestsUserInformation' => $result['requestsUserInformation'],
        ] = ContestsDAO::getNeedsInformation($contest->problemset_id);
        $identity = $session['identity'];

        $result['needsBasicInformation'] =
            $result['needsBasicInformation'] && (
                !$identity->country_id || !$identity->state_id ||
                !$identity->school_id
        );

        // Privacy Statement Information
        $privacyStatementMarkdown = PrivacyStatement::getForProblemset(
            $identity->language_id,
            'contest',
            $result['requestsUserInformation']
        );
        if (!is_null($privacyStatementMarkdown)) {
            $statementType =
                "contest_{$result['requestsUserInformation']}_consent";
            $result['privacyStatement'] = [
                'markdown' => $privacyStatementMarkdown,
                'gitObjectId' =>
                    PrivacyStatementsDAO::getLatestPublishedStatement(
                        $statementType
                    )['git_object_id'],
                'statementType' => $statementType
            ];
        }

        return $result;
    }

    /**
     * Show the contest intro unless you are admin, or you already started this
     * contest.
     * @param \OmegaUp\Request $r
     * @param \OmegaUp\DAO\VO\Contests $contest
     * @return bool
     */
    public static function shouldShowIntro(
        \OmegaUp\Request $r,
        \OmegaUp\DAO\VO\Contests $contest
    ) : bool {
        try {
            $session = SessionController::apiCurrentSession($r)['session'];
            if (is_null($session['identity'])) {
                // No session, show the intro (if public), so that they can login.
                return self::isPublic($contest->admission_mode);
            }
            self::canAccessContest($contest, $session['identity']);
        } catch (Exception $e) {
            // Could not access contest. Private contests must not be leaked, so
            // unless they were manually added beforehand, show them a 404 error.
            if (!self::isInvitedToContest($contest, $session['identity'])) {
                throw $e;
            }
            self::$log->error('Exception while trying to verify access: ' . $e);
            return ContestController::SHOW_INTRO;
        }

        // You already started the contest.
        $contestOpened = ProblemsetIdentitiesDAO::getByPK(
            $session['identity']->identity_id,
            $contest->problemset_id
        );
        if (!is_null($contestOpened) && !is_null($contestOpened->access_time)) {
            self::$log->debug('No intro because you already started the contest');
            return !ContestController::SHOW_INTRO;
        }
        return ContestController::SHOW_INTRO;
    }

    /**
     * Validate request of a details contest
     *
     * @param \OmegaUp\Request $r
     * @return [$contest, $contestAdmin]
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     * @throws \OmegaUp\Exceptions\PreconditionFailedException
     */
    public static function validateDetails(\OmegaUp\Request $r) : Array {
        [$contest, $problemset] = self::validateBasicDetails($r['contest_alias']);

        $contestAdmin = false;
        $contestAlias = '';

        // If the contest has not started, user should not see it, unless it i
        // admin or has a token.
        if (is_null($r['token'])) {
            // Crack the request to get the current user
            self::authenticateRequest($r);
            self::canAccessContest($contest, $r->identity);

            $contestAdmin = \OmegaUp\Authorization::isContestAdmin($r->identity, $contest);
            if (!ContestsDAO::hasStarted($contest) && !$contestAdmin) {
                $exception = new \OmegaUp\Exceptions\PreconditionFailedException('contestNotStarted');
                $exception->addCustomMessageToArray('start_time', $contest->start_time);

                throw $exception;
            }
        } else {
            if ($r['token'] === $problemset->scoreboard_url_admin) {
                $contestAdmin = true;
                $contestAlias = $contest->alias;
            } elseif ($r['token'] !== $problemset->scoreboard_url) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException('invalidScoreboardUrl');
            }
        }
        return [
            'contest' => $contest,
            'contest_admin' => $contestAdmin,
            'contest_alias' => $contestAlias,
        ];
    }

    public static function apiPublicDetails(\OmegaUp\Request $r) {
        \OmegaUp\Validators::validateStringNonEmpty($r['contest_alias'], 'contest_alias');

        $result = [];

        // If the contest is private, verify that our user is invited
        $r['contest'] = ContestsDAO::getByAlias($r['contest_alias']);
        if (is_null($r['contest'])) {
            throw new \OmegaUp\Exceptions\NotFoundException('contestNotFound');
        }

        // Initialize response to be the contest information
        $result = $r['contest']->asFilteredArray([
            'title',
            'description',
            'start_time',
            'finish_time',
            'window_length',
            'alias',
            'scoreboard',
            'points_decay_factor',
            'partial_score',
            'submissions_gap',
            'feedback',
            'penalty',
            'time_start',
            'penalty_type',
            'penalty_calc_policy',
            'show_scoreboard_after',
            'rerun_id',
            'admission_mode',
        ]);

        $current_ses = SessionController::getCurrentSession($r);

        if ($current_ses['valid'] && $result['admission_mode'] == 'registration') {
            $registration = ProblemsetIdentityRequestDAO::getByPK($current_ses['identity']->identity_id, $r['contest']->problemset_id);

            $result['user_registration_requested'] = !is_null($registration);

            if (is_null($registration)) {
                $result['user_registration_accepted'] = false;
                $result['user_registration_answered'] = false;
            } else {
                $result['user_registration_answered'] = !is_null($registration->accepted);
                $result['user_registration_accepted'] = $registration->accepted == '1';
            }
        }

        $result['start_time'] = \OmegaUp\DAO\DAO::fromMySQLTimestamp($result['start_time']);
        $result['finish_time'] = \OmegaUp\DAO\DAO::fromMySQLTimestamp($result['finish_time']);

        $result['status'] = 'ok';

        return $result;
    }

    public static function apiRegisterForContest(\OmegaUp\Request $r) {
        // Authenticate request
        self::authenticateRequest($r);

        $contest = self::validateContest($r['contest_alias'] ?? '');

        ProblemsetIdentityRequestDAO::create(new \OmegaUp\DAO\VO\ProblemsetIdentityRequest([
            'identity_id' => $r->identity->identity_id,
            'problemset_id' => $contest->problemset_id,
            'request_time' => \OmegaUp\Time::get(),
        ]));

        return ['status' => 'ok'];
    }

    /**
     * Joins a contest - explicitly adds a identity to a contest.
     *
     * @param \OmegaUp\Request $r
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     */
    public static function apiOpen(\OmegaUp\Request $r) {
        $response = self::validateDetails($r);
        [
            'needsBasicInformation' => $needsInformation,
            'requestsUserInformation' => $requestsUserInformation
        ] = ContestsDAO::getNeedsInformation($response['contest']->problemset_id);
        $session = SessionController::apiCurrentSession($r)['session'];

        if ($needsInformation && !is_null($session['identity']) &&
              (!$session['identity']->country_id || !$session['identity']->state_id
                || !$session['identity']->school_id)
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('contestBasicInformationNeeded');
        }

        $r->ensureBool('share_user_information', false);
        \OmegaUp\DAO\DAO::transBegin();
        try {
            ProblemsetIdentitiesDAO::checkAndSaveFirstTimeAccess(
                $r->identity,
                $response['contest'],
                /*$grantAccess=*/true,
                $r['share_user_information'] ?: false
            );

            // Insert into PrivacyStatement_Consent_Log whether request
            // user info is optional or required
            if ($requestsUserInformation != 'no') {
                $privacyStatementId = PrivacyStatementsDAO::getId(
                    $r['privacy_git_object_id'],
                    $r['statement_type']
                );

                $privacyStatementConsentId = PrivacyStatementConsentLogDAO::getId(
                    $r->identity->identity_id,
                    $privacyStatementId
                );
                if (is_null($privacyStatementConsentId)) {
                    $privacyStatementConsentId = PrivacyStatementConsentLogDAO::saveLog(
                        $r->identity->identity_id,
                        $privacyStatementId
                    );
                }

                ProblemsetIdentitiesDAO::updatePrivacyStatementConsent(new \OmegaUp\DAO\VO\ProblemsetIdentities([
                    'identity_id' => $r->identity->identity_id,
                    'problemset_id' => $response['contest']->problemset_id,
                    'privacystatement_consent_id' => $privacyStatementConsentId,
                ]));
            }

            \OmegaUp\DAO\DAO::transEnd();
        } catch (Exception $e) {
            \OmegaUp\DAO\DAO::transRollback();
            throw $e;
        }

        self::$log->info("User '{$r->identity->username}' joined contest '{$response['contest']->alias}'");
        return ['status' => 'ok'];
    }

    /**
     * Returns details of a Contest. This is shared between apiDetails and
     * apiAdminDetails.
     *
     * @param string $contestAlias
     * @param \OmegaUp\DAO\VO\Contests $contest
     * @param $result
     */
    private static function getCachedDetails(string $contestAlias, \OmegaUp\DAO\VO\Contests $contest) : array {
        return \OmegaUp\Cache::getFromCacheOrSet(
            \OmegaUp\Cache::CONTEST_INFO,
            $contestAlias,
            function () use ($contest, &$result) {
                // Initialize response to be the contest information
                $result = $contest->asFilteredArray([
                    'title',
                    'description',
                    'start_time',
                    'finish_time',
                    'window_length',
                    'alias',
                    'scoreboard',
                    'scoreboard_url',
                    'scoreboard_url_admin',
                    'points_decay_factor',
                    'partial_score',
                    'submissions_gap',
                    'feedback',
                    'penalty',
                    'penalty_type',
                    'penalty_calc_policy',
                    'show_scoreboard_after',
                    'admission_mode',
                    'languages',
                    'problemset_id',
                    'rerun_id',
                ]);

                $result['start_time'] = \OmegaUp\DAO\DAO::fromMySQLTimestamp($result['start_time']);
                $result['finish_time'] = \OmegaUp\DAO\DAO::fromMySQLTimestamp($result['finish_time']);
                $result['show_scoreboard_after'] = (bool)$result['show_scoreboard_after'];
                $result['original_contest_alias'] = null;
                $result['original_problemset_id'] = null;
                if ($result['rerun_id'] != 0) {
                    $original_contest = ContestsDAO::getByPK($result['rerun_id']);
                    $result['original_contest_alias'] = $original_contest->alias;
                    $result['original_problemset_id'] = $original_contest->problemset_id;
                }

                $acl = ACLsDAO::getByPK($contest->acl_id);
                $result['director'] = UsersDAO::getByPK($acl->owner_id)->username;

                $problemsInContest = ProblemsetProblemsDAO::getProblemsByProblemset(
                    $contest->problemset_id
                );

                // Add info of each problem to the contest
                $problemsResponseArray = [];

                $letter = 0;

                foreach ($problemsInContest as $problem) {
                    // Add the 'points' value that is stored in the ContestProblem relationship
                    $problem['letter'] = ContestController::columnName($letter++);
                    if (!empty($result['languages'])) {
                        $problem['languages'] = join(',', array_intersect(
                            explode(',', $result['languages']),
                            explode(',', $problem['languages'])
                        ));
                    }

                    // Save our array into the response
                    array_push($problemsResponseArray, $problem);
                }

                // Add problems to response
                $result['problems'] = $problemsResponseArray;
                $result['languages'] = explode(',', $result['languages']);
                [
                    'needsBasicInformation' => $result['needs_basic_information'],
                    'requestsUserInformation' => $result['requests_user_information'],
                ] = ContestsDAO::getNeedsInformation($contest->problemset_id);
                return $result;
            },
            APC_USER_CACHE_CONTEST_INFO_TIMEOUT
        );
    }

    /**
     * Returns details of a Contest. Requesting the details of a contest will
     * not start the current user into that contest. In order to participate
     * in the contest, ContestController::apiOpen() must be used.
     *
     * @param \OmegaUp\Request $r
     * @return array
     */
    public static function apiDetails(\OmegaUp\Request $r) {
        $response = self::validateDetails($r);

        $result = self::getCachedDetails($r['contest_alias'], $response['contest']);
        unset($result['scoreboard_url']);
        unset($result['scoreboard_url_admin']);
        unset($result['rerun_id']);
        if (is_null($r['token'])) {
            // Adding timer info separately as it depends on the current user and we don't
            // want this to get generally cached for everybody
            // Save the time of the first access
            $problemsetIdentity = ProblemsetIdentitiesDAO::checkAndSaveFirstTimeAccess(
                $r->identity,
                $response['contest']
            );

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
            ProblemsetAccessLogDAO::create(new \OmegaUp\DAO\VO\ProblemsetAccessLog([
                'identity_id' => $r->identity->identity_id,
                'problemset_id' => $response['contest']->problemset_id,
                'ip' => ip2long($_SERVER['REMOTE_ADDR']),
            ]));
        } else {
            $result['admin'] = $response['contest_admin'];
        }

        $result['status'] = 'ok';
        $result['opened'] = true;
        return $result;
    }

    /**
     * Returns details of a Contest, for administrators. This differs from
     * apiDetails in the sense that it does not attempt to calculate the
     * remaining time from the contest, or register the opened time.
     *
     * @param \OmegaUp\Request $r
     * @return array
     */
    public static function apiAdminDetails(\OmegaUp\Request $r) {
        $response = self::validateDetails($r);

        if (!\OmegaUp\Authorization::isContestAdmin($r->identity, $response['contest'])) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        $result = self::getCachedDetails($r['contest_alias'], $response['contest']);

        $result['opened'] = ProblemsetIdentitiesDAO::checkProblemsetOpened(
            (int)$r->identity->identity_id,
            (int)$response['contest']->problemset_id
        );
        $result['available_languages'] = RunController::$kSupportedLanguages;
        $result['status'] = 'ok';
        $result['admin'] = true;
        return $result;
    }

    /**
     * Returns a report with all user activity for a contest.
     *
     * @param \OmegaUp\Request $r
     * @return array
     */
    public static function apiActivityReport(\OmegaUp\Request $r) {
        $response = self::validateDetails($r);

        if (!$response['contest_admin']) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        $accesses = ProblemsetAccessLogDAO::GetAccessForProblemset($response['contest']->problemset_id);
        $submissions = SubmissionLogDAO::GetSubmissionsForProblemset($response['contest']->problemset_id);

        return ActivityReport::getActivityReport($accesses, $submissions);
    }

    /**
     * Returns a "column name" for the $idx (think Excel column names).
     */
    public static function columnName($idx) {
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
     * @return Array
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     * @throws \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException
     */
    public static function apiClone(\OmegaUp\Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        // Authenticate user
        self::authenticateRequest($r, true /* requireMainUserIdentity */);

        $originalContest = self::validateContestAdmin(
            $r['contest_alias'],
            $r->identity
        );

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
            'start_time' => $r['start_time'],
            'finish_time' => $r['start_time'] + $length,
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

            $problemsetProblems = ProblemsetProblemsDAO::getProblemsByProblemset(
                $originalContest->problemset_id
            );
            foreach ($problemsetProblems as $problemsetProblem) {
                $problem = new \OmegaUp\DAO\VO\Problems([
                    'problem_id' => $problemsetProblem['problem_id'],
                    'alias' => $problemsetProblem['alias'],
                    'visibility' => $problemsetProblem['visibility'],
                ]);
                ProblemsetController::addProblem(
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
        } catch (Exception $e) {
            \OmegaUp\DAO\DAO::transRollback();
            throw $e;
        }

        return ['status' => 'ok', 'alias' => $r['alias']];
    }

    public static function apiCreateVirtual(\OmegaUp\Request $r) {
        global $experiments;
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        // Authenticate user
        self::authenticateRequest($r, true /* requireMainUserIdentity */);

        $originalContest = ContestsDAO::getByAlias($r['alias']);
        if (is_null($originalContest)) {
            throw new \OmegaUp\Exceptions\NotFoundException('contestNotFound');
        }

        if ($originalContest->finish_time > \OmegaUp\Time::get()) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('originalContestHasNotEnded');
        }

        $virtualContestAlias = ContestsDAO::generateAlias($originalContest);

        $contestLength = $originalContest->finish_time - $originalContest->start_time;

        $r->ensureInt('start_time', null, null, false);
        $r['start_time'] = !is_null($r['start_time']) ? $r['start_time'] : \OmegaUp\Time::get();

        // Initialize contest
        $contest = new \OmegaUp\DAO\VO\Contests([
            'title' => $originalContest->title,
            'description' => $originalContest->description,
            'window_length' => $originalContest->window_length,
            'start_time' => $r['start_time'],
            'finish_time' => $r['start_time'] + $contestLength,
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

        return ['status' => 'ok', 'alias' => $contest->alias];
    }

    /**
     * It retrieves a Problemset and a Contest objects to store them in the
     * database
     *
     * @param \OmegaUp\DAO\VO\Problemsets $problemset
     * @param \OmegaUp\DAO\VO\Contests $contest
     * @param int $currentUserId
     * @param int $originalProblemsetId
     * @param \OmegaUp\DAO\VO\Problemsets $problemset
     */
    private static function createContest(
        \OmegaUp\DAO\VO\Problemsets $problemset,
        \OmegaUp\DAO\VO\Contests $contest,
        int $currentUserId,
        ?int $originalProblemsetId = null
    ) : void {
        $acl = new \OmegaUp\DAO\VO\ACLs();
        $acl->owner_id = $currentUserId;
        // Push changes
        try {
            // Begin a new transaction
            \OmegaUp\DAO\DAO::transBegin();

            ACLsDAO::create($acl);
            $problemset->acl_id = $acl->acl_id;
            $problemset->type = 'Contest';
            $problemset->scoreboard_url = SecurityTools::randomString(30);
            $problemset->scoreboard_url_admin = SecurityTools::randomString(30);
            $contest->acl_id = $acl->acl_id;

            // Save the problemset object with data sent by user to the database
            ProblemsetsDAO::create($problemset);

            $contest->problemset_id = $problemset->problemset_id;
            $contest->penalty_calc_policy = $contest->penalty_calc_policy ?: 'sum';
            $contest->rerun_id = $contest->rerun_id ?: 0;
            if (!is_null($originalProblemsetId)) {
                ProblemsetProblemsDAO::copyProblemset(
                    $contest->problemset_id,
                    $originalProblemsetId
                );
            }

            // Save the contest object with data sent by user to the database
            ContestsDAO::create($contest);

            // Update contest_id in problemset object
            $problemset->contest_id = $contest->contest_id;
            ProblemsetsDAO::update($problemset);

            // End transaction transaction
            \OmegaUp\DAO\DAO::transEnd();
        } catch (Exception $e) {
            // Operation failed in the data layer, rollback transaction
            \OmegaUp\DAO\DAO::transRollback();
            if (\OmegaUp\DAO\DAO::isDuplicateEntryException($e)) {
                throw new \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException('titleInUse', $e);
            }
            throw $e;
        }

        // Expire contest-list cache
        \OmegaUp\Cache::invalidateAllKeys(\OmegaUp\Cache::CONTESTS_LIST_PUBLIC);
        \OmegaUp\Cache::invalidateAllKeys(\OmegaUp\Cache::CONTESTS_LIST_SYSTEM_ADMIN);

        self::$log->info('New Contest Created: ' . $contest->alias);
    }

    /**
     * Creates a new contest
     *
     * @param \OmegaUp\Request $r
     * @return array
     * @throws \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException
     */
    public static function apiCreate(\OmegaUp\Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        // Authenticate user
        self::authenticateRequest($r, true /* requireMainUserIdentity */);

        // Validate request
        self::validateCreate($r);

        // Set private contest by default if is not sent in request
        if (!is_null($r['admission_mode']) && $r['admission_mode'] != 'private') {
            throw new \OmegaUp\Exceptions\InvalidParameterException('contestMustBeCreatedInPrivateMode');
        }

        $problemset = new \OmegaUp\DAO\VO\Problemsets([
            'needs_basic_information' => $r['basic_information'] == 'true',
            'requests_user_information' => $r['requests_user_information'],
        ]);

        $languages = empty($r['languages']) ? null : join(',', $r['languages']);
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
     * @param \OmegaUp\Request $r
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     */
    private static function validateCommonCreateOrUpdate(\OmegaUp\Request $r, ?\OmegaUp\DAO\VO\Contests $contest = null, bool $isRequired = true) : void {
        \OmegaUp\Validators::validateStringNonEmpty($r['title'], 'title', $isRequired);
        \OmegaUp\Validators::validateStringNonEmpty($r['description'], 'description', $isRequired);

        $r->ensureInt('start_time', null, null, $isRequired);
        $r->ensureInt('finish_time', null, null, $isRequired);

        // Get the actual start and finish time of the contest, considering that
        // in case of update, parameters can be optional
        $start_time = !is_null($r['start_time']) ? $r['start_time'] : \OmegaUp\DAO\DAO::fromMySQLTimestamp($contest->start_time);
        $finish_time = !is_null($r['finish_time']) ? $r['finish_time'] : \OmegaUp\DAO\DAO::fromMySQLTimestamp($contest->finish_time);

        // Validate start & finish time
        if ($start_time > $finish_time) {
            throw new \OmegaUp\Exceptions\InvalidParameterException('contestNewInvalidStartTime');
        }

        // Calculate the actual contest length
        $contest_length = $finish_time - $start_time;

        // Validate max contest length
        if ($contest_length > ContestController::MAX_CONTEST_LENGTH_SECONDS) {
            throw new \OmegaUp\Exceptions\InvalidParameterException('contestLengthTooLong');
        }

        // Window_length is optional
        if (!empty($r['window_length'])) {
            $r->ensureInt(
                'window_length',
                0,
                intval($contest_length / 60),
                false
            );
        }

        \OmegaUp\Validators::validateInEnum($r['admission_mode'], 'admission_mode', [
            'public',
            'private',
            'registration'
        ], false);
        \OmegaUp\Validators::validateValidAlias($r['alias'], 'alias', $isRequired);
        $r->ensureFloat('scoreboard', 0, 100, $isRequired);
        $r->ensureFloat('points_decay_factor', 0, 1, $isRequired);
        $r->ensureBool('partial_score', false);
        $r->ensureInt('submissions_gap', 0, null, $isRequired);
        // Validate the submission_gap in minutes so that the error message
        // matches what is displayed in the UI.
        \OmegaUp\Validators::validateNumberInRange(
            $r['submissions_gap'] == null ? null : floor($r['submissions_gap']/60),
            'submissions_gap',
            1,
            floor($contest_length / 60),
            $isRequired
        );

        \OmegaUp\Validators::validateInEnum($r['feedback'], 'feedback', ['no', 'yes', 'partial'], $isRequired);
        \OmegaUp\Validators::validateInEnum($r['penalty_type'], 'penalty_type', ['contest_start', 'problem_open', 'runtime', 'none'], $isRequired);
        \OmegaUp\Validators::validateInEnum($r['penalty_calc_policy'], 'penalty_calc_policy', ['sum', 'max'], false);

        // Problems is optional
        if (!is_null($r['problems'])) {
            $requestProblems = json_decode($r['problems']);
            if (is_null($requestProblems)) {
                throw new \OmegaUp\Exceptions\InvalidParameterException('invalidParameters', 'problems');
            }

            $problems = [];

            foreach ($requestProblems as $requestProblem) {
                $problem = ProblemsDAO::getByAlias($requestProblem->problem);
                if (is_null($problem)) {
                    throw new \OmegaUp\Exceptions\InvalidParameterException('parameterNotFound', 'problems');
                }
                ProblemsetController::validateAddProblemToProblemset(
                    $problem,
                    $r->identity
                );
                array_push($problems, [
                    'id' => $problem->problem_id,
                    'alias' => $requestProblem->problem,
                    'points' => $requestProblem->points
                ]);
            }

            $r['problems'] = $problems;
        }

        // Show scoreboard is always optional
        $r->ensureBool('show_scoreboard_after', false);

        // languages is always optional
        if (!empty($r['languages'])) {
            foreach ($r['languages'] as $language) {
                \OmegaUp\Validators::validateInEnum($language, 'languages', array_keys(RunController::$kSupportedLanguages), false);
            }
        }
    }

    /**
     * Validates that Request contains expected data to create a contest
     * In case of error, this function throws.
     *
     * @param \OmegaUp\Request $r
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     */
    private static function validateCreate(\OmegaUp\Request $r) : void {
        self::validateCommonCreateOrUpdate($r);
    }

    /**
     * Validates that Request contains expected data to update a contest
     * everything is optional except the contest_alias
     * In case of error, this function throws.
     *
     * @param \OmegaUp\Request $r
     * @return \OmegaUp\DAO\VO\Contests
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     */
    private static function validateUpdate(\OmegaUp\Request $r) : \OmegaUp\DAO\VO\Contests {
        $contest = self::validateContestAdmin(
            $r['contest_alias'],
            $r->identity
        );

        self::validateCommonCreateOrUpdate($r, $contest, false /* is required*/);

        // Prevent date changes if a contest already has runs
        if (!is_null($r['start_time']) && $r['start_time'] != \OmegaUp\DAO\DAO::fromMySQLTimestamp($contest->start_time)) {
            $runCount = 0;

            $runCount = SubmissionsDAO::countTotalSubmissionsOfProblemset(
                (int)$contest->problemset_id
            );

            if ($runCount > 0) {
                throw new \OmegaUp\Exceptions\InvalidParameterException('contestUpdateAlreadyHasRuns');
            }
        }
        return $contest;
    }

    /**
     * Function created to be called for all the API's that only can access
     * admins or contest organizers.
     *
     * @param string $contestAlias
     * @param \OmegaUp\DAO\VO\Identities $identity
     * @return \OmegaUp\DAO\VO\Contests
     * @throws \OmegaUp\Exceptions\NotFoundException
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     */
    private static function validateContestAdmin(
        string $contestAlias,
        \OmegaUp\DAO\VO\Identities $identity,
        string $message = 'userNotAllowed'
    ) : \OmegaUp\DAO\VO\Contests {
        $contest = ContestsDAO::getByAlias($contestAlias);
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
     * @param \OmegaUp\Request $r
     * @return void
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     */
    private static function forbiddenInVirtual(\OmegaUp\DAO\VO\Contests $contest) {
        if (ContestsDAO::isVirtual($contest)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('forbiddenInVirtualContest');
        }
    }

    /**
     * Gets the problems from a contest
     *
     * @param \OmegaUp\Request $r
     * @return array
     */
    public static function apiProblems(\OmegaUp\Request $r) {
        // Authenticate user
        self::authenticateRequest($r);

        \OmegaUp\Validators::validateStringNonEmpty($r['contest_alias'], 'contest_alias');

        // Only director is allowed to create problems in contest
        $contest = self::validateContestAdmin(
            $r['contest_alias'],
            $r->identity,
            'cannotAddProb'
        );

        $problemset = ProblemsetsDAO::getByPK($contest->problemset_id);
        $problems = ProblemsetProblemsDAO::getProblemsByProblemset(
            $problemset->problemset_id
        );
        foreach ($problems as &$problem) {
            unset($problem['problem_id']);
        }

        return ['status' => 'ok', 'problems' => $problems];
    }

    /**
     * Adds a problem to a contest
     *
     * @param \OmegaUp\Request $r
     * @return array
     */
    public static function apiAddProblem(\OmegaUp\Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        // Authenticate user
        self::authenticateRequest($r);

        // Validate the request and get the problem and the contest in an array
        $params = self::validateAddToContestRequest(
            $r,
            $r['contest_alias'],
            $r['problem_alias']
        );

        self::forbiddenInVirtual($params['contest']);

        $problemset = ProblemsetsDAO::getByPK($params['contest']->problemset_id);

        if (ProblemsetProblemsDAO::countProblemsetProblems($problemset)
                >= MAX_PROBLEMS_IN_CONTEST) {
            throw new \OmegaUp\Exceptions\PreconditionFailedException('contestAddproblemTooManyProblems');
        }

        [$masterCommit, $currentVersion] = ProblemController::resolveCommit(
            $params['problem'],
            $r['commit']
        );

        ProblemsetController::addProblem(
            $params['contest']->problemset_id,
            $params['problem'],
            $masterCommit,
            $currentVersion,
            $r->identity,
            $r['points'],
            $r['order_in_contest'] ?: 1
        );

        // Invalidar cache
        \OmegaUp\Cache::deleteFromCache(\OmegaUp\Cache::CONTEST_INFO, $r['contest_alias']);
        Scoreboard::invalidateScoreboardCache(ScoreboardParams::fromContest($params['contest']));

        return ['status' => 'ok'];
    }

    /**
     * Validates the request for AddToContest and returns an array with
     * the problem and contest DAOs
     *
     * @param \OmegaUp\Request $r
     * @param string $contestAlias
     * @param string $problemAlias
     * @return Array
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     */
    private static function validateAddToContestRequest(
        \OmegaUp\Request $r,
        string $contestAlias,
        ?string $problemAlias
    ) : Array {
        \OmegaUp\Validators::validateStringNonEmpty($contestAlias, 'contest_alias');

        // Only director is allowed to create problems in contest
        $contest = ContestsDAO::getByAlias($r['contest_alias']);
        if (is_null($contest)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException('parameterNotFound', 'contest_alias');
        }
        // Only contest admin is allowed to create problems in contest
        if (!\OmegaUp\Authorization::isContestAdmin($r->identity, $contest)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('cannotAddProb');
        }

        \OmegaUp\Validators::validateStringNonEmpty($problemAlias, 'problem_alias');

        $problem = ProblemsDAO::getByAlias($problemAlias);
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException('parameterNotFound', 'problem_alias');
        }

        if ($problem->visibility == ProblemController::VISIBILITY_PRIVATE_BANNED
            || $problem->visibility == ProblemController::VISIBILITY_PUBLIC_BANNED) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('problemIsBanned');
        }
        if (!ProblemsDAO::isVisible($problem) && !\OmegaUp\Authorization::isProblemAdmin(
            $r->identity,
            $problem
        )) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('problemIsPrivate');
        }

        $r->ensureFloat('points', 0, INF);
        $r->ensureInt('order_in_contest', 0, null, false);

        return [
            'contest' => $contest,
            'problem' => $problem,
        ];
    }

    /**
     * Removes a problem from a contest
     *
     * @param \OmegaUp\Request $r
     * @return array
     */
    public static function apiRemoveProblem(\OmegaUp\Request $r) {
        // Authenticate user
        self::authenticateRequest($r);

        // Validate the request and get the problem and the contest in an array
        $params = self::validateRemoveFromContestRequest(
            $r['contest_alias'],
            $r['problem_alias'],
            $r->identity
        );

        self::forbiddenInVirtual($params['contest']);

        ProblemsetProblemsDAO::delete(new \OmegaUp\DAO\VO\ProblemsetProblems([
            'problemset_id' => $params['contest']->problemset_id,
            'problem_id' => $params['problem']->problem_id
        ]));

        // Invalidar cache
        \OmegaUp\Cache::deleteFromCache(\OmegaUp\Cache::CONTEST_INFO, $r['contest_alias']);
        Scoreboard::invalidateScoreboardCache(ScoreboardParams::fromContest($params['contest']));

        return ['status' => 'ok'];
    }

    /**
     * Validates the request for RemoveFromContest and returns an array with
     * the problem and contest DAOs
     *
     * @param string $contestAlias
     * @param string $problemAlias
     * @param \OmegaUp\DAO\VO\Identities $identity
     * @return Array
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     */
    private static function validateRemoveFromContestRequest(
        string $contestAlias,
        ?string $problemAlias,
        \OmegaUp\DAO\VO\Identities $identity
    ) : Array {
        \OmegaUp\Validators::validateStringNonEmpty($contestAlias, 'contest_alias');

        $contest = ContestsDAO::getByAlias($contestAlias);
        if (is_null($contest)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException('parameterNotFound', 'problem_alias');
        }
        // Only contest admin is allowed to remove problems in contest
        if (!\OmegaUp\Authorization::isContestAdmin($identity, $contest)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('cannotRemoveProblem');
        }

        \OmegaUp\Validators::validateStringNonEmpty($problemAlias, 'problem_alias');

        $problem = ProblemsDAO::getByAlias($problemAlias);
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException('parameterNotFound', 'problem_alias');
        }

        // Disallow removing problem from contest if it already has runs within the contest
        if (SubmissionsDAO::countTotalRunsOfProblemInProblemset(
            intval($problem->problem_id),
            intval($contest->problemset_id)
        ) > 0
            && !\OmegaUp\Authorization::isSystemAdmin($identity)
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('cannotRemoveProblemWithSubmissions');
        }

        if (self::isPublic($contest->admission_mode)) {
            // Check that contest has at least 2 problems
            $problemset = ProblemsetsDAO::getByPK($contest->problemset_id);
            $problemsInContest = ProblemsetProblemsDAO::GetRelevantProblems($problemset);
            if (count($problemsInContest) < 2) {
                throw new \OmegaUp\Exceptions\InvalidParameterException('contestPublicRequiresProblem');
            }
        }

        return [
            'contest' => $contest,
            'problem' => $problem,
        ];
    }

    /**
     * Return a report of which runs would change due to a version change.
     */
    public static function apiRunsDiff(\OmegaUp\Request $r) : array {
        self::authenticateRequest($r);

        \OmegaUp\Validators::validateValidAlias($r['problem_alias'], 'problem_alias');
        \OmegaUp\Validators::validateStringNonEmpty($r['contest_alias'], 'contest_alias');
        \OmegaUp\Validators::validateStringNonEmpty($r['version'], 'version');

        $contest = self::validateContestAdmin($r['contest_alias'], $r->identity);

        $problem = ProblemsDAO::getByAlias($r['problem_alias']);
        if (is_null($problem)) {
            throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
        }

        $problemsetProblem = ProblemsetProblemsDAO::getByPK(
            (int)$contest->problemset_id,
            (int)$problem->problem_id
        );
        if (is_null($problemsetProblem)) {
            throw new \OmegaUp\Exceptions\NotFoundException('recordNotFound');
        }

        return [
            'status' => 'ok',
            'diff' => RunsDAO::getRunsDiffsForVersion(
                $problem,
                (int)$contest->problemset_id,
                $problemsetProblem->version,
                $r['version']
            ),
        ];
    }

    /**
     * Validates add/remove user request
     *
     * @param string $contestAlias
     * @param string $usernameOrEmail
     * @param \OmegaUp\DAO\VO\Identities $identity
     * @return Array
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     */
    private static function validateAddRemoveUser(
        string $contestAlias,
        string $usernameOrEmail,
        \OmegaUp\DAO\VO\Identities $identity
    ) : Array {
        // Check contest_alias
        \OmegaUp\Validators::validateStringNonEmpty($contestAlias, 'contest_alias');

        $identityToRemove = IdentityController::resolveIdentity($usernameOrEmail);
        $contest = self::validateContestAdmin($contestAlias, $identity);
        return [$identityToRemove, $contest];
    }

    /**
     * Adds a user to a contest.
     * By default, any user can view details of public contests.
     * Only users added through this API can view private contests
     *
     * @param \OmegaUp\Request $r
     * @return array
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     */
    public static function apiAddUser(\OmegaUp\Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        // Authenticate logged user
        self::authenticateRequest($r);
        [$identity, $contest] = self::validateAddRemoveUser(
            $r['contest_alias'],
            $r['usernameOrEmail'],
            $r->identity
        );

        // Save the contest to the DB
        ProblemsetIdentitiesDAO::replace(new \OmegaUp\DAO\VO\ProblemsetIdentities([
            'problemset_id' => $contest->problemset_id,
            'identity_id' => $identity->identity_id,
            'access_time' => null,
            'end_time' => null,
            'score' => '0',
            'time' => '0',
            'is_invited' => '1',
        ]));

        return ['status' => 'ok'];
    }

    /**
     * Remove a user from a private contest
     *
     * @param \OmegaUp\Request $r
     * @return type
     */
    public static function apiRemoveUser(\OmegaUp\Request $r) {
        // Authenticate logged user
        self::authenticateRequest($r);
        [$identity, $contest] = self::validateAddRemoveUser(
            $r['contest_alias'],
            $r['usernameOrEmail'],
            $r->identity
        );

        ProblemsetIdentitiesDAO::delete(new \OmegaUp\DAO\VO\ProblemsetIdentities([
            'problemset_id' => $contest->problemset_id,
            'identity_id' => $identity->identity_id,
        ]));

        return ['status' => 'ok'];
    }

    /**
     * Adds an admin to a contest
     *
     * @param \OmegaUp\Request $r
     * @return array
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     */
    public static function apiAddAdmin(\OmegaUp\Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        // Authenticate logged user
        self::authenticateRequest($r);

        // Check contest_alias
        \OmegaUp\Validators::validateStringNonEmpty($r['contest_alias'], 'contest_alias');

        $user = UserController::resolveUser($r['usernameOrEmail']);

        $contest = self::validateContestAdmin($r['contest_alias'], $r->identity);

        ACLController::addUser($contest->acl_id, $user->user_id);

        return ['status' => 'ok'];
    }

    /**
     * Removes an admin from a contest
     *
     * @param \OmegaUp\Request $r
     * @return array
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     */
    public static function apiRemoveAdmin(\OmegaUp\Request $r) {
        // Authenticate logged user
        self::authenticateRequest($r);

        // Check contest_alias
        \OmegaUp\Validators::validateStringNonEmpty($r['contest_alias'], 'contest_alias');

        $identity = IdentityController::resolveIdentity($r['usernameOrEmail']);

        $contest = self::validateContestAdmin($r['contest_alias'], $r->identity);

        // Check if admin to delete is actually an admin
        if (!\OmegaUp\Authorization::isContestAdmin($identity, $contest)) {
            throw new \OmegaUp\Exceptions\NotFoundException();
        }

        ACLController::removeUser($contest->acl_id, $identity->user_id);

        return ['status' => 'ok'];
    }

    /**
     * Adds an group admin to a contest
     *
     * @param \OmegaUp\Request $r
     * @return array
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     */
    public static function apiAddGroupAdmin(\OmegaUp\Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        // Authenticate logged user
        self::authenticateRequest($r);

        // Check contest_alias
        \OmegaUp\Validators::validateStringNonEmpty($r['contest_alias'], 'contest_alias');

        $group = GroupsDAO::findByAlias($r['group']);

        if ($group == null) {
            throw new \OmegaUp\Exceptions\InvalidParameterException('invalidParameters');
        }

        $contest = self::validateContestAdmin($r['contest_alias'], $r->identity);

        ACLController::addGroup($contest->acl_id, $group->group_id);

        return ['status' => 'ok'];
    }

    /**
     * Removes a group admin from a contest
     *
     * @param \OmegaUp\Request $r
     * @return array
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     */
    public static function apiRemoveGroupAdmin(\OmegaUp\Request $r) {
        // Authenticate logged user
        self::authenticateRequest($r);

        // Check contest_alias
        \OmegaUp\Validators::validateStringNonEmpty($r['contest_alias'], 'contest_alias');

        $group = GroupsDAO::findByAlias($r['group']);

        if ($group == null) {
            throw new \OmegaUp\Exceptions\InvalidParameterException('invalidParameters');
        }

        $contest = self::validateContestAdmin($r['contest_alias'], $r->identity);

        ACLController::removeGroup($contest->acl_id, $group->group_id);

        return ['status' => 'ok'];
    }

    /**
     * Validate the Clarifications request
     *
     * @param \OmegaUp\Request $r
     * @return \OmegaUp\DAO\VO\Contests
     */
    private static function validateClarifications(\OmegaUp\Request $r) : \OmegaUp\DAO\VO\Contests {
        // Check contest_alias
        \OmegaUp\Validators::validateStringNonEmpty($r['contest_alias'], 'contest_alias');

        $contest = ContestsDAO::getByAlias($r['contest_alias']);
        if (is_null($contest)) {
            throw new \OmegaUp\Exceptions\NotFoundException('contestNotFound');
        }

        $r->ensureInt('offset', null, null, false /* optional */);
        $r->ensureInt('rowcount', null, null, false /* optional */);

        return $contest;
    }

    /**
     *
     * Get clarifications of a contest
     *
     * @param \OmegaUp\Request $r
     * @return array
     */
    public static function apiClarifications(\OmegaUp\Request $r) {
        self::authenticateRequest($r);
        $contest = self::validateClarifications($r);

        $isContestDirector = \OmegaUp\Authorization::isContestAdmin(
            $r->identity,
            $contest
        );

        $clarifications = ClarificationsDAO::GetProblemsetClarifications(
            $contest->problemset_id,
            $isContestDirector,
            $r->identity->identity_id,
            $r['offset'],
            $r['rowcount']
        );

        foreach ($clarifications as &$clar) {
            $clar['time'] = (int)$clar['time'];
        }

        // Add response to array
        $response = [];
        $response['clarifications'] = $clarifications;
        $response['status'] = 'ok';

        return $response;
    }

    /**
     * Returns the Scoreboard events
     *
     * @param \OmegaUp\Request $r
     * @return array
     * @throws \OmegaUp\Exceptions\NotFoundException
     */
    public static function apiScoreboardEvents(\OmegaUp\Request $r) {
        // Get the current user
        $response = self::validateDetails($r);

        $params = ScoreboardParams::fromContest($response['contest']);
        $params['admin'] = (
            \OmegaUp\Authorization::isContestAdmin($r->identity, $response['contest']) &&
            !ContestsDAO::isVirtual($response['contest'])
        );
        $params['show_all_runs'] = !ContestsDAO::isVirtual($response['contest']);
        $scoreboard = new Scoreboard($params);

        // Push scoreboard data in response
        $response = [];
        $response['events'] = $scoreboard->events();

        return $response;
    }

    /**
     * Returns the Scoreboard
     *
     * @param \OmegaUp\Request $r
     * @return array
     * @throws \OmegaUp\Exceptions\NotFoundException
     */
    public static function apiScoreboard(\OmegaUp\Request $r) {
        [$contest, $problemset] = self::validateBasicDetails($r['contest_alias']);

        // If true, will override Scoreboard Pertentage to 100%
        $showAllRuns = false;

        if (is_null($r['token'])) {
            // Get the current user
            self::authenticateRequest($r);

            self::canAccessContest($contest, $r->identity);

            if (\OmegaUp\Authorization::isContestAdmin($r->identity, $contest)) {
                $showAllRuns = true;
            }
        } else {
            if ($r['token'] === $problemset->scoreboard_url) {
                $showAllRuns = false;
            } elseif ($r['token'] === $problemset->scoreboard_url_admin) {
                $showAllRuns = true;
            } else {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException('invalidScoreboardUrl');
            }
        }

        // Create scoreboard
        $params = ScoreboardParams::fromContest($contest);
        $params['admin'] = $showAllRuns;
        $scoreboard = new Scoreboard($params);

        return $scoreboard->generate();
    }

    /**
     * Gets the accomulative scoreboard for an array of contests
     *
     * @param \OmegaUp\Request $r
     */
    public static function apiScoreboardMerge(\OmegaUp\Request $r) {
        // Get the current user
        self::authenticateRequest($r);

        \OmegaUp\Validators::validateStringNonEmpty($r['contest_aliases'], 'contest_aliases');
        $contest_aliases = explode(',', $r['contest_aliases']);

        \OmegaUp\Validators::validateStringNonEmpty($r['usernames_filter'], 'usernames_filter', false);

        $usernames_filter = [];
        if (isset($r['usernames_filter'])) {
            $usernames_filter = explode(',', $r['usernames_filter']);
        }

        // Validate all contest alias
        $contests = [];
        foreach ($contest_aliases as $contest_alias) {
            $contest = ContestsDAO::getByAlias($contest_alias);
            if (is_null($contest)) {
                throw new \OmegaUp\Exceptions\NotFoundException('contestNotFound');
            }

            array_push($contests, $contest);
        }

        // Get all scoreboards
        $scoreboards = [];
        foreach ($contests as $contest) {
            // Set defaults for contests params
            if (!isset($r['contest_params'][$contest->alias]['only_ac'])) {
                // Hay que hacer esto para evitar "Indirect modification of overloaded element of Request has no effect"
                // http://stackoverflow.com/questions/20053269/indirect-modification-of-overloaded-element-of-splfixedarray-has-no-effect
                $cp = $r['contest_params'];
                $cp[$contest->alias]['only_ac'] = false;
                $r['contest_params'] = $cp;
            }

            if (!isset($r['contest_params'][$contest->alias]['weight'])) {
                // Ditto indirect modification.
                $cp = $r['contest_params'];
                $cp[$contest->alias]['weight'] = 1;
                $r['contest_params'] = $cp;
            }

            $params = ScoreboardParams::fromContest($contest);
            $params['only_ac'] = $r['contest_params'][$contest->alias]['only_ac'];
            $s = new Scoreboard($params);

            $scoreboards[$contest->alias] = $s->generate();
        }

        $merged_scoreboard = [];

        // Merge
        foreach ($scoreboards as $contest_alias => $scoreboard) {
            foreach ($scoreboard['ranking'] as $user_results) {
                // If user haven't been added to the merged scoredboard, add him
                if (!isset($merged_scoreboard[$user_results['username']])) {
                    $merged_scoreboard[$user_results['username']] = [];
                    $merged_scoreboard[$user_results['username']]['name'] = $user_results['name'];
                    $merged_scoreboard[$user_results['username']]['username'] = $user_results['username'];
                    $merged_scoreboard[$user_results['username']]['total']['points'] = 0;
                    $merged_scoreboard[$user_results['username']]['total']['penalty'] = 0;
                }

                $merged_scoreboard[$user_results['username']]['contests'][$contest_alias]['points'] = ($user_results['total']['points'] * $r['contest_params'][$contest_alias]['weight']);
                $merged_scoreboard[$user_results['username']]['contests'][$contest_alias]['penalty'] = $user_results['total']['penalty'];

                $merged_scoreboard[$user_results['username']]['total']['points'] += ($user_results['total']['points'] * $r['contest_params'][$contest_alias]['weight']);
                $merged_scoreboard[$user_results['username']]['total']['penalty'] += $user_results['total']['penalty'];
            }
        }

        // Remove users not in filter
        if (isset($r['usernames_filter'])) {
            foreach ($merged_scoreboard as $username => $entry) {
                if (array_search($username, $usernames_filter) === false) {
                    unset($merged_scoreboard[$username]);
                }
            }
        }

        // Normalize user["contests"] entries so all contain the same contests
        foreach ($merged_scoreboard as $username => $entry) {
            foreach ($contests as $contest) {
                if (!isset($entry['contests'][$contest->alias]['points'])) {
                    $merged_scoreboard[$username]['contests'][$contest->alias]['points'] = 0;
                    $merged_scoreboard[$username]['contests'][$contest->alias]['penalty'] = 0;
                }
            }
        }

        // Sort merged_scoreboard
        usort($merged_scoreboard, ['self', 'compareUserScores']);

        $response = [];
        $response['ranking'] = $merged_scoreboard;
        $response['status'] = 'ok';

        return $response;
    }

    /**
     * Compares results of 2 contestants to sort them in the scoreboard
     *
     * @param type $a
     * @param type $b
     * @return int
     */
    private static function compareUserScores($a, $b) {
        if ($a['total']['points'] == $b['total']['points']) {
            if ($a['total']['penalty'] == $b['total']['penalty']) {
                return 0;
            }

            return ($a['total']['penalty'] > $b['total']['penalty']) ? 1 : -1;
        }

        return ($a['total']['points'] < $b['total']['points']) ? 1 : -1;
    }

    public static function apiRequests(\OmegaUp\Request $r) {
        // Authenticate request
        self::authenticateRequest($r);

        \OmegaUp\Validators::validateStringNonEmpty($r['contest_alias'], 'contest_alias');

        $contest = self::validateContestAdmin($r['contest_alias'], $r->identity);

        $resultAdmins =
            ProblemsetIdentityRequestDAO::getFirstAdminForProblemsetRequest(
                $contest->problemset_id
            );
        $resultRequests =
            ProblemsetIdentityRequestDAO::getRequestsForProblemset(
                $contest->problemset_id
            );

        $admins = [];
        $requestsAdmins = [];
        foreach ($resultAdmins as $result) {
            $adminId = $result['admin_id'];
            if (!empty($adminId) && !array_key_exists($adminId, $admins)) {
                $admin = [];
                $data = IdentitiesDAO::findByUserId($adminId);
                if (!is_null($data)) {
                    $admin = [
                        'user_id' => $data->user_id,
                        'username' => $data->username,
                        'name' => $data->name,
                    ];
                }
                $requestsAdmins[$result['identity_id']] = $admin;
            }
        }

        $usersRequests = array_map(function ($request) use ($requestsAdmins) {
            if (isset($requestsAdmins[$request['identity_id']])) {
                $request['admin'] = $requestsAdmins[$request['identity_id']];
            }
            return $request;
        }, $resultRequests);

        return [
            'users' => $usersRequests,
            'contest_alias' => $r['contest_alias'],
            'status' => 'ok',
        ];
    }

    public static function apiArbitrateRequest(\OmegaUp\Request $r) {
        self::authenticateRequest($r);

        \OmegaUp\Validators::validateStringNonEmpty($r['contest_alias'], 'contest_alias');

        if (is_null($r['resolution'])) {
            throw new \OmegaUp\Exceptions\InvalidParameterException('invalidParameters');
        }

        $contest = self::validateContestAdmin($r['contest_alias'], $r->identity);

        $targetIdentity = IdentitiesDAO::findByUsername($r['username']);

        $request = ProblemsetIdentityRequestDAO::getByPK($targetIdentity->identity_id, $contest->problemset_id);

        if (is_null($request)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException('userNotInListOfRequests');
        }

        if (is_bool($r['resolution'])) {
            $resolution = $r['resolution'];
        } else {
            $resolution = $r['resolution'] === 'true';
        }

        $request->accepted = $resolution;
        $request->extra_note = $r['note'];
        $request->last_update = \OmegaUp\Time::get();

        ProblemsetIdentityRequestDAO::update($request);

        // Save this action in the history
        ProblemsetIdentityRequestHistoryDAO::create(new \OmegaUp\DAO\VO\ProblemsetIdentityRequestHistory([
            'identity_id' => $request->identity_id,
            'problemset_id' => $contest->problemset_id,
            'time' => $request->last_update,
            'admin_id' => $r->user->user_id,
            'accepted' => $request->accepted,
        ]));

        self::$log->info('Arbitrated contest for user, new accepted username='
                                . $targetIdentity->username . ', state=' . $resolution);

        return ['status' => 'ok'];
    }

    /**
     * Returns ALL identities participating in a contest
     *
     * @param \OmegaUp\Request $r
     * @return array
     */
    public static function apiUsers(\OmegaUp\Request $r) {
        // Authenticate request
        self::authenticateRequest($r);

        \OmegaUp\Validators::validateStringNonEmpty($r['contest_alias'], 'contest_alias');
        $contest = self::validateContestAdmin($r['contest_alias'], $r->identity);

        return [
            'status' => 'ok',
            'users' => ProblemsetIdentitiesDAO::getWithExtraInformation(
                $contest->problemset_id
            ),
        ];
    }

    /**
     * Returns all contest administrators
     *
     * @param \OmegaUp\Request $r
     * @return array
     */
    public static function apiAdmins(\OmegaUp\Request $r) {
        // Authenticate request
        self::authenticateRequest($r);

        \OmegaUp\Validators::validateStringNonEmpty($r['contest_alias'], 'contest_alias');

        $contest = self::validateContestAdmin($r['contest_alias'], $r->identity);

        return [
            'status' => 'ok',
            'admins' => UserRolesDAO::getContestAdmins($contest),
            'group_admins' => GroupRolesDAO::getContestAdmins($contest)
        ];
    }

    /**
     * Enforces rules to avoid having invalid/unactionable public contests
     *
     * @param \OmegaUp\DAO\VO\Contests $contest
     */
    private static function validateContestCanBePublic(\OmegaUp\DAO\VO\Contests $contest) {
        $problemset = ProblemsetsDAO::getByPK($contest->problemset_id);
        // Check that contest has some problems at least 1 problem
        $problemsInProblemset = ProblemsetProblemsDAO::getRelevantProblems($problemset);
        if (count($problemsInProblemset) < 1) {
            throw new \OmegaUp\Exceptions\InvalidParameterException('contestPublicRequiresProblem');
        }
    }

    /**
     * Update a Contest
     *
     * @param \OmegaUp\Request $r
     * @return array
     */
    public static function apiUpdate(\OmegaUp\Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        // Authenticate request
        self::authenticateRequest($r);

        // Validate request
        $contest = self::validateUpdate($r);

        self::forbiddenInVirtual($contest);

        $updateProblemset = true;
        // Update contest DAO
        if (!is_null($r['admission_mode'])) {
            // If going public
            if (self::isPublic($r['admission_mode'])) {
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
            'window_length' => ['transform' => function ($value) {
                return empty($value) ? null : $value;
            }],
            'scoreboard',
            'points_decay_factor',
            'partial_score',
            'submissions_gap',
            'feedback',
            'penalty' => ['transform' => function ($value) {
                return max(0, intval($value));
            }],
            'penalty_type',
            'penalty_calc_policy',
            'show_scoreboard_after' => ['transform' => function ($value) {
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            }],
            'languages' => ['transform' => function ($value) {
                if (!is_array($value)) {
                    return $value;
                }
                return join(',', $value);
            }],
            'admission_mode',
        ];
        self::updateValueProperties($r, $contest, $valueProperties);

        $originalContest = ContestsDAO::getByPK($contest->contest_id);

        // Push changes
        try {
            // Begin a new transaction
            \OmegaUp\DAO\DAO::transBegin();

            // Save the contest object with data sent by user to the database
            self::updateContest($contest, $originalContest, $r->identity);

            if ($updateProblemset) {
                // Save the problemset object with data sent by user to the database
                $problemset = ProblemsetsDAO::getByPK($contest->problemset_id);
                $problemset->needs_basic_information = $r['basic_information'] ?? 0;
                $problemset->requests_user_information = $r['requests_user_information'] ?? 'no';
                ProblemsetsDAO::update($problemset);
            }

            // End transaction
            \OmegaUp\DAO\DAO::transEnd();
        } catch (Exception $e) {
            // Operation failed in the data layer, rollback transaction
            \OmegaUp\DAO\DAO::transRollback();

            throw $e;
        }

        // Expire contest-info cache
        \OmegaUp\Cache::deleteFromCache(\OmegaUp\Cache::CONTEST_INFO, $r['contest_alias']);

        // Expire contest scoreboard cache
        Scoreboard::invalidateScoreboardCache(ScoreboardParams::fromContest($contest));

        // Expire contest-list cache
        \OmegaUp\Cache::invalidateAllKeys(\OmegaUp\Cache::CONTESTS_LIST_PUBLIC);
        \OmegaUp\Cache::invalidateAllKeys(\OmegaUp\Cache::CONTESTS_LIST_SYSTEM_ADMIN);

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
     * @param \OmegaUp\Request $r
     * @return array
     * @throws \OmegaUp\Exceptions\NotFoundException
     */
    public static function apiUpdateEndTimeForIdentity(\OmegaUp\Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        self::authenticateRequest($r);
        $contest = self::validateContestAdmin(
            $r['contest_alias'],
            $r->identity
        );

        \OmegaUp\Validators::validateStringNonEmpty($r['username'], 'username');
        $r->ensureInt('end_time');

        $identity = IdentityController::resolveIdentity($r['username']);
        if (is_null($identity)) {
            throw new \OmegaUp\Exceptions\NotFoundException('userNotFound');
        }

        $problemsetIdentity = ProblemsetIdentitiesDAO::getByPK(
            $identity->identity_id,
            $contest->problemset_id
        );

        $problemsetIdentity->end_time = $r['end_time'];
        ProblemsetIdentitiesDAO::update($problemsetIdentity);

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
    ) : void {
        if ($originalContest->admission_mode !== $contest->admission_mode) {
            $timestamp = \OmegaUp\Time::get();
            ContestLogDAO::create(new \OmegaUp\DAO\VO\ContestLog([
                'contest_id' => $contest->contest_id,
                'user_id' => $identity->user_id,
                'from_admission_mode' => $originalContest->admission_mode,
                'to_admission_mode' => $contest->admission_mode,
                'time' => $timestamp,
            ]));
            $contest->last_updated = $timestamp;
        }
        if (($originalContest->finish_time !== $contest->finish_time) ||
            ($originalContest->window_length !== $contest->window_length)) {
            if (!is_null($contest->window_length)) {
                // When window length is enabled, end time value is access time + window length
                ProblemsetIdentitiesDAO::recalculateEndTimeForProblemsetIdentities(
                    $contest
                );
            } else {
                ProblemsetIdentitiesDAO::recalculateEndTimeAsFinishTime($contest);
            }
        }

        ContestsDAO::update($contest);
        if ($originalContest->penalty_type == $contest->penalty_type) {
            return;
        }
        RunsDAO::recalculatePenaltyForContest($contest);
    }

    /**
     * Validates runs API
     *
     * @param \OmegaUp\Request $r
     * @return Array
     * @throws \OmegaUp\Exceptions\NotFoundException
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     */
    private static function validateRuns(\OmegaUp\Request $r) : Array {
        // Defaults for offset and rowcount
        if (!isset($r['offset'])) {
            $r['offset'] = 0;
        }
        if (!isset($r['rowcount'])) {
            $r['rowcount'] = 100;
        }

        \OmegaUp\Validators::validateStringNonEmpty($r['contest_alias'], 'contest_alias');

        $contest = self::validateContestAdmin($r['contest_alias'], $r->identity);

        $r->ensureInt('offset', null, null, false);
        $r->ensureInt('rowcount', null, null, false);
        \OmegaUp\Validators::validateInEnum($r['status'], 'status', ['new', 'waiting', 'compiling', 'running', 'ready'], false);
        \OmegaUp\Validators::validateInEnum($r['verdict'], 'verdict', ['AC', 'PA', 'WA', 'TLE', 'MLE', 'OLE', 'RTE', 'RFE', 'CE', 'JE', 'NO-AC'], false);

        $problem = null;
        // Check filter by problem, is optional
        if (!is_null($r['problem_alias'])) {
            \OmegaUp\Validators::validateStringNonEmpty($r['problem_alias'], 'problem');

            $problem = ProblemsDAO::getByAlias($r['problem_alias']);
            if (is_null($problem)) {
                throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
            }
        }

        \OmegaUp\Validators::validateInEnum($r['language'], 'language', array_keys(RunController::$kSupportedLanguages), false);

        // Get user if we have something in username
        $identity = null;
        if (!is_null($r['username'])) {
            $identity = IdentityController::resolveIdentity($r['username']);
        }
        return [$contest, $problem, $identity];
    }

    /**
     * Returns all runs for a contest
     *
     * @param \OmegaUp\Request $r
     * @return array
     */
    public static function apiRuns(\OmegaUp\Request $r) {
        // Authenticate request
        self::authenticateRequest($r);

        // Validate request
        [$contest, $problem, $identity] = self::validateRuns($r);

        // Get our runs
        $runs = RunsDAO::getAllRuns(
            $contest->problemset_id,
            $r['status'],
            $r['verdict'],
            !is_null($problem) ? $problem->problem_id : null,
            $r['language'],
            !is_null($identity) ? $identity->identity_id : null,
            $r['offset'],
            $r['rowcount']
        );

        $result = [];

        foreach ($runs as $run) {
            $run['time'] = (int)$run['time'];
            $run['score'] = round((float)$run['score'], 4);
            $run['contest_score'] = round((float)$run['contest_score'], 2);
            array_push($result, $run);
        }

        $response = [];
        $response['runs'] = $result;
        $response['status'] = 'ok';

        return $response;
    }

    /**
     * Validates that request contains contest_alias and the api is contest-admin only
     *
     * @param string $contestAlias
     * @param \OmegaUp\DAO\VO\Identities $identity
     * @return \OmegaUp\DAO\VO\Contests
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     */
    private static function validateStats(
        string $contestAlias,
        \OmegaUp\DAO\VO\Identities $identity
    ) : \OmegaUp\DAO\VO\Contests {
        \OmegaUp\Validators::validateStringNonEmpty($contestAlias, 'contest_alias');

        return self::validateContestAdmin($contestAlias, $identity);
    }

    /**
     * Stats of a problem
     *
     * @param \OmegaUp\Request $r
     * @return array
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     */
    public static function apiStats(\OmegaUp\Request $r) {
        // Get user
        self::authenticateRequest($r);

        $contest = self::validateStats($r['contest_alias'], $r->identity);

        $pendingRunGuids = RunsDAO::getPendingRunGuidsOfProblemset((int)$contest->problemset_id);

        // Count of pending runs (int)
        $totalRunsCount = SubmissionsDAO::countTotalSubmissionsOfProblemset(
            (int)$contest->problemset_id
        );

        // Wait time
        $waitTimeArray = RunsDAO::getLargestWaitTimeOfProblemset((int)$contest->problemset_id);

        // List of verdicts
        $verdictCounts = [];

        foreach (self::$verdicts as $verdict) {
            $verdictCounts[$verdict] = (int)RunsDAO::countTotalRunsOfProblemsetByVerdict(
                (int)$contest->problemset_id,
                $verdict
            );
        }

        // Get max points posible for contest
        $totalPoints = ProblemsetProblemsDAO::getMaxPointsByProblemset($contest->problemset_id);

        // Get scoreboard to calculate distribution
        $distribution = [];
        for ($i = 0; $i < 101; $i++) {
            $distribution[$i] = 0;
        }

        $sizeOfBucket = $totalPoints / 100;
        if ($sizeOfBucket > 0) {
            $scoreboardResponse = self::apiScoreboard($r);
            foreach ($scoreboardResponse['ranking'] as $results) {
                $distribution[(int)($results['total']['points'] / $sizeOfBucket)]++;
            }
        }

        return [
            'total_runs' => $totalRunsCount,
            'pending_runs' => $pendingRunGuids,
            'max_wait_time' => empty($waitTimeArray) ? 0 : $waitTimeArray['time'],
            'max_wait_time_guid' => empty($waitTimeArray) ? 0 : $waitTimeArray['guid'],
            'verdict_counts' => $verdictCounts,
            'distribution' => $distribution,
            'size_of_bucket' => $sizeOfBucket,
            'total_points' => $totalPoints,
            'status' => 'ok',
        ];
    }

    /**
     * Returns a detailed report of the contest
     *
     * @param \OmegaUp\Request $r
     * @return array
     */
    public static function apiReport(\OmegaUp\Request $r) {
        $contestReport = self::getContestReportDetails($r);

        $contestReport['status'] = 'ok';
        return $contestReport;
    }

    /**
     * Returns a detailed report of the contest. Only Admins can get the report
     *
     * @param \OmegaUp\Request $r
     * @return array
     */
    private static function getContestReportDetails(\OmegaUp\Request $r) : array {
        self::authenticateRequest($r);
        $contest = self::validateStats($r['contest_alias'], $r->identity);

        $params = ScoreboardParams::fromContest($contest);
        $params['admin'] = true;
        $params['auth_token'] = $r['auth_token'];
        $scoreboard = new Scoreboard($params);

        // Check the filter if we have one
        \OmegaUp\Validators::validateStringNonEmpty($r['filterBy'], 'filterBy', false /* not required */);

        return $scoreboard->generate(
            true, // with run details for reporting
            true, // sort contestants by name,
            (isset($r['filterBy']) ? null : $r['filterBy'])
        );
    }

    /**
     * Gets all details to show the report
     *
     * @param \OmegaUp\Request $r
     * @return array
     */
    public static function getContestReportDetailsForSmarty(\OmegaUp\Request $r) {
        $contestReport = self::getContestReportDetails($r)['ranking'];

        foreach ($contestReport as &$user) {
            if (!isset($user['problems'])) {
                continue;
            }
            foreach ($user['problems'] as &$problem) {
                if (!isset($problem['run_details']) ||
                    !isset($problem['run_details']['groups'])) {
                    continue;
                }

                foreach ($problem['run_details']['groups'] as &$group) {
                    foreach ($group['cases'] as &$case) {
                        $case['meta']['time'] = (float)$case['meta']['time'];
                        $case['meta']['time-wall'] =
                            (float)$case['meta']['time-wall'];
                        $case['meta']['mem'] =
                            (float)$case['meta']['mem'] / 1024.0 / 1024.0;
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
     *
     * @param \OmegaUp\Request $r
     * @return array
     */
    public static function apiCsvReport(\OmegaUp\Request $r) {
        self::authenticateRequest($r);

        $contest = self::validateStats($r['contest_alias'], $r->identity);

        // Get full Report API of the contest
        $contestReport = self::apiReport(new \OmegaUp\Request([
            'contest_alias' => $r['contest_alias'],
            'auth_token' => $r['auth_token'],
        ]));

        // Get problem stats for each contest problem so we can
        // have the full list of cases
        $problemStats = [];
        $i = 0;
        foreach ($contestReport['problems'] as $entry) {
            $problem_alias = $entry['alias'];
            $problemStatsRequest = new \OmegaUp\Request([
                        'problem_alias' => $problem_alias,
                        'auth_token' => $r['auth_token'],
                    ]);

            $problemStats[$i] = ProblemController::apiStats($problemStatsRequest);
            $problemStats[$problem_alias] = $problemStats[$i];

            $i++;
        }

        // Build a csv
        $csvData = [];

        // Build titles
        $csvRow = [];
        $csvRow[] = 'username';
        foreach ($contestReport['problems'] as $entry) {
            foreach ($problemStats[$entry['alias']]['cases_stats'] as $caseName => $counts) {
                $csvRow[] = $caseName;
            }
            $csvRow[] = $entry['alias'] . ' total';
        }
        $csvRow[] = 'total';
        $csvData[] = $csvRow;

        foreach ($contestReport['ranking'] as $userData) {
            if ($userData === 'ok') {
                continue;
            }

            $csvRow = [];
            $csvRow[] = $userData['username'];

            foreach ($userData['problems'] as $key => $problemData) {
                // If the user don't have these details then he didn't submit,
                // we need to fill the report with 0s for completeness
                if (!isset($problemData['run_details']['cases']) || empty($problemData['run_details']['cases'])) {
                    for ($i = 0; $i < count($problemStats[$key]['cases_stats']); $i++) {
                        $csvRow[] = '0';
                    }

                    // And adding the total for this problem
                    $csvRow[] = '0';
                } else {
                    // for each case
                    foreach ($problemData['run_details']['cases'] as $caseData) {
                        // If case is correct
                        if (strcmp($caseData['meta']['status'], 'OK') === 0 && strcmp($caseData['out_diff'], '') === 0) {
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
        header('Content-Disposition: attachment;filename=' . $r['contest_alias'] . '_report.csv');
        header('Content-Transfer-Encoding: binary');

        // Write contents to a csv raw string
        // TODO(https://github.com/omegaup/omegaup/issues/628): Escape = to prevent applications from inadvertently executing code
        // http://contextis.co.uk/blog/comma-separated-vulnerabilities/
        $out = fopen('php://output', 'w');
        foreach ($csvData as $csvRow) {
            fputcsv($out, ContestController::escapeCsv($csvRow));
        }
        fclose($out);

        // X_X
        die();
    }

    private static function escapeCsv($csvRow) {
        $escapedRow = [];
        foreach ($csvRow as $field) {
            if (is_string($field) && $field[0] == '=') {
                $escapedRow[] = "'" . $field;
            } else {
                $escapedRow[] = $field;
            }
        }
        return $escapedRow;
    }

    public static function apiDownload(\OmegaUp\Request $r) {
        self::authenticateRequest($r);

        $contest = self::validateStats($r['contest_alias'], $r->identity);

        include_once 'libs/third_party/ZipStream.php';
        $zip = new ZipStream("{$r['contest_alias']}.zip");
        ProblemsetController::downloadRuns($contest->problemset_id, $zip);
        $zip->finish();

        die();
    }

    /**
     * Given a contest_alias and user_id, returns the role of the user within
     * the context of a contest.
     *
     * @param \OmegaUp\Request $r
     * @return array
     */
    public static function apiRole(\OmegaUp\Request $r) {
        try {
            if ($r['contest_alias'] == 'all-events') {
                self::authenticateRequest($r);
                if (\OmegaUp\Authorization::isSystemAdmin($r->identity)) {
                    return [
                        'status' => 'ok',
                        'admin' => true
                    ];
                }
            }

            $response = self::validateDetails($r);

            return [
                'status' => 'ok',
                'admin' => $response['contest_admin']
            ];
        } catch (Exception $e) {
            self::$log->error('Error getting role: ' . $e);

            return [
                'status' => 'error',
                'admin' => false
            ];
        }
    }

    /**
     * Given a contest_alias, sets the recommended flag on/off.
     * Only omegaUp admins can call this API.
     *
     * @param \OmegaUp\Request $r
     * @return array
     */
    public static function apiSetRecommended(\OmegaUp\Request $r) {
        self::authenticateRequest($r);

        if (!\OmegaUp\Authorization::isSystemAdmin($r->identity)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('userNotAllowed');
        }

        // Validate & get contest_alias
        $r['contest'] = ContestsDAO::getByAlias($r['contest_alias']);
        if (is_null($r['contest'])) {
            throw new \OmegaUp\Exceptions\NotFoundException('contestNotFound');
        }

        // Validate value param
        $r->ensureBool('value');

        $r['contest']->recommended = $r['value'];

        ContestsDAO::update($r['contest']);

        return ['status' => 'ok'];
    }

    /**
     * Return users who participate in a contest, as long as contest admin
     * has chosen to ask for users information and contestants have
     * previously agreed to share their information.
     *
     * @param \OmegaUp\Request $r
     * @return array
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     */
    public static function apiContestants(\OmegaUp\Request $r) {
        self::authenticateRequest($r);

        $contest = self::validateStats($r['contest_alias'], $r->identity);

        if (!ContestsDAO::requestsUserInformation($contest->contest_id)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('contestInformationNotRequired');
        }

        // Get contestants info
        $contestants = ContestsDAO::getContestantsInfo($contest->contest_id);

        return [
            'status' => 'ok',
            'contestants' => $contestants,
        ];
    }

    public static function isPublic($admission_mode) {
        return $admission_mode != 'private';
    }
}
