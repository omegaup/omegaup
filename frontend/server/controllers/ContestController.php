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
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     */
    public static function apiList(Request $r) {
        // Check who is visiting, but a not logged user can still view
        // the list of contests
        try {
            self::authenticateRequest($r);
        } catch (UnauthorizedException $e) {
            // Do nothing.
        }

        try {
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
            Validators::validateNumber($active_contests, 'active', true /* required */);
            $recommended = isset($r['recommended'])
                ? RecommendedStatus::getIntValue($r['recommended'])
                : RecommendedStatus::ALL;
            // Same as above.
            Validators::validateNumber($recommended, 'recommended', true /* required */);
            $participating = isset($r['participating'])
                ? ParticipatingStatus::getIntValue($r['participating'])
                : ParticipatingStatus::NO;
            Validators::validateInEnum($r['admission_mode'], 'admission_mode', [
                'public',
                'private',
                'registration'
            ], false);

            // admission mode status in contest is public
            $public = isset($r['admission_mode']) && self::isPublic($r['admission_mode']);

            if (is_null($participating)) {
                throw new InvalidParameterException('parameterInvalid', 'participating');
            }
            $query = $r['query'];
            Validators::validateStringOfLengthInRange($query, 'query', null, 255, false /* not required */);
            $cache_key = "$active_contests-$recommended-$page-$page_size";
            if (is_null($r->identity)) {
                // Get all public contests
                Cache::getFromCacheOrSet(
                    Cache::CONTESTS_LIST_PUBLIC,
                    $cache_key,
                    $r,
                    function (Request $r) use ($page, $page_size, $active_contests, $recommended, $query) {
                            return ContestsDAO::getAllPublicContests($page, $page_size, $active_contests, $recommended, $query);
                    },
                    $contests
                );
            } elseif ($participating == ParticipatingStatus::YES) {
                $contests = ContestsDAO::getContestsParticipating($r->identity->identity_id, $page, $page_size, $query);
            } elseif ($public) {
                $contests = ContestsDAO::getRecentPublicContests($r->identity->identity_id, $page, $page_size, $query);
            } elseif (Authorization::isSystemAdmin($r->identity->identity_id)) {
                // Get all contests
                Cache::getFromCacheOrSet(
                    Cache::CONTESTS_LIST_SYSTEM_ADMIN,
                    $cache_key,
                    $r,
                    function (Request $r) use ($page, $page_size, $active_contests, $recommended, $query) {
                            return ContestsDAO::getAllContests($page, $page_size, $active_contests, $recommended, $query);
                    },
                    $contests
                );
            } else {
                // Get all public+private contests
                $contests = ContestsDAO::getAllContestsForIdentity($r->identity->identity_id, $page, $page_size, $active_contests, $recommended, $query);
            }
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
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
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     */
    public static function apiAdminList(Request $r) {
        self::authenticateRequest($r);

        $r->ensureInt('page', null, null, false);
        $r->ensureInt('page_size', null, null, false);

        $page = (isset($r['page']) ? intval($r['page']) : 1);
        $pageSize = (isset($r['page_size']) ? intval($r['page_size']) : 1000);

        // Create array of relevant columns
        $contests = null;
        try {
            if (Authorization::isSystemAdmin($r->identity->identity_id)) {
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
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        return [
            'status' => 'ok',
            'contests' => $contests,
        ];
    }

    /**
     * Callback to get contests list, depending on a given method
     * @param Request $r
     * @param $callback_user_function
     * @return array
     * @throws InvalidDatabaseOperationException
     */
    public static function getContestListInternal(Request $r, $callback_user_function) : Array {
        self::authenticateRequest($r);

        $r->ensureInt('page', null, null, false);
        $r->ensureInt('page_size', null, null, false);

        $page = (isset($r['page']) ? intval($r['page']) : 1);
        $pageSize = (isset($r['page_size']) ? intval($r['page_size']) : 1000);
        $query = $r['query'];
        $contests = null;
        $identity_id = $callback_user_function == 'ContestsDAO::getContestsParticipating'
          ? $r->identity->identity_id : $r->user->user_id;
        try {
            $contests = call_user_func(
                $callback_user_function,
                $identity_id,
                $page,
                $pageSize,
                $query
            );
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        $addedContests = [];
        foreach ($contests as $contest) {
            $contest['start_time'] = strtotime($contest['start_time']);
            $contest['finish_time'] = strtotime($contest['finish_time']);
            $contest['last_updated'] = strtotime($contest['last_updated']);
            $addedContests[] = $contest;
        }

        // Expire contest-list cache
        Cache::invalidateAllKeys(Cache::CONTESTS_LIST_PUBLIC);
        Cache::invalidateAllKeys(Cache::CONTESTS_LIST_SYSTEM_ADMIN);

        return [
            'status' => 'ok',
            'contests' => $addedContests,
        ];
    }

    /**
     * Returns a list of contests where current user is the director
     *
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     */
    public static function apiMyList(Request $r) {
        return self::getContestListInternal($r, 'ContestsDAO::getAllContestsOwnedByUser');
    }

    /**
     * Returns a list of contests where current user is participating in
     *
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     */
    public static function apiListParticipating(Request $r) {
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
     * @param Contests $contest
     * @param int $currentIdentityId
     * @throws ApiException
     * @throws InvalidDatabaseOperationException
     * @throws ForbiddenAccessException
     */
    public static function canAccessContest(Contests $contest, int $currentIdentityId) : void {
        if ($contest->admission_mode == 'private') {
            try {
                if (is_null(ProblemsetIdentitiesDAO::getByPK($currentIdentityId, $contest->problemset_id))
                        && !Authorization::isContestAdmin($currentIdentityId, $contest)) {
                    throw new ForbiddenAccessException('userNotAllowed');
                }
            } catch (ApiException $e) {
                // Propagate exception
                throw $e;
            } catch (Exception $e) {
                // Operation failed in the data layer
                throw new InvalidDatabaseOperationException($e);
            }
        } elseif ($contest->admission_mode == 'registration' &&
            !Authorization::isContestAdmin($currentIdentityId, $contest)
        ) {
            $req = ProblemsetIdentityRequestDAO::getByPK(
                $currentIdentityId,
                $contest->problemset_id
            );
            if (is_null($req) || ($req->accepted === '0')) {
                throw new ForbiddenAccessException('contestNotRegistered');
            }
        }
    }

    /**
     * Validate the basics of a contest request.
     *
     * @param string $contestAlias
     * @return [Contests, Problemsets]
     * @throws InvalidDatabaseOperationException
     * @throws NotFoundException
     */
    private static function validateBasicDetails(string $contestAlias) : Array {
        Validators::validateStringNonEmpty($contestAlias, 'contest_alias');
        // If the contest is private, verify that our user is invited
        try {
            $contestProblemset = ContestsDAO::getByAliasWithExtraInformation($contestAlias);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }
        if (is_null($contestProblemset)) {
            throw new NotFoundException('contestNotFound');
        }
        return [new Contests($contestProblemset), new Problemsets($contestProblemset)];
    }

    /**
     * Validate if a contestant has explicit access to a contest.
     *
     * @param Contests $contest
     * @param int $currentUserId
     * @param int $currentIdentityId
     * @return bool
     */
    private static function isInvitedToContest(Contests $contest, ?int $currentUserId, int $currentIdentityId) : bool {
        if (is_null($currentUserId)) {
            return false;
        }
        return self::isPublic($contest->admission_mode) ||
            !is_null(ProblemsetIdentitiesDAO::getByPK(
                $currentIdentityId,
                $contest->problemset_id
            ));
    }

    /**
     * Show the contest intro unless you are admin, or you
     * already started this contest.
     * @param Request $r
     * @return Array
     */
    public static function showContestIntro(Request $r) : Array {
        try {
            $contest = ContestsDAO::getByAlias($r['contest_alias']);
        } catch (Exception $e) {
            throw new NotFoundException('contestNotFound');
        }
        if (is_null($contest)) {
            throw new NotFoundException('contestNotFound');
        }
        $result = ContestsDAO::getNeedsInformation($contest->problemset_id);

        try {
            // Half-authenticate, in case there is no session in place.
            $session = SessionController::apiCurrentSession($r)['session'];
            if ($session['valid'] && !is_null($session['identity'])) {
                $r->identity = $session['identity'];

                if (!is_null($session['user'])) {
                    $r->user = $session['user'];
                }

                // Privacy Statement Information
                $result['privacy_statement_markdown'] = PrivacyStatement::getForProblemset(
                    $session['user']->language_id,
                    'contest',
                    $result['requests_user_information']
                );
                if (!is_null($result['privacy_statement_markdown'])) {
                    $statement_type = "contest_{$result['requests_user_information']}_consent";
                    $result['git_object_id'] = PrivacyStatementsDAO::getLatestPublishedStatement($statement_type)['git_object_id'];
                    $result['statement_type'] = $statement_type;
                }
            } else {
                // No session, show the intro (if public), so that they can login.
                $result['shouldShowIntro'] =
                    self::isPublic($contest->admission_mode) ? ContestController::SHOW_INTRO : !ContestController::SHOW_INTRO;
                return $result;
            }
            self::canAccessContest($contest, $r->identity->identity_id);
        } catch (Exception $e) {
            // Could not access contest. Private contests must not be leaked, so
            // unless they were manually added beforehand, show them a 404 error.
            if (!self::isInvitedToContest($contest, $r->user->user_id, $r->identity->identity_id)) {
                throw $e;
            }
            self::$log->error('Exception while trying to verify access: ' . $e);
            $result['shouldShowIntro'] = ContestController::SHOW_INTRO;
            return $result;
        }

        // You already started the contest.
        $contestOpened = ProblemsetIdentitiesDAO::getByPK(
            $r->identity->identity_id,
            $contest->problemset_id
        );
        if (!is_null($contestOpened) && !is_null($contestOpened->access_time)) {
            self::$log->debug('No intro because you already started the contest');
            $result['shouldShowIntro'] = !ContestController::SHOW_INTRO;
            return $result;
        }
        $result['shouldShowIntro'] = ContestController::SHOW_INTRO;
        return $result;
    }

    /**
     * Validate request of a details contest
     *
     * @param Request $r
     * @return [$contest, $contestAdmin]
     * @throws InvalidDatabaseOperationException
     * @throws ForbiddenAccessException
     * @throws PreconditionFailedException
     */
    public static function validateDetails(Request $r) : Array {
        [$contest, $problemset] = self::validateBasicDetails($r['contest_alias']);

        $contestAdmin = false;
        $contestAlias = '';

        // If the contest has not started, user should not see it, unless it is admin or has a token.
        if (is_null($r['token'])) {
            // Crack the request to get the current user
            self::authenticateRequest($r);
            self::canAccessContest($contest, $r->identity->identity_id);

            $contestAdmin = Authorization::isContestAdmin($r->identity->identity_id, $contest);
            if (!ContestsDAO::hasStarted($contest) && !$contestAdmin) {
                $exception = new PreconditionFailedException('contestNotStarted');
                $exception->addCustomMessageToArray('start_time', strtotime($contest->start_time));

                throw $exception;
            }
        } else {
            if ($r['token'] === $problemset->scoreboard_url_admin) {
                $contestAdmin = true;
                $contestAlias = $contest->alias;
            } elseif ($r['token'] !== $problemset->scoreboard_url) {
                throw new ForbiddenAccessException('invalidScoreboardUrl');
            }
        }
        return [
            'contest' => $contest,
            'contest_admin' => $contestAdmin,
            'contest_alias' => $contestAlias,
        ];
    }

     /**
     * Temporal hotfix wrapper
     */
    public static function apiIntroDetails(Request $r) {
        return self::apiPublicDetails($r);
    }

    public static function apiPublicDetails(Request $r) {
        Validators::validateStringNonEmpty($r['contest_alias'], 'contest_alias');

        $result = [];

        // If the contest is private, verify that our user is invited
        try {
            $r['contest'] = ContestsDAO::getByAlias($r['contest_alias']);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        if (is_null($r['contest'])) {
            throw new NotFoundException('contestNotFound');
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

        $result['start_time'] = strtotime($result['start_time']);
        $result['finish_time'] = strtotime($result['finish_time']);

        $result['status'] = 'ok';

        return $result;
    }

    public static function apiRegisterForContest(Request $r) {
        // Authenticate request
        self::authenticateRequest($r);

        [$contest, $_] = self::validateBasicDetails($r['contest_alias']);

        try {
            ProblemsetIdentityRequestDAO::save(new ProblemsetIdentityRequest([
                'identity_id' => $r->identity->identity_id,
                'problemset_id' => $contest->problemset_id,
                'request_time' => gmdate('Y-m-d H:i:s'),
            ]));
        } catch (Exception $e) {
            self::$log->error('Failed to create new ProblemsetIdentityRequest: ' . $e->getMessage());
            throw new InvalidDatabaseOperationException($e);
        }

        return ['status' => 'ok'];
    }

    /**
     * Joins a contest - explicitly adds a identity to a contest.
     *
     * @param Request $r
     * @throws ForbiddenAccessException
     */
    public static function apiOpen(Request $r) {
        $response = self::validateDetails($r);
        $needsInformation = ContestsDAO::getNeedsInformation($response['contest']->problemset_id);
        $session = SessionController::apiCurrentSession($r)['session'];

        if ($needsInformation['needs_basic_information'] && !is_null($session['identity']) &&
              (!$session['identity']->country_id || !$session['identity']->state_id || !$session['identity']->school_id)
        ) {
            throw new ForbiddenAccessException('contestBasicInformationNeeded');
        }

        DAO::transBegin();
        try {
            ProblemsetIdentitiesDAO::checkAndSaveFirstTimeAccess(
                $r->identity->identity_id,
                $response['contest']->problemset_id,
                true,
                $r['share_user_information']
            );

            // Insert into PrivacyStatement_Consent_Log whether request
            // user info is optional or required
            if ($needsInformation['requests_user_information'] != 'no') {
                $privacystatement_id = PrivacyStatementsDAO::getId($r['privacy_git_object_id'], $r['statement_type']);
                $privacystatement_consent_id = PrivacyStatementConsentLogDAO::saveLog(
                    $r->identity->identity_id,
                    $privacystatement_id
                );

                ProblemsetIdentitiesDAO::updatePrivacyStatementConsent(new ProblemsetIdentities([
                    'identity_id' => $r->identity->identity_id,
                    'problemset_id' => $response['contest']->problemset_id,
                    'privacystatement_consent_id' => $privacystatement_consent_id
                ]));
            }

            DAO::transEnd();
        } catch (Exception $e) {
            DAO::transRollback();
            throw new InvalidDatabaseOperationException($e);
        }

        self::$log->info("User '{$r->identity->username}' joined contest '{$response['contest']->alias}'");
        return ['status' => 'ok'];
    }

    /**
     * Returns details of a Contest. This is shared between apiDetails and
     * apiAdminDetails.
     *
     * @param string $contestAlias
     * @param Contests $contest
     * @param $result
     */
    private static function getCachedDetails(string $contestAlias, Contests $contest, &$result) {
        Cache::getFromCacheOrSet(Cache::CONTEST_INFO, $contestAlias, $contest, function (Contests $contest) {
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

            $result['start_time'] = strtotime($result['start_time']);
            $result['finish_time'] = strtotime($result['finish_time']);
            $result['show_scoreboard_after'] = (bool)$result['show_scoreboard_after'];
            $result['original_contest_alias'] = null;
            $result['original_problemset_id'] = null;
            if ($result['rerun_id'] != 0) {
                $original_contest = ContestsDAO::getByPK($result['rerun_id']);
                $result['original_contest_alias'] = $original_contest->alias;
                $result['original_problemset_id'] = $original_contest->problemset_id;
            }

            try {
                $acl = ACLsDAO::getByPK($contest->acl_id);
                $result['director'] = UsersDAO::getByPK($acl->owner_id)->username;
            } catch (Exception $e) {
                // Operation failed in the data layer
                throw new InvalidDatabaseOperationException($e);
            }

            try {
                $problemsInContest = ProblemsetProblemsDAO::getProblemsByProblemset($contest->problemset_id);
            } catch (Exception $e) {
                // Operation failed in the data layer
                throw new InvalidDatabaseOperationException($e);
            }

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
            $result = array_merge(
                $result,
                ContestsDAO::getNeedsInformation($contest->problemset_id)
            );
            return $result;
        }, $result, APC_USER_CACHE_CONTEST_INFO_TIMEOUT);
    }

    /**
     * Returns details of a Contest. Requesting the details of a contest will
     * not start the current user into that contest. In order to participate
     * in the contest, ContestController::apiOpen() must be used.
     *
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     */
    public static function apiDetails(Request $r) {
        $response = self::validateDetails($r);

        $result = [];
        self::getCachedDetails($r['contest_alias'], $response['contest'], $result);
        unset($result['scoreboard_url']);
        unset($result['scoreboard_url_admin']);
        unset($result['rerun_id']);
        if (is_null($r['token'])) {
            // Adding timer info separately as it depends on the current user and we don't
            // want this to get generally cached for everybody
            // Save the time of the first access
            try {
                $problemset_user = ProblemsetIdentitiesDAO::checkAndSaveFirstTimeAccess(
                    $r->identity->identity_id,
                    $response['contest']->problemset_id
                );
            } catch (ApiException $e) {
                throw $e;
            } catch (Exception $e) {
                // Operation failed in the data layer
                throw new InvalidDatabaseOperationException($e);
            }

            // Add time left to response
            if ($response['contest']->window_length === null) {
                $result['submission_deadline'] = strtotime($response['contest']->finish_time);
            } else {
                $result['submission_deadline'] = min(
                    strtotime($response['contest']->finish_time),
                    strtotime($problemset_user->access_time) + $response['contest']->window_length * 60
                );
            }
            $result['admin'] = Authorization::isContestAdmin($r->identity->identity_id, $response['contest']);

            // Log the operation.
            ProblemsetAccessLogDAO::create(new ProblemsetAccessLog([
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
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     */
    public static function apiAdminDetails(Request $r) {
        $response = self::validateDetails($r);

        if (!Authorization::isContestAdmin($r->identity->identity_id, $response['contest'])) {
            throw new ForbiddenAccessException();
        }

        $result = [];
        self::getCachedDetails($r['contest_alias'], $response['contest'], $result);

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
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     */
    public static function apiActivityReport(Request $r) {
        $response = self::validateDetails($r);

        if (!$response['contest_admin']) {
            throw new ForbiddenAccessException();
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
     * @throws InvalidParameterException
     * @throws DuplicatedEntryInDatabaseException
     * @throws InvalidDatabaseOperationException
     */
    public static function apiClone(Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        // Authenticate user
        self::authenticateRequest($r);

        $originalContest = self::validateContestAdmin($r['contest_alias'], $r->identity->identity_id);

        $length = strtotime($originalContest->finish_time) -
                  strtotime($originalContest->start_time);

        $auth_token = isset($r['auth_token']) ? $r['auth_token'] : null;

        $problemset = new Problemsets([
            'needs_basic_information' => false,
            'requests_user_information' => 'no',
        ]);

        $contest = new Contests([
            'title' => $r['title'],
            'description' => $r['description'],
            'alias' => $r['alias'],
            'start_time' => gmdate('Y-m-d H:i:s', $r['start_time']),
            'finish_time' => gmdate('Y-m-d H:i:s', $r['start_time'] + $length),
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

        DAO::transBegin();
        try {
            // Create the contest
            self::createContest($problemset, $contest, $r->user->user_id);

            $problemsetProblems = ProblemsetProblemsDAO::getProblemsetProblems(
                $originalContest->problemset_id
            );
            foreach ($problemsetProblems as $problemsetProblem) {
                $problem = new Problems([
                    'problem_id' => $problemsetProblem['problem_id'],
                    'alias' => $problemsetProblem['alias'],
                    'visibility' => $problemsetProblem['visibility'],
                ]);
                ProblemsetController::addProblem(
                    $contest->problemset_id,
                    $problem,
                    $problemsetProblem['commit'],
                    $problemsetProblem['version'],
                    $r->identity->identity_id,
                    $problemsetProblem['points'],
                    $problemsetProblem['order'] ?: 1
                );
            }
            DAO::transEnd();
        } catch (InvalidParameterException $e) {
            DAO::transRollback();
            throw $e;
        } catch (DuplicatedEntryInDatabaseException $e) {
            DAO::transRollback();
            throw $e;
        } catch (Exception $e) {
            DAO::transRollback();
            throw new InvalidDatabaseOperationException($e);
        }

        return ['status' => 'ok', 'alias' => $r['alias']];
    }

    public static function apiCreateVirtual(Request $r) {
        global $experiments;
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        // Authenticate user
        self::authenticateRequest($r);

        try {
            $originalContest = ContestsDAO::getByAlias($r['alias']);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        if (is_null($originalContest)) {
            throw new NotFoundException('contestNotFound');
        }

        $startTime = strtotime($originalContest->start_time);
        $finishTime = strtotime($originalContest->finish_time);

        if ($finishTime > Time::get()) {
            throw new ForbiddenAccessException('originalContestHasNotEnded');
        }

        $virtualContestAlias = ContestsDAO::generateAlias($originalContest);

        $contestLength = $finishTime - $startTime;

        $r->ensureInt('start_time', null, null, false);
        $r['start_time'] = !is_null($r['start_time']) ? $r['start_time'] : Time::get();

        // Initialize contest
        $contest = new Contests();
        $contest->title = $originalContest->title;
        $contest->description = $originalContest->description;
        $contest->window_length = $originalContest->window_length;
        $contest->public = 0; // Virtual contest must be private
        $contest->start_time = gmdate('Y-m-d H:i:s', $r['start_time']);
        $contest->finish_time = gmdate('Y-m-d H:i:s', $r['start_time'] + $contestLength);
        $contest->scoreboard = 100; // Always show scoreboard in virtual contest
        $contest->alias = $virtualContestAlias;
        $contest->points_decay_factor = $originalContest->points_decay_factor;
        $contest->submissions_gap = $originalContest->submissions_gap;
        $contest->partial_score = $originalContest->partial_score;
        $contest->feedback = $originalContest->feedback;
        $contest->penalty = $originalContest->penalty;
        $contest->penalty_type = $originalContest->penalty_type;
        $contest->penalty_calc_policy = $originalContest->penalty_calc_policy;
        $contest->show_scoreboard_after = true;
        $contest->languages = $originalContest->languages;
        $contest->rerun_id = $originalContest->contest_id;

        $problemset = new Problemsets([
            'needs_basic_information' => false,
            'requests_user_information' => 'no',
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
     * @param Problemsets $problemset
     * @param Contests $contest
     * @param int $currentUserId
     * @param int $originalProblemsetId
     * @param Problemsets $problemset
     */
    private static function createContest(
        Problemsets $problemset,
        Contests $contest,
        int $currentUserId,
        ?int $originalProblemsetId = null
    ) : void {
        $acl = new ACLs();
        $acl->owner_id = $currentUserId;
        // Push changes
        try {
            // Begin a new transaction
            DAO::transBegin();

            ACLsDAO::save($acl);
            $problemset->acl_id = $acl->acl_id;
            $problemset->type = 'Contest';
            $problemset->scoreboard_url = SecurityTools::randomString(30);
            $problemset->scoreboard_url_admin = SecurityTools::randomString(30);
            $contest->acl_id = $acl->acl_id;

            // Save the problemset object with data sent by user to the database
            ProblemsetsDAO::save($problemset);

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
            ContestsDAO::save($contest);

            // Update contest_id in problemset object
            $problemset->contest_id = $contest->contest_id;
            ProblemsetsDAO::save($problemset);

            // End transaction transaction
            DAO::transEnd();
        } catch (Exception $e) {
            // Operation failed in the data layer, rollback transaction
            DAO::transRollback();

            if (DAO::isDuplicateEntryException($e)) {
                throw new DuplicatedEntryInDatabaseException('aliasInUse', $e);
            } else {
                throw new InvalidDatabaseOperationException($e);
            }
        }

        // Expire contest-list cache
        Cache::invalidateAllKeys(Cache::CONTESTS_LIST_PUBLIC);
        Cache::invalidateAllKeys(Cache::CONTESTS_LIST_SYSTEM_ADMIN);

        self::$log->info('New Contest Created: ' . $contest->alias);
    }

    /**
     * Creates a new contest
     *
     * @param Request $r
     * @return array
     * @throws DuplicatedEntryInDatabaseException
     * @throws InvalidDatabaseOperationException
     */
    public static function apiCreate(Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        // Authenticate user
        self::authenticateRequest($r);

        // Validate request
        self::validateCreate($r);

        // Set private contest by default if is not sent in request
        if (!is_null($r['admission_mode']) && $r['admission_mode'] != 'private') {
            throw new InvalidParameterException('contestMustBeCreatedInPrivateMode');
        }

        $problemset = new Problemsets([
            'needs_basic_information' => $r['basic_information'] == 'true',
            'requests_user_information' => $r['requests_user_information'],
        ]);

        $languages = empty($r['languages']) ? null : join(',', $r['languages']);
        $contest = new Contests([
            'admission_mode' => 'private',
            'title' => $r['title'],
            'description' => $r['description'],
            'start_time' => gmdate('Y-m-d H:i:s', $r['start_time']),
            'finish_time' => gmdate('Y-m-d H:i:s', $r['finish_time']),
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
     * @param Request $r
     * @throws InvalidParameterException
     */
    private static function validateCommonCreateOrUpdate(Request $r, ?Contests $contest = null, bool $isRequired = true) : void {
        Validators::validateStringNonEmpty($r['title'], 'title', $isRequired);
        Validators::validateStringNonEmpty($r['description'], 'description', $isRequired);

        $r->ensureInt('start_time', null, null, $isRequired);
        $r->ensureInt('finish_time', null, null, $isRequired);

        // Get the actual start and finish time of the contest, considering that
        // in case of update, parameters can be optional
        $start_time = !is_null($r['start_time']) ? $r['start_time'] : strtotime($contest->start_time);
        $finish_time = !is_null($r['finish_time']) ? $r['finish_time'] : strtotime($contest->finish_time);

        // Validate start & finish time
        if ($start_time > $finish_time) {
            throw new InvalidParameterException('contestNewInvalidStartTime');
        }

        // Calculate the actual contest length
        $contest_length = $finish_time - $start_time;

        // Validate max contest length
        if ($contest_length > ContestController::MAX_CONTEST_LENGTH_SECONDS) {
            throw new InvalidParameterException('contestLengthTooLong');
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

        Validators::validateInEnum($r['admission_mode'], 'admission_mode', [
            'public',
            'private',
            'registration'
        ], false);
        Validators::validateValidAlias($r['alias'], 'alias', $isRequired);
        $r->ensureFloat('scoreboard', 0, 100, $isRequired);
        $r->ensureFloat('points_decay_factor', 0, 1, $isRequired);
        $r->ensureBool('partial_score', false);
        $r->ensureInt('submissions_gap', 0, null, $isRequired);
        // Validate the submission_gap in minutes so that the error message
        // matches what is displayed in the UI.
        Validators::validateNumberInRange(
            $r['submissions_gap'] == null ? null : floor($r['submissions_gap']/60),
            'submissions_gap',
            1,
            floor($contest_length / 60),
            $isRequired
        );

        Validators::validateInEnum($r['feedback'], 'feedback', ['no', 'yes', 'partial'], $isRequired);
        Validators::validateInEnum($r['penalty_type'], 'penalty_type', ['contest_start', 'problem_open', 'runtime', 'none'], $isRequired);
        Validators::validateInEnum($r['penalty_calc_policy'], 'penalty_calc_policy', ['sum', 'max'], false);

        // Problems is optional
        if (!is_null($r['problems'])) {
            $request_problems = json_decode($r['problems']);
            if (is_null($request_problems)) {
                throw new InvalidParameterException('invalidParameters', 'problems');
            }

            $problems = [];

            foreach ($request_problems as $problem) {
                $p = ProblemsDAO::getByAlias($problem->problem);
                if (is_null($p)) {
                    throw new InvalidParameterException('parameterNotFound', 'problems');
                }
                ProblemsetController::validateAddProblemToProblemset(null, $p, $r->identity->identity_id);
                array_push($problems, [
                    'id' => $p->problem_id,
                    'alias' => $problem->problem,
                    'points' => $problem->points
                ]);
            }

            $r['problems'] = $problems;
        }

        // Show scoreboard is always optional
        $r->ensureBool('show_scoreboard_after', false);

        // languages is always optional
        if (!empty($r['languages'])) {
            foreach ($r['languages'] as $language) {
                Validators::validateInEnum($language, 'languages', array_keys(RunController::$kSupportedLanguages), false);
            }
        }
    }

    /**
     * Validates that Request contains expected data to create a contest
     * In case of error, this function throws.
     *
     * @param Request $r
     * @throws InvalidParameterException
     */
    private static function validateCreate(Request $r) : void {
        self::validateCommonCreateOrUpdate($r);
    }

    /**
     * Validates that Request contains expected data to update a contest
     * everything is optional except the contest_alias
     * In case of error, this function throws.
     *
     * @param Request $r
     * @return Contests
     * @throws InvalidParameterException
     */
    private static function validateUpdate(Request $r) : Contests {
        $contest = self::validateContestAdmin($r['contest_alias'], $r->identity->identity_id);

        self::validateCommonCreateOrUpdate($r, $contest, false /* is required*/);

        // Prevent date changes if a contest already has runs
        if (!is_null($r['start_time']) && $r['start_time'] != strtotime($contest->start_time)) {
            $runCount = 0;

            try {
                $runCount = SubmissionsDAO::countTotalSubmissionsOfProblemset(
                    (int)$contest->problemset_id
                );
            } catch (Exception $e) {
                throw new InvalidDatabaseOperationException($e);
            }

            if ($runCount > 0) {
                throw new InvalidParameterException('contestUpdateAlreadyHasRuns');
            }
        }
        return $contest;
    }

    /**
     * Function created to be called for all the API's that only can access
     * admins or contest organizers.
     *
     * @param string $contestAlias
     * @param int $currentIdentityId
     * @return Contests
     * @throws InvalidDatabaseOperationException
     * @throws NotFoundException
     * @throws ForbiddenAccessException
     */
    private static function validateContestAdmin(
        string $contestAlias,
        int $currentIdentityId,
        string $message = 'userNotAllowed'
    ) : Contests {
        try {
            $contest = ContestsDAO::getByAlias($contestAlias);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        if (is_null($contest)) {
            throw new NotFoundException('contestNotFound');
        }

        if (!Authorization::isContestAdmin($currentIdentityId, $contest)) {
            throw new ForbiddenAccessException($message);
        }
        return $contest;
    }

    /**
     * This function is used to restrict API in virtual contest
     *
     * @param Request $r
     * @return void
     * @throws ForbiddenAccessException
     */
    private static function forbiddenInVirtual(Contests $contest) {
        if (ContestsDAO::isVirtual($contest)) {
            throw new ForbiddenAccessException('forbiddenInVirtualContest');
        }
    }

    /**
     * Gets the problems from a contest
     *
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     */
    public static function apiProblems(Request $r) {
        // Authenticate user
        self::authenticateRequest($r);

        Validators::validateStringNonEmpty($r['contest_alias'], 'contest_alias');

        // Only director is allowed to create problems in contest
        $contest = self::validateContestAdmin($r['contest_alias'], $r->identity->identity_id, 'cannotAddProb');

        $problemset = ProblemsetsDAO::getByPK($contest->problemset_id);

        try {
            $problems = ProblemsetProblemsDAO::getProblemsetProblems($problemset->problemset_id);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        return ['status' => 'ok', 'problems' => $problems];
    }

    /**
     * Adds a problem to a contest
     *
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     */
    public static function apiAddProblem(Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        // Authenticate user
        self::authenticateRequest($r);

        // Validate the request and get the problem and the contest in an array
        $params = self::validateAddToContestRequest($r, $r['contest_alias'], $r['problem_alias'], $r->identity->identity_id);

        self::forbiddenInVirtual($params['contest']);

        $problemset = ProblemsetsDAO::getByPK($params['contest']->problemset_id);

        if (ProblemsetProblemsDAO::countProblemsetProblems($problemset)
                >= MAX_PROBLEMS_IN_CONTEST) {
            throw new PreconditionFailedException('contestAddproblemTooManyProblems');
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
            $r->identity->identity_id,
            $r['points'],
            is_null($r['order_in_contest']) ? 1 : $r['order_in_contest']
        );

        // Invalidar cache
        Cache::deleteFromCache(Cache::CONTEST_INFO, $r['contest_alias']);
        Scoreboard::invalidateScoreboardCache(ScoreboardParams::fromContest($params['contest']));

        return ['status' => 'ok'];
    }

    /**
     * Validates the request for AddToContest and returns an array with
     * the problem and contest DAOs
     *
     * @param Request $r
     * @param string $contestAlias
     * @param string $problemAlias
     * @param int $currentIdentityId
     * @return Array
     * @throws InvalidDatabaseOperationException
     * @throws InvalidParameterException
     * @throws ForbiddenAccessException
     */
    private static function validateAddToContestRequest(
        Request $r,
        string $contestAlias,
        ?string $problemAlias,
        int $currentIdentityId
    ) : Array {
        Validators::validateStringNonEmpty($contestAlias, 'contest_alias');

        // Only director is allowed to create problems in contest
        try {
            $contest = ContestsDAO::getByAlias($r['contest_alias']);
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }
        if (is_null($contest)) {
            throw new InvalidParameterException('parameterNotFound', 'contest_alias');
        }
        // Only contest admin is allowed to create problems in contest
        if (!Authorization::isContestAdmin($r->identity->identity_id, $contest)) {
            throw new ForbiddenAccessException('cannotAddProb');
        }

        Validators::validateStringNonEmpty($problemAlias, 'problem_alias');

        try {
            $problem = ProblemsDAO::getByAlias($problemAlias);
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }

        if (is_null($problem)) {
            throw new InvalidParameterException('parameterNotFound', 'problem_alias');
        }

        if ($problem->visibility == ProblemController::VISIBILITY_PRIVATE_BANNED
            || $problem->visibility == ProblemController::VISIBILITY_PUBLIC_BANNED) {
            throw new ForbiddenAccessException('problemIsBanned');
        }
        if (!ProblemsDAO::isVisible($problem) && !Authorization::isProblemAdmin($currentIdentityId, $problem)) {
            throw new ForbiddenAccessException('problemIsPrivate');
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
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     */
    public static function apiRemoveProblem(Request $r) {
        // Authenticate user
        self::authenticateRequest($r);

        // Validate the request and get the problem and the contest in an array
        $params = self::validateRemoveFromContestRequest($r['contest_alias'], $r['problem_alias'], $r->identity->identity_id);

        self::forbiddenInVirtual($params['contest']);

        try {
            $relationship = new ProblemsetProblems([
                'problemset_id' => $params['contest']->problemset_id,
                'problem_id' => $params['problem']->problem_id
            ]);

            ProblemsetProblemsDAO::delete($relationship);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        // Invalidar cache
        Cache::deleteFromCache(Cache::CONTEST_INFO, $r['contest_alias']);
        Scoreboard::invalidateScoreboardCache(ScoreboardParams::fromContest($params['contest']));

        return ['status' => 'ok'];
    }

    /**
     * Validates the request for RemoveFromContest and returns an array with
     * the problem and contest DAOs
     *
     * @param string $contestAlias
     * @param string $problemAlias
     * @param int $currentIdentityId
     * @return Array
     * @throws InvalidDatabaseOperationException
     * @throws InvalidParameterException
     * @throws ForbiddenAccessException
     */
    private static function validateRemoveFromContestRequest(
        string $contestAlias,
        ?string $problemAlias,
        int $currentIdentityId
    ) : Array {
        Validators::validateStringNonEmpty($contestAlias, 'contest_alias');

        try {
            $contest = ContestsDAO::getByAlias($contestAlias);
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }
        if (is_null($contest)) {
            throw new InvalidParameterException('parameterNotFound', 'problem_alias');
        }
        // Only contest admin is allowed to remove problems in contest
        if (!Authorization::isContestAdmin($currentIdentityId, $contest)) {
            throw new ForbiddenAccessException('cannotRemoveProblem');
        }

        Validators::validateStringNonEmpty($problemAlias, 'problem_alias');

        try {
            $problem = ProblemsDAO::getByAlias($problemAlias);
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }

        if (is_null($problem)) {
            throw new InvalidParameterException('parameterNotFound', 'problem_alias');
        }

        // Disallow removing problem from contest if it already has runs within the contest
        if (SubmissionsDAO::countTotalRunsOfProblemInProblemset(
            (int)$problem->problem_id,
            (int)$contest->problemset_id
        ) > 0 &&
            !Authorization::isSystemAdmin($currentIdentityId)) {
            throw new ForbiddenAccessException('cannotRemoveProblemWithSubmissions');
        }

        if (self::isPublic($contest->admission_mode)) {
            // Check that contest has at least 2 problems
            $problemset = ProblemsetsDAO::getByPK($contest->problemset_id);
            $problemsInContest = ProblemsetProblemsDAO::GetRelevantProblems($problemset);
            if (count($problemsInContest) < 2) {
                throw new InvalidParameterException('contestPublicRequiresProblem');
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
    public static function apiRunsDiff(Request $r) : array {
        self::authenticateRequest($r);

        Validators::validateValidAlias($r['problem_alias'], 'problem_alias');
        Validators::validateStringNonEmpty($r['contest_alias'], 'contest_alias');
        Validators::validateStringNonEmpty($r['version'], 'version');

        $contest = self::validateContestAdmin($r['contest_alias'], $r->identity->identity_id);

        try {
            $problem = ProblemsDAO::getByAlias($r['problem_alias']);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }
        if (is_null($problem)) {
            throw new NotFoundException('problemNotFound');
        }

        $problemsetProblem = ProblemsetProblemsDAO::getByPK(
            (int)$contest->problemset_id,
            (int)$problem->problem_id
        );
        if (is_null($problemsetProblem)) {
            throw new NotFoundException('recordNotFound');
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
     * @param int $currentIdentityId
     * @return Array
     * @throws InvalidDatabaseOperationException
     * @throws InvalidParameterException
     * @throws ForbiddenAccessException
     */
    private static function validateAddRemoveUser(
        string $contestAlias,
        string $usernameOrEmail,
        int $currentIdentityId
    ) : Array {
        // Check contest_alias
        Validators::validateStringNonEmpty($contestAlias, 'contest_alias');

        $identity = IdentityController::resolveIdentity($usernameOrEmail);

        if (is_null($identity)) {
            throw new NotFoundException('userOrMailNotFound');
        }

        $contest = self::validateContestAdmin($contestAlias, $currentIdentityId);
        return [$identity, $contest];
    }

    /**
     * Adds a user to a contest.
     * By default, any user can view details of public contests.
     * Only users added through this API can view private contests
     *
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     * @throws ForbiddenAccessException
     */
    public static function apiAddUser(Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        // Authenticate logged user
        self::authenticateRequest($r);
        [$identity, $contest] = self::validateAddRemoveUser(
            $r['contest_alias'],
            $r['usernameOrEmail'],
            $r->identity->identity_id
        );

        // Save the contest to the DB
        try {
            ProblemsetIdentitiesDAO::save(new ProblemsetIdentities([
                'problemset_id' => $contest->problemset_id,
                'identity_id' => $identity->identity_id,
                'access_time' => null,
                'score' => '0',
                'time' => '0',
                'is_invited' => '1',
            ]));
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        return ['status' => 'ok'];
    }

    /**
     * Remove a user from a private contest
     *
     * @param Request $r
     * @return type
     * @throws InvalidDatabaseOperationException
     */
    public static function apiRemoveUser(Request $r) {
        // Authenticate logged user
        self::authenticateRequest($r);
        [$identity, $contest] = self::validateAddRemoveUser($r['contest_alias'], $r['usernameOrEmail'], $r->identity->identity_id);

        try {
            ProblemsetIdentitiesDAO::delete(new ProblemsetIdentities([
                'problemset_id' => $contest->problemset_id,
                'identity_id' => $identity->identity_id,
            ]));
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        return ['status' => 'ok'];
    }

    /**
     * Adds an admin to a contest
     *
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     * @throws ForbiddenAccessException
     */
    public static function apiAddAdmin(Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        // Authenticate logged user
        self::authenticateRequest($r);

        // Check contest_alias
        Validators::validateStringNonEmpty($r['contest_alias'], 'contest_alias');

        $user = UserController::resolveUser($r['usernameOrEmail']);

        $contest = self::validateContestAdmin($r['contest_alias'], $r->identity->identity_id);

        ACLController::addUser($contest->acl_id, $user->user_id);

        return ['status' => 'ok'];
    }

    /**
     * Removes an admin from a contest
     *
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     * @throws ForbiddenAccessException
     */
    public static function apiRemoveAdmin(Request $r) {
        // Authenticate logged user
        self::authenticateRequest($r);

        // Check contest_alias
        Validators::validateStringNonEmpty($r['contest_alias'], 'contest_alias');

        $user = UserController::resolveUser($r['usernameOrEmail']);

        $contest = self::validateContestAdmin($r['contest_alias'], $r->identity->identity_id);

        // Check if admin to delete is actually an admin
        if (!Authorization::isContestAdmin($user->main_identity_id, $contest)) {
            throw new NotFoundException();
        }

        ACLController::removeUser($contest->acl_id, $user->user_id);

        return ['status' => 'ok'];
    }

    /**
     * Adds an group admin to a contest
     *
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     * @throws ForbiddenAccessException
     */
    public static function apiAddGroupAdmin(Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        // Authenticate logged user
        self::authenticateRequest($r);

        // Check contest_alias
        Validators::validateStringNonEmpty($r['contest_alias'], 'contest_alias');

        $group = GroupsDAO::FindByAlias($r['group']);

        if ($group == null) {
            throw new InvalidParameterException('invalidParameters');
        }

        $contest = self::validateContestAdmin($r['contest_alias'], $r->identity->identity_id);

        ACLController::addGroup($contest->acl_id, $group->group_id);

        return ['status' => 'ok'];
    }

    /**
     * Removes a group admin from a contest
     *
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     * @throws ForbiddenAccessException
     */
    public static function apiRemoveGroupAdmin(Request $r) {
        // Authenticate logged user
        self::authenticateRequest($r);

        // Check contest_alias
        Validators::validateStringNonEmpty($r['contest_alias'], 'contest_alias');

        $group = GroupsDAO::FindByAlias($r['group']);

        if ($group == null) {
            throw new InvalidParameterException('invalidParameters');
        }

        $contest = self::validateContestAdmin($r['contest_alias'], $r->identity->identity_id);

        ACLController::removeGroup($contest->acl_id, $group->group_id);

        return ['status' => 'ok'];
    }

    /**
     * Validate the Clarifications request
     *
     * @param Request $r
     * @return Contests
     * @throws InvalidDatabaseOperationException
     */
    private static function validateClarifications(Request $r) : Contests {
        // Check contest_alias
        Validators::validateStringNonEmpty($r['contest_alias'], 'contest_alias');

        try {
            $contest = ContestsDAO::getByAlias($r['contest_alias']);
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }

        if (is_null($contest)) {
            throw new NotFoundException('contestNotFound');
        }

        $r->ensureInt('offset', null, null, false /* optional */);
        $r->ensureInt('rowcount', null, null, false /* optional */);

        return $contest;
    }

    /**
     *
     * Get clarifications of a contest
     *
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     */
    public static function apiClarifications(Request $r) {
        self::authenticateRequest($r);
        $contest = self::validateClarifications($r);

        $is_contest_director = Authorization::isContestAdmin(
            $r->identity->identity_id,
            $contest
        );

        try {
            $clarifications = ClarificationsDAO::GetProblemsetClarifications(
                $contest->problemset_id,
                $is_contest_director,
                $r->identity->identity_id,
                $r['offset'],
                $r['rowcount']
            );
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }

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
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     * @throws NotFoundException
     */
    public static function apiScoreboardEvents(Request $r) {
        // Get the current user
        $response = self::validateDetails($r);

        $params = ScoreboardParams::fromContest($response['contest']);
        $params['admin'] = (
            Authorization::isContestAdmin($r->identity->identity_id, $response['contest']) &&
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
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     * @throws NotFoundException
     */
    public static function apiScoreboard(Request $r) {
        [$contest, $problemset] = self::validateBasicDetails($r['contest_alias']);

        // If true, will override Scoreboard Pertentage to 100%
        $showAllRuns = false;

        if (is_null($r['token'])) {
            // Get the current user
            self::authenticateRequest($r);

            self::canAccessContest($contest, $r->identity->identity_id);

            if (Authorization::isContestAdmin($r->identity->identity_id, $contest)) {
                $showAllRuns = true;
            }
        } else {
            if ($r['token'] === $problemset->scoreboard_url) {
                $showAllRuns = false;
            } elseif ($r['token'] === $problemset->scoreboard_url_admin) {
                $showAllRuns = true;
            } else {
                throw new ForbiddenAccessException('invalidScoreboardUrl');
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
     * @param Request $r
     */
    public static function apiScoreboardMerge(Request $r) {
        // Get the current user
        self::authenticateRequest($r);

        Validators::validateStringNonEmpty($r['contest_aliases'], 'contest_aliases');
        $contest_aliases = explode(',', $r['contest_aliases']);

        Validators::validateStringNonEmpty($r['usernames_filter'], 'usernames_filter', false);

        $usernames_filter = [];
        if (isset($r['usernames_filter'])) {
            $usernames_filter = explode(',', $r['usernames_filter']);
        }

        // Validate all contest alias
        $contests = [];
        foreach ($contest_aliases as $contest_alias) {
            try {
                $contest = ContestsDAO::getByAlias($contest_alias);
            } catch (Exception $e) {
                // Operation failed in the data layer
                throw new InvalidDatabaseOperationException($e);
            }

            if (is_null($contest)) {
                throw new NotFoundException('contestNotFound');
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

    public static function apiRequests(Request $r) {
        // Authenticate request
        self::authenticateRequest($r);

        Validators::validateStringNonEmpty($r['contest_alias'], 'contest_alias');

        $contest = self::validateContestAdmin($r['contest_alias'], $r->identity->identity_id);

        try {
            $db_results = ProblemsetIdentityRequestDAO::getRequestsForProblemset($contest->problemset_id);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        // @TODO prefetch an alias-user_id map so that we dont need
        // a getbypk (sql select query) on every iteration of the following loop

        // Precalculate all admin profiles.
        $admin_infos = [];
        foreach ($db_results as $result) {
            $admin_id = $result['admin_id'];
            if (!array_key_exists($admin_id, $admin_infos)) {
                $data = UsersDAO::getByPK($admin_id);
                if (!is_null($data)) {
                    $admin_infos[$admin_id]['user_id'] = $data->user_id;
                    $admin_infos[$admin_id]['username'] = $data->username;
                    $admin_infos[$admin_id]['name'] = $data->name;
                }
            }
        }

        $users = [];
        foreach ($db_results as $result) {
            $admin_id = $result['admin_id'];

            $result = new ProblemsetIdentityRequest($result);
            $identity_id = $result->identity_id;
            $user = IdentitiesDAO::getByPK($identity_id);

            // Get user profile. Email, school, etc.
            $profile_request = new Request();
            $profile_request['username'] = $user->username;
            $profile_request['omit_rank'] = true;

            $userprofile = UserController::apiProfile($profile_request);
            $adminprofile = [];

            if (array_key_exists($admin_id, $admin_infos)) {
                $adminprofile = $admin_infos[$admin_id];
            }

            $users[] = array_merge(
                $userprofile['userinfo'],
                [
                    'last_update' => $result->last_update,
                    'accepted' => $result->accepted,
                    'extra_note' => $result->extra_note,
                    'admin' => $adminprofile,
                    'request_time' => $result->request_time]
            );
        }

        $response = [];
        $response['users'] = $users;
        $response['contest_alias'] = $r['contest_alias'];
        $response['status'] = 'ok';

        return $response;
    }

    public static function apiArbitrateRequest(Request $r) {
        self::authenticateRequest($r);

        Validators::validateStringNonEmpty($r['contest_alias'], 'contest_alias');

        if (is_null($r['resolution'])) {
            throw new InvalidParameterException('invalidParameters');
        }

        $contest = self::validateContestAdmin($r['contest_alias'], $r->identity->identity_id);

        $targetIdentity = IdentitiesDAO::FindByUsername($r['username']);

        $request = ProblemsetIdentityRequestDAO::getByPK($targetIdentity->identity_id, $contest->problemset_id);

        if (is_null($request)) {
            throw new InvalidParameterException('userNotInListOfRequests');
        }

        if (is_bool($r['resolution'])) {
            $resolution = $r['resolution'];
        } else {
            $resolution = $r['resolution'] === 'true';
        }

        $request->accepted = $resolution;
        $request->extra_note = $r['note'];
        $request->last_update = gmdate('Y-m-d H:i:s');

        ProblemsetIdentityRequestDAO::save($request);

        // Save this action in the history
        ProblemsetIdentityRequestHistoryDAO::save(new ProblemsetIdentityRequestHistory([
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
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     */
    public static function apiUsers(Request $r) {
        // Authenticate request
        self::authenticateRequest($r);

        Validators::validateStringNonEmpty($r['contest_alias'], 'contest_alias');

        $contest = self::validateContestAdmin($r['contest_alias'], $r->identity->identity_id);

        // Get identities from DB
        try {
            $identities = ProblemsetIdentitiesDAO::getWithExtraInformation($contest->problemset_id);
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }

        $response = [];
        $response['users'] = $identities;
        $response['status'] = 'ok';

        return $response;
    }

    /**
     * Returns all contest administrators
     *
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     */
    public static function apiAdmins(Request $r) {
        // Authenticate request
        self::authenticateRequest($r);

        Validators::validateStringNonEmpty($r['contest_alias'], 'contest_alias');

        $contest = self::validateContestAdmin($r['contest_alias'], $r->identity->identity_id);

        return [
            'status' => 'ok',
            'admins' => UserRolesDAO::getContestAdmins($contest),
            'group_admins' => GroupRolesDAO::getContestAdmins($contest)
        ];
    }

    /**
     * Enforces rules to avoid having invalid/unactionable public contests
     *
     * @param Contests $contest
     */
    private static function validateContestCanBePublic(Contests $contest) {
        $problemset = ProblemsetsDAO::getByPK($contest->problemset_id);
        // Check that contest has some problems at least 1 problem
        $problemsInProblemset = ProblemsetProblemsDAO::getRelevantProblems($problemset);
        if (count($problemsInProblemset) < 1) {
            throw new InvalidParameterException('contestPublicRequiresProblem');
        }
    }

    /**
     * Update a Contest
     *
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     */
    public static function apiUpdate(Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
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
            'start_time'        => ['transform' => function ($value) {
                return gmdate('Y-m-d H:i:s', $value);
            }],
            'finish_time'       => ['transform' => function ($value) {
                return gmdate('Y-m-d H:i:s', $value);
            }],
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
            DAO::transBegin();

            // Save the contest object with data sent by user to the database
            self::updateContest($contest, $originalContest, $r->user->user_id);

            if ($updateProblemset) {
                // Save the problemset object with data sent by user to the database
                $problemset = ProblemsetsDAO::getByPK($contest->problemset_id);
                $problemset->needs_basic_information = $r['basic_information'] ?? 0;
                $problemset->requests_user_information = $r['requests_user_information'] ?? 'no';
                ProblemsetsDAO::save($problemset);
            }

            // End transaction
            DAO::transEnd();
        } catch (Exception $e) {
            // Operation failed in the data layer, rollback transaction
            DAO::transRollback();

            throw new InvalidDatabaseOperationException($e);
        }

        // Expire contest-info cache
        Cache::deleteFromCache(Cache::CONTEST_INFO, $r['contest_alias']);

        // Expire contest scoreboard cache
        Scoreboard::invalidateScoreboardCache(ScoreboardParams::fromContest($contest));

        // Expire contest-list cache
        Cache::invalidateAllKeys(Cache::CONTESTS_LIST_PUBLIC);
        Cache::invalidateAllKeys(Cache::CONTESTS_LIST_SYSTEM_ADMIN);

        // Happy ending
        $response = [];
        $response['status'] = 'ok';

        self::$log->info('Contest updated (alias): ' . $r['contest_alias']);

        return $response;
    }

    /**
     * This function reviews changes in penalty type and admission mode
     */
    private static function updateContest(Contests $contest, Contests $original_contest, $user_id) {
        if ($original_contest->admission_mode !== $contest->admission_mode) {
            $timestamp = gmdate('Y-m-d H:i:s', Time::get());
            ContestLogDAO::save(new ContestLog([
                'contest_id' => $contest->contest_id,
                'user_id' => $user_id,
                'from_admission_mode' => $original_contest->admission_mode,
                'to_admission_mode' => $contest->admission_mode,
                'time' => $timestamp
            ]));
            $contest->last_updated = $timestamp;
        }
        ContestsDAO::save($contest);
        if ($original_contest->penalty_type == $contest->penalty_type) {
            return;
        }
        RunsDAO::recalculatePenaltyForContest($contest);
    }

    /**
     * Validates runs API
     *
     * @param Request $r
     * @return Array
     * @throws InvalidDatabaseOperationException
     * @throws NotFoundException
     * @throws ForbiddenAccessException
     */
    private static function validateRuns(Request $r) : Array {
        // Defaults for offset and rowcount
        if (!isset($r['offset'])) {
            $r['offset'] = 0;
        }
        if (!isset($r['rowcount'])) {
            $r['rowcount'] = 100;
        }

        Validators::validateStringNonEmpty($r['contest_alias'], 'contest_alias');

        $contest = self::validateContestAdmin($r['contest_alias'], $r->identity->identity_id);

        $r->ensureInt('offset', null, null, false);
        $r->ensureInt('rowcount', null, null, false);
        Validators::validateInEnum($r['status'], 'status', ['new', 'waiting', 'compiling', 'running', 'ready'], false);
        Validators::validateInEnum($r['verdict'], 'verdict', ['AC', 'PA', 'WA', 'TLE', 'MLE', 'OLE', 'RTE', 'RFE', 'CE', 'JE', 'NO-AC'], false);

        $problem = null;
        // Check filter by problem, is optional
        if (!is_null($r['problem_alias'])) {
            Validators::validateStringNonEmpty($r['problem_alias'], 'problem');

            try {
                $problem = ProblemsDAO::getByAlias($r['problem_alias']);
            } catch (Exception $e) {
                // Operation failed in the data layer
                throw new InvalidDatabaseOperationException($e);
            }

            if (is_null($problem)) {
                throw new NotFoundException('problemNotFound');
            }
        }

        Validators::validateInEnum($r['language'], 'language', array_keys(RunController::$kSupportedLanguages), false);

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
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     */
    public static function apiRuns(Request $r) {
        // Authenticate request
        self::authenticateRequest($r);

        // Validate request
        [$contest, $problem, $identity] = self::validateRuns($r);

        // Get our runs
        try {
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
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }

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
     * @param int $currentIdentityId
     * @return Contests
     * @throws InvalidDatabaseOperationException
     * @throws ForbiddenAccessException
     */
    private static function validateStats(string $contestAlias, int $currentIdentityId) : Contests {
        Validators::validateStringNonEmpty($contestAlias, 'contest_alias');

        return self::validateContestAdmin($contestAlias, $currentIdentityId);
    }

    /**
     * Stats of a problem
     *
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     * @throws ForbiddenAccessException
     */
    public static function apiStats(Request $r) {
        // Get user
        self::authenticateRequest($r);

        $contest = self::validateStats($r['contest_alias'], $r->identity->identity_id);

        try {
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
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
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
     * @param Request $r
     * @return array
     */
    public static function apiReport(Request $r) {
        self::authenticateRequest($r);

        $contest = self::validateStats($r['contest_alias'], $r->identity->identity_id);

        $params = ScoreboardParams::fromContest($contest);
        $params['admin'] = true;
        $params['auth_token'] = $r['auth_token'];
        $scoreboard = new Scoreboard($params);

        // Check the filter if we have one
        Validators::validateStringNonEmpty($r['filterBy'], 'filterBy', false /* not required */);

        $contestReport = $scoreboard->generate(
            true, // with run details for reporting
            true, // sort contestants by name,
            (isset($r['filterBy']) ? null : $r['filterBy'])
        );

        $contestReport['status'] = 'ok';
        return $contestReport;
    }

    /**
     * Generates a CSV for contest report
     *
     * @param Request $r
     * @return array
     */
    public static function apiCsvReport(Request $r) {
        self::authenticateRequest($r);

        $contest = self::validateStats($r['contest_alias'], $r->identity->identity_id);

        // Get full Report API of the contest
        $contestReport = self::apiReport(new Request([
            'contest_alias' => $r['contest_alias'],
            'auth_token' => $r['auth_token'],
        ]));

        // Get problem stats for each contest problem so we can
        // have the full list of cases
        $problemStats = [];
        $i = 0;
        foreach ($contestReport['problems'] as $entry) {
            $problem_alias = $entry['alias'];
            $problemStatsRequest = new Request([
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

    public static function apiDownload(Request $r) {
        self::authenticateRequest($r);

        $contest = self::validateStats($r['contest_alias'], $r->identity->identity_id);

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
     * @param Request $r
     * @return array
     */
    public static function apiRole(Request $r) {
        try {
            if ($r['contest_alias'] == 'all-events') {
                self::authenticateRequest($r);
                if (Authorization::isSystemAdmin($r->identity->identity_id)) {
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
     * @param Request $r
     * @return array
     */
    public static function apiSetRecommended(Request $r) {
        self::authenticateRequest($r);

        if (!Authorization::isSystemAdmin($r->identity->identity_id)) {
            throw new ForbiddenAccessException('userNotAllowed');
        }

        // Validate & get contest_alias
        try {
            $r['contest'] = ContestsDAO::getByAlias($r['contest_alias']);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        if (is_null($r['contest'])) {
            throw new NotFoundException('contestNotFound');
        }

        // Validate value param
        $r->ensureBool('value');

        $r['contest']->recommended = $r['value'];

        try {
            ContestsDAO::save($r['contest']);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        return ['status' => 'ok'];
    }

    /**
     * Return users who participate in a contest, as long as contest admin
     * has chosen to ask for users information and contestants have
     * previously agreed to share their information.
     *
     * @param Request $r
     * @return array
     * @throws ForbiddenAccessException
     * @throws InvalidDatabaseOperationException
     */
    public static function apiContestants(Request $r) {
        self::authenticateRequest($r);

        $contest = self::validateStats($r['contest_alias'], $r->identity->identity_id);

        if (!ContestsDAO::requestsUserInformation($contest->contest_id)) {
            throw new ForbiddenAccessException('contestInformationNotRequired');
        }

        // Get contestants info
        try {
            $contestants = ContestsDAO::getContestantsInfo($contest->contest_id);
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }

        return [
            'status' => 'ok',
            'contestants' => $contestants,
        ];
    }

    public static function isPublic($admission_mode) {
        return $admission_mode != 'private';
    }
}
