<?php

require_once('libs/dao/Contests.dao.php');
require_once('libs/ActivityReport.php');

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
            Validators::isNumber($r['page'], 'page', false);
            Validators::isNumber($r['page_size'], 'page_size', false);

            $page = (isset($r['page']) ? intval($r['page']) : 1);
            $page_size = (isset($r['page_size']) ? intval($r['page_size']) : 20);
            $active_contests = isset($r['active'])
                ? ActiveStatus::getIntValue($r['active'])
                : ActiveStatus::ALL;
            // If the parameter was not set, the default should be ALL which is
            // a number and should pass this check.
            Validators::isNumber($active_contests, 'active', true /* required */);
            $recommended = isset($r['recommended'])
                ? RecommendedStatus::getIntValue($r['recommended'])
                : RecommendedStatus::ALL;
            // Same as above.
            Validators::isNumber($recommended, 'recommended', true /* required */);
            $participating = isset($r['participating'])
                ? ParticipatingStatus::getIntValue($r['participating'])
                : ParticipatingStatus::NO;
            Validators::isInEnum($r['admission_mode'], 'admission_mode', [
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
            Validators::isStringOfMaxLength($query, 'query', 255, false /* not required */);
            $cache_key = "$active_contests-$recommended-$page-$page_size";
            if ($r['current_user_id'] === null) {
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
                $contests = ContestsDAO::getContestsParticipating($r['current_identity_id'], $page, $page_size, $query);
            } elseif ($public) {
                $contests = ContestsDAO::getRecentPublicContests($r['current_identity_id'], $page, $page_size, $query);
            } elseif (Authorization::isSystemAdmin($r['current_identity_id'])) {
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
                $contests = ContestsDAO::getAllContestsForIdentity($r['current_identity_id'], $page, $page_size, $active_contests, $recommended, $query);
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

        Validators::isNumber($r['page'], 'page', false);
        Validators::isNumber($r['page_size'], 'page_size', false);

        $page = (isset($r['page']) ? intval($r['page']) : 1);
        $pageSize = (isset($r['page_size']) ? intval($r['page_size']) : 1000);

        // Create array of relevant columns
        $relevant_columns = ['title', 'alias', 'start_time', 'finish_time', 'admission_mode', 'scoreboard_url', 'scoreboard_url_admin'];
        $contests = null;
        try {
            if (Authorization::isSystemAdmin($r['current_identity_id'])) {
                $contests = ContestsDAO::getAll(
                    $page,
                    $pageSize,
                    'contest_id',
                    'DESC'
                );
            } else {
                $contests = ContestsDAO::getAllContestsAdminedByIdentity(
                    $r['current_identity_id'],
                    $page,
                    $pageSize
                );
            }
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        $addedContests = [];
        foreach ($contests as $c) {
            $c->toUnixTime();
            $contestInfo = $c->asFilteredArray($relevant_columns);
            $addedContests[] = $contestInfo;
        }

        return [
            'status' => 'ok',
            'contests' => $addedContests,
        ];
    }

    /**
     * Callback to get contests list, depending on a given method
     * @param Request $r
     * @param $callback_user_function
     * @return array
     * @throws InvalidDatabaseOperationException
     */
    public static function getContestListInternal(Request $r, $callback_user_function) {
        self::authenticateRequest($r);

        Validators::isNumber($r['page'], 'page', false);
        Validators::isNumber($r['page_size'], 'page_size', false);

        $page = (isset($r['page']) ? intval($r['page']) : 1);
        $pageSize = (isset($r['page_size']) ? intval($r['page_size']) : 1000);
        $query = $r['query'];
        // Create array of relevant columns
        $relevant_columns = [
            'title',
            'problemset_id',
            'alias',
            'start_time',
            'finish_time',
            'problemset_id',
            'admission_mode',
            'scoreboard_url',
            'scoreboard_url_admin',
            'rerun_id'
        ];
        $contests = null;
        $identity_id = $callback_user_function == 'ContestsDAO::getContestsParticipating'
          ? $r['current_identity_id'] : $r['current_user_id'];
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
     * Expects $r["contest"] to contain the contest to check against.
     *
     * In case of access check failed, an exception is thrown.
     *
     * @param Request $r
     * @throws ApiException
     * @throws InvalidDatabaseOperationException
     * @throws ForbiddenAccessException
     */
    public static function canAccessContest(Request $r) {
        if (!isset($r['contest']) || is_null($r['contest'])) {
            throw new NotFoundException('contestNotFound');
        }

        if (!($r['contest'] instanceof Contests)) {
            throw new InvalidParameterException('contest must be an instance of ContestVO');
        }

        if ($r['contest']->admission_mode == 'private') {
            try {
                if (is_null(ProblemsetIdentitiesDAO::getByPK($r['current_identity_id'], $r['contest']->problemset_id))
                        && !Authorization::isContestAdmin($r['current_identity_id'], $r['contest'])) {
                    throw new ForbiddenAccessException('userNotAllowed');
                }
            } catch (ApiException $e) {
                // Propagate exception
                throw $e;
            } catch (Exception $e) {
                // Operation failed in the data layer
                throw new InvalidDatabaseOperationException($e);
            }
        } elseif ($r['contest']->admission_mode == 'registration' &&
            !Authorization::isContestAdmin($r['current_identity_id'], $r['contest'])
        ) {
            $req = ProblemsetIdentityRequestDAO::getByPK(
                $r['current_identity_id'],
                $r['contest']->problemset_id
            );
            if (is_null($req) || ($req->accepted === '0')) {
                throw new ForbiddenAccessException('contestNotRegistered');
            }
        }
    }

    /**
     * Validate the basics of a contest request.
     *
     */
    private static function validateBasicDetails(Request $r) {
        Validators::isStringNonEmpty($r['contest_alias'], 'contest_alias');
        // If the contest is private, verify that our user is invited
        try {
            $contest_problemset = ContestsDAO::getByAliasWithExtraInformation($r['contest_alias']);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }
        if (is_null($contest_problemset)) {
            throw new NotFoundException('contestNotFound');
        }
        $r['contest'] = new Contests($contest_problemset);
        $r['problemset'] = new Problemsets($contest_problemset);
    }

    /**
     * Validate if a contestant has explicit access to a contest.
     *
     * @param Request $r
     */
    public static function isInvitedToContest(Request $r) {
        if (is_null($r['contest']) || is_null($r['current_user_id'])) {
            return false;
        }
        return self::isPublic($r['contest']->admission_mode) ||
            !is_null(ProblemsetIdentitiesDAO::getByPK(
                $r['current_identity_id'],
                $r['contest']->problemset_id
            ));
    }

    /**
     * Show the contest intro unless you are admin, or you
     * already started this contest.
     */
    public static function showContestIntro(Request $r) {
        try {
            $r['contest'] = ContestsDAO::getByAlias($r['contest_alias']);
        } catch (Exception $e) {
            throw new NotFoundException('contestNotFound');
        }
        if (is_null($r['contest'])) {
            throw new NotFoundException('contestNotFound');
        }
        $result = ContestsDAO::getNeedsInformation($r['contest']->problemset_id);

        try {
            // Half-authenticate, in case there is no session in place.
            $session = SessionController::apiCurrentSession($r)['session'];
            if ($session['valid'] && !is_null($session['identity'])) {
                $r['current_identity'] = $session['identity'];
                $r['current_identity_id'] = $session['identity']->identity_id;

                if (!is_null($session['user'])) {
                    $r['current_user'] = $session['user'];
                    $r['current_user_id'] = $session['user']->user_id;
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
                    self::isPublic($r['contest']->admission_mode) ? ContestController::SHOW_INTRO : !ContestController::SHOW_INTRO;
                return $result;
            }
            self::canAccessContest($r);
        } catch (Exception $e) {
            // Could not access contest. Private contests must not be leaked, so
            // unless they were manually added beforehand, show them a 404 error.
            if (!ContestController::isInvitedToContest($r)) {
                throw $e;
            }
            self::$log->error('Exception while trying to verify access: ' . $e);
            $result['shouldShowIntro'] = ContestController::SHOW_INTRO;
            return $result;
        }

        // You already started the contest.
        $contestOpened = ProblemsetIdentitiesDAO::getByPK(
            $r['current_identity_id'],
            $r['contest']->problemset_id
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
     * @throws InvalidDatabaseOperationException
     * @throws NotFoundException
     * @throws Exception
     * @throws ForbiddenAccessException
     * @throws PreconditionFailedException
     */
    public static function validateDetails(Request $r) {
        self::validateBasicDetails($r);

        $r['contest_admin'] = false;

        // If the contest has not started, user should not see it, unless it is admin or has a token.
        if (is_null($r['token'])) {
            // Crack the request to get the current user
            self::authenticateRequest($r);
            self::canAccessContest($r);

            $r['contest_admin'] = Authorization::isContestAdmin($r['current_identity_id'], $r['contest']);
            if (!ContestsDAO::hasStarted($r['contest']) && !$r['contest_admin']) {
                $exception = new PreconditionFailedException('contestNotStarted');
                $exception->addCustomMessageToArray('start_time', strtotime($r['contest']->start_time));

                throw $exception;
            }
        } else {
            if ($r['token'] === $r['problemset']->scoreboard_url_admin) {
                $r['contest_admin'] = true;
                $r['contest_alias'] = $r['contest']->alias;
            } elseif ($r['token'] !== $r['problemset']->scoreboard_url) {
                throw new ForbiddenAccessException('invalidScoreboardUrl');
            }
        }
    }

     /**
     * Temporal hotfix wrapper
     */
    public static function apiIntroDetails(Request $r) {
        return self::apiPublicDetails($r);
    }

    public static function apiPublicDetails(Request $r) {
        Validators::isStringNonEmpty($r['contest_alias'], 'contest_alias');

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

        // Create array of relevant columns
        $relevant_columns = [
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
        ];

        // Initialize response to be the contest information
        $result = $r['contest']->asFilteredArray($relevant_columns);

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

        self::validateBasicDetails($r);

        try {
            ProblemsetIdentityRequestDAO::save(new ProblemsetIdentityRequest([
                'identity_id' => $r['current_identity_id'],
                'problemset_id' => $r['contest']->problemset_id,
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
        self::validateDetails($r);
        $needsInformation = ContestsDAO::getNeedsInformation($r['contest']->problemset_id);
        $session = SessionController::apiCurrentSession($r)['session'];

        if ($needsInformation['needs_basic_information'] && !is_null($session['identity']) &&
              (!$session['identity']->country_id || !$session['identity']->state_id || !$session['identity']->school_id)
        ) {
            throw new ForbiddenAccessException('contestBasicInformationNeeded');
        }

        CoursesDAO::transBegin();
        try {
            ProblemsetIdentitiesDAO::CheckAndSaveFirstTimeAccess(
                $r['current_identity_id'],
                $r['contest']->problemset_id,
                true,
                $r['share_user_information']
            );

            // Insert into PrivacyStatement_Consent_Log whether request
            // user info is optional or required
            if ($needsInformation['requests_user_information'] != 'no') {
                $privacystatement_id = PrivacyStatementsDAO::getId($r['privacy_git_object_id '], $r['statement_type']);
                $privacystatement_consent_id = PrivacyStatementConsentLogDAO::saveLog(
                    $r['current_identity_id'],
                    $privacystatement_id
                );

                ProblemsetIdentitiesDAO::updatePrivacyStatementConsent(new ProblemsetIdentities([
                    'identity_id' => $r['current_identity_id'],
                    'problemset_id' => $r['contest']->problemset_id,
                    'privacystatement_consent_id' => $privacystatement_consent_id
                ]));
            }

            CoursesDAO::transEnd();
        } catch (Exception $e) {
            CoursesDAO::transRollback();
            throw new InvalidDatabaseOperationException($e);
        }

        self::$log->info("User '{$r['current_identity']->username}' joined contest '{$r['contest']->alias}'");
        return ['status' => 'ok'];
    }

    /**
     * Returns details of a Contest. This is shared between apiDetails and
     * apiAdminDetails.
     *
     * @param Request $r
     * @param $result
     */
    private static function getCachedDetails(Request $r, &$result) {
        Cache::getFromCacheOrSet(Cache::CONTEST_INFO, $r['contest_alias'], $r, function (Request $r) {
            // Create array of relevant columns
            $relevant_columns = [
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
                'rerun_id'];

            // Initialize response to be the contest information
            $result = $r['contest']->asFilteredArray($relevant_columns);

            $result['start_time'] = strtotime($result['start_time']);
            $result['finish_time'] = strtotime($result['finish_time']);
            $result['original_contest_alias'] = null;
            $result['original_problemset_id'] = null;
            if ($result['rerun_id'] != 0) {
                $original_contest = ContestsDAO::getByPK($result['rerun_id']);
                $result['original_contest_alias'] = $original_contest->alias;
                $result['original_problemset_id'] = $original_contest->problemset_id;
            }

            try {
                $acl = ACLsDAO::getByPK($r['contest']->acl_id);
                $result['director'] = UsersDAO::getByPK($acl->owner_id)->username;
            } catch (Exception $e) {
                // Operation failed in the data layer
                throw new InvalidDatabaseOperationException($e);
            }

            try {
                $problemsInContest = ProblemsetProblemsDAO::getProblemsByProblemset($r['contest']->problemset_id);
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
                ContestsDAO::getNeedsInformation($r['contest']->problemset_id)
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
        self::validateDetails($r);

        $result = [];
        self::getCachedDetails($r, $result);
        unset($result['scoreboard_url']);
        unset($result['scoreboard_url_admin']);
        unset($result['rerun_id']);
        if (is_null($r['token'])) {
            // Adding timer info separately as it depends on the current user and we don't
            // want this to get generally cached for everybody
            // Save the time of the first access
            try {
                $problemset_user = ProblemsetIdentitiesDAO::CheckAndSaveFirstTimeAccess(
                    $r['current_identity_id'],
                    $r['contest']->problemset_id
                );
            } catch (ApiException $e) {
                throw $e;
            } catch (Exception $e) {
                // Operation failed in the data layer
                throw new InvalidDatabaseOperationException($e);
            }

            // Add time left to response
            if ($r['contest']->window_length === null) {
                $result['submission_deadline'] = strtotime($r['contest']->finish_time);
            } else {
                $result['submission_deadline'] = min(
                    strtotime($r['contest']->finish_time),
                    strtotime($problemset_user->access_time) + $r['contest']->window_length * 60
                );
            }
            $result['admin'] = Authorization::isContestAdmin($r['current_identity_id'], $r['contest']);

            // Log the operation.
            ProblemsetAccessLogDAO::save(new ProblemsetAccessLog([
                'identity_id' => $r['current_identity_id'],
                'problemset_id' => $r['contest']->problemset_id,
                'ip' => ip2long($_SERVER['REMOTE_ADDR']),
            ]));
        } else {
            $result['admin'] = $r['contest_admin'];
        }

        $result['status'] = 'ok';
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
        self::validateDetails($r);

        if (!Authorization::isContestAdmin($r['current_identity_id'], $r['contest'])) {
            throw new ForbiddenAccessException();
        }

        $result = [];
        self::getCachedDetails($r, $result);

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
        self::validateDetails($r);

        if (!$r['contest_admin']) {
            throw new ForbiddenAccessException();
        }

        $accesses = ProblemsetAccessLogDAO::GetAccessForProblemset($r['contest']->problemset_id);
        $submissions = SubmissionLogDAO::GetSubmissionsForProblemset($r['contest']->problemset_id);

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

        try {
            $original_contest = ContestsDAO::getByAlias($r['contest_alias']);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        if (is_null($original_contest)) {
            throw new NotFoundException('contestNotFound');
        }

        if (!Authorization::isContestAdmin($r['current_identity_id'], $original_contest)) {
            throw new ForbiddenAccessException();
        }

        $length = strtotime($original_contest->finish_time) - strtotime($original_contest->start_time);
        $auth_token = isset($r['auth_token']) ? $r['auth_token'] : null;

        ContestsDAO::transBegin();
        $response = [];
        try {
            // Create the contest
            $response[] = self::apiCreate(new Request([
                'title' => $r['title'],
                'description' => $r['description'],
                'alias' => $r['alias'],
                'start_time' => $r['start_time'],
                'finish_time' => $r['start_time'] + $length,
                'scoreboard' => $original_contest->scoreboard,
                'points_decay_factor' => $original_contest->points_decay_factor,
                'submissions_gap' => $original_contest->submissions_gap,
                'feedback' => $original_contest->feedback,
                'penalty_type' => $original_contest->penalty_type,
                'admission_mode' => 'private', // All cloned contests start in private admission_mode
                'auth_token' => $auth_token
            ]));
            $problems = self::apiProblems($r);
            foreach ($problems['problems'] as $problem) {
                $response[] = self::apiAddProblem(new Request([
                        'contest_alias' => $r['alias'],
                        'problem_alias' => $problem['alias'],
                        'points' => $problem['points'],
                        'auth_token' => $auth_token
                    ]));
            }
            ContestsDAO::transEnd();
        } catch (InvalidParameterException $e) {
            ContestsDAO::transRollback();
            throw $e;
        } catch (DuplicatedEntryInDatabaseException $e) {
            ContestsDAO::transRollback();
            throw $e;
        } catch (Exception $e) {
            ContestsDAO::transRollback();
            throw new InvalidDatabaseOperationException($e);
        }

        return ['status' => 'ok', 'alias' => $r['alias']];
    }

    public static function apiCreateVirtual(Request $r) {
        global $experiments;
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        $experiments->ensureEnabled(Experiments::VIRTUAL);
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

        Validators::isNumber($r['start_time'], 'start_time', false);
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
            'scoreboard_url' => SecurityTools::randomString(30),
            'scoreboard_url_admin' => SecurityTools::randomString(30),
        ]);

        self::createContest($r, $problemset, $contest, $originalContest->problemset_id);

        return ['status' => 'ok', 'alias' => $contest->alias];
    }

    private static function createContest(Request $r, Problemsets $problemset, Contests $contest, $originalProblemset = null) {
        $acl = new ACLs();
        $acl->owner_id = $r['current_user_id'];
        // Push changes
        try {
            // Begin a new transaction
            ContestsDAO::transBegin();

            ACLsDAO::save($acl);
            $problemset->acl_id = $acl->acl_id;
            $problemset->type = 'Contest';
            $contest->acl_id = $acl->acl_id;

            // Save the problemset object with data sent by user to the database
            ProblemsetsDAO::save($problemset);

            $contest->problemset_id = $problemset->problemset_id;
            if (!is_null($originalProblemset)) {
                ProblemsetProblemsDAO::copyProblemset($contest->problemset_id, $originalProblemset);
            }

            // Save the contest object with data sent by user to the database
            ContestsDAO::save($contest);

            // Update contest_id in problemset object
            $problemset->contest_id = $contest->contest_id;
            ProblemsetsDAO::save($problemset);

            // End transaction transaction
            ContestsDAO::transEnd();
        } catch (Exception $e) {
            // Operation failed in the data layer, rollback transaction
            ContestsDAO::transRollback();

            // Alias may be duplicated, 1062 error indicates that
            if (strpos($e->getMessage(), '1062') !== false) {
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
        self::validateCreateOrUpdate($r);

        // Create and populate a new Contests object
        $contest = new Contests();
        // Set private contest by default if is not sent in request
        $contest->admission_mode = is_null($r['admission_mode']) ? 'private' : $r['admission_mode'];
        $contest->title = $r['title'];
        $contest->description = $r['description'];
        $contest->start_time = gmdate('Y-m-d H:i:s', $r['start_time']);
        $contest->finish_time = gmdate('Y-m-d H:i:s', $r['finish_time']);
        $contest->window_length = $r['window_length'] === 'NULL' ? null : $r['window_length'];
        $contest->rerun_id = 0;
        $contest->alias = $r['alias'];
        $contest->scoreboard = $r['scoreboard'];
        $contest->points_decay_factor = $r['points_decay_factor'];
        $contest->partial_score = is_null($r['partial_score']) ? '1' : $r['partial_score'];
        $contest->submissions_gap = $r['submissions_gap'];
        $contest->feedback = $r['feedback'];
        $contest->penalty = max(0, intval($r['penalty']));
        $contest->penalty_type = $r['penalty_type'];
        $contest->penalty_calc_policy = is_null($r['penalty_calc_policy']) ? 'sum' : $r['penalty_calc_policy'];
        $contest->languages = empty($r['languages']) ? null :  join(',', $r['languages']);

        if (!is_null($r['show_scoreboard_after'])) {
            $contest->show_scoreboard_after = $r['show_scoreboard_after'];
        } else {
            $contest->show_scoreboard_after = '1';
        }

        if ($contest->admission_mode != 'private') {
            throw new InvalidParameterException('contestMustBeCreatedInPrivateMode');
        }

        $problemset = new Problemsets([
            'needs_basic_information' => $r['needs_basic_information'] == 'true',
            'requests_user_information' => $r['requests_user_information'],
            'type' => 'Contest',
            'scoreboard_url' => SecurityTools::randomString(30),
            'scoreboard_url_admin' => SecurityTools::randomString(30),
        ]);

        self::createContest($r, $problemset, $contest);

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
    private static function validateCreateOrUpdate(Request $r, $is_update = false) {
        // Is the parameter required?
        $is_required = true;

        if ($is_update === true) {
            // In case of Update API, required parameters for Create API are not required
            $is_required = false;

            try {
                $r['contest'] = ContestsDAO::getByAlias($r['contest_alias']);
            } catch (Exception $e) {
                throw new InvalidDatabaseOperationException($e);
            }

            if (is_null($r['contest'])) {
                throw new NotFoundException('contestNotFound');
            }

            if (!Authorization::isContestAdmin($r['current_identity_id'], $r['contest'])) {
                throw new ForbiddenAccessException();
            }
        }

        Validators::isStringNonEmpty($r['title'], 'title', $is_required);
        Validators::isStringNonEmpty($r['description'], 'description', $is_required);

        Validators::isNumber($r['start_time'], 'start_time', $is_required);
        Validators::isNumber($r['finish_time'], 'finish_time', $is_required);

        // Get the actual start and finish time of the contest, considering that
        // in case of update, parameters can be optional
        $start_time = !is_null($r['start_time']) ? $r['start_time'] : strtotime($r['contest']->start_time);
        $finish_time = !is_null($r['finish_time']) ? $r['finish_time'] : strtotime($r['contest']->finish_time);

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
        if (!is_null($r['window_length']) && $r['window_length'] !== 'NULL') {
            Validators::isNumberInRange(
                $r['window_length'],
                'window_length',
                0,
                floor($contest_length) / 60,
                false
            );
        }

        Validators::isInEnum($r['admission_mode'], 'admission_mode', [
            'public',
            'private',
            'registration'
        ], false);
        Validators::isValidAlias($r['alias'], 'alias', $is_required);
        Validators::isNumberInRange($r['scoreboard'], 'scoreboard', 0, 100, $is_required);
        Validators::isNumberInRange($r['points_decay_factor'], 'points_decay_factor', 0, 1, $is_required);
        Validators::isInEnum($r['partial_score'], 'partial_score', ['0', '1'], false);
        Validators::isNumberInRange($r['submissions_gap'], 'submissions_gap', 0, $contest_length, $is_required);

        Validators::isInEnum($r['feedback'], 'feedback', ['no', 'yes', 'partial'], $is_required);
        Validators::isInEnum($r['penalty_type'], 'penalty_type', ['contest_start', 'problem_open', 'runtime', 'none'], $is_required);
        Validators::isInEnum($r['penalty_calc_policy'], 'penalty_calc_policy', ['sum', 'max'], false);

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
                ProblemsetController::validateAddProblemToProblemset(null, $p, $r['current_identity_id']);
                array_push($problems, [
                    'id' => $p->problem_id,
                    'alias' => $problem->problem,
                    'points' => $problem->points
                ]);
            }

            $r['problems'] = $problems;
        }

        // Show scoreboard is always optional
        Validators::isInEnum($r['show_scoreboard_after'], 'show_scoreboard_after', ['0', '1'], false);

        // languages is always optional
        if (!empty($r['languages'])) {
            foreach ($r['languages'] as $language) {
                Validators::isInEnum($language, 'languages', array_keys(RunController::$kSupportedLanguages), false);
            }
        }

        if ($is_update) {
            // Prevent date changes if a contest already has runs
            if (!is_null($r['start_time']) && $r['start_time'] != strtotime($r['contest']->start_time)) {
                $runCount = 0;

                try {
                    $runCount = RunsDAO::CountTotalRunsOfProblemset($r['contest']->problemset_id);
                } catch (Exception $e) {
                    throw new InvalidDatabaseOperationException($e);
                }

                if ($runCount > 0) {
                    throw new InvalidParameterException('contestUpdateAlreadyHasRuns');
                }
            }
        }
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

        Validators::isStringNonEmpty($r['contest_alias'], 'contest_alias');

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

        // Only contest admin is allowed to view details through this API
        if (!Authorization::isContestAdmin($r['current_identity_id'], $contest)) {
            throw new ForbiddenAccessException('cannotAddProb');
        }

        $problemset = ProblemsetsDAO::getByPK($contest->problemset_id);

        try {
            $problems = ProblemsetProblemsDAO::getProblemsetProblems($problemset);
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
        $params = self::validateAddToContestRequest($r);

        self::forbiddenInVirtual($params['contest']);

        $problemset = ProblemsetsDAO::getByPK($params['contest']->problemset_id);

        if (ProblemsetProblemsDAO::countProblemsetProblems($problemset)
                >= MAX_PROBLEMS_IN_CONTEST) {
            throw new PreconditionFailedException('contestAddproblemTooManyProblems');
        }

        ProblemsetController::addProblem(
            $params['contest']->problemset_id,
            $params['problem'],
            $r['current_identity_id'],
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
     * @throws InvalidDatabaseOperationException
     * @throws InvalidParameterException
     * @throws ForbiddenAccessException
     */
    private static function validateAddToContestRequest(Request $r) {
        Validators::isStringNonEmpty($r['contest_alias'], 'contest_alias');

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
        if (!Authorization::isContestAdmin($r['current_identity_id'], $contest)) {
            throw new ForbiddenAccessException('cannotAddProb');
        }

        Validators::isStringNonEmpty($r['problem_alias'], 'problem_alias');

        try {
            $problem = ProblemsDAO::getByAlias($r['problem_alias']);
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
        if (!ProblemsDAO::isVisible($problem) && !Authorization::isProblemAdmin($r['current_identity_id'], $problem)) {
            throw new ForbiddenAccessException('problemIsPrivate');
        }

        Validators::isNumberInRange($r['points'], 'points', 0, INF);
        Validators::isNumberInRange($r['order_in_contest'], 'order_in_contest', 0, INF, false);

        return [
            'contest' => $contest,
            'problem' => $problem];
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
        $params = self::validateRemoveFromContestRequest($r);

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
     * @throws InvalidDatabaseOperationException
     * @throws InvalidParameterException
     * @throws ForbiddenAccessException
     */
    private static function validateRemoveFromContestRequest(Request $r) {
        Validators::isStringNonEmpty($r['contest_alias'], 'contest_alias');

        try {
            $contest = ContestsDAO::getByAlias($r['contest_alias']);
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }

        if (is_null($contest)) {
            throw new InvalidParameterException('parameterNotFound', 'problem_alias');
        }

        // Only contest admin is allowed to remove problems in contest
        if (!Authorization::isContestAdmin($r['current_identity_id'], $contest)) {
            throw new ForbiddenAccessException('cannotRemoveProblem');
        }

        Validators::isStringNonEmpty($r['problem_alias'], 'problem_alias');

        try {
            $problem = ProblemsDAO::getByAlias($r['problem_alias']);
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }

        if (is_null($problem)) {
            throw new InvalidParameterException('parameterNotFound', 'problem_alias');
        }

        // Disallow removing problem from contest if it already has runs within the contest
        if (RunsDAO::CountTotalRunsOfProblemInProblemset($problem->problem_id, $contest->problemset_id) > 0 &&
            !Authorization::isSystemAdmin($r['current_identity_id'])) {
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
            'problem' => $problem];
    }

    /**
     * Validates add/remove user request
     *
     * @param Request $r
     * @throws InvalidDatabaseOperationException
     * @throws InvalidParameterException
     * @throws ForbiddenAccessException
     */
    private static function validateAddUser(Request $r) {
        $r['user'] = null;

        // Check contest_alias
        Validators::isStringNonEmpty($r['contest_alias'], 'contest_alias');

        $r['user'] = UserController::resolveUser($r['usernameOrEmail']);

        if (is_null($r['user'])) {
            throw new NotFoundException('userOrMailNotFound');
        }

        try {
            $r['contest'] = ContestsDAO::getByAlias($r['contest_alias']);
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }

        if (is_null($r['contest'])) {
            throw new NotFoundException('contestNotFound');
        }

        // Only director is allowed to create problems in contest
        if (!Authorization::isContestAdmin($r['current_identity_id'], $r['contest'])) {
            throw new ForbiddenAccessException();
        }
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
        self::validateAddUser($r);

        // Save the contest to the DB
        try {
            ProblemsetIdentitiesDAO::save(new ProblemsetIdentities([
                'problemset_id' => $r['contest']->problemset_id,
                'identity_id' => $r['user']->main_identity_id,
                'access_time' => null,
                'score' => '0',
                'time' => '0',
            ]));
        } catch (Exception $e) {
            // Operation failed in the data layer
            self::$log->error('Failed to create new ContestUser: ' . $e->getMessage());
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
        self::validateAddUser($r);

        try {
            ProblemsetIdentitiesDAO::delete(new ProblemsetIdentities([
                'problemset_id' => $r['contest']->problemset_id,
                'identity_id' => $r['user']->main_identity_id,
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
        Validators::isStringNonEmpty($r['contest_alias'], 'contest_alias');

        $user = UserController::resolveUser($r['usernameOrEmail']);

        try {
            $contest = ContestsDAO::getByAlias($r['contest_alias']);
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }

        // Only director is allowed to create problems in contest
        if (!Authorization::isContestAdmin($r['current_identity_id'], $contest)) {
            throw new ForbiddenAccessException();
        }

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
        Validators::isStringNonEmpty($r['contest_alias'], 'contest_alias');

        $user = UserController::resolveUser($r['usernameOrEmail']);

        try {
            $contest = ContestsDAO::getByAlias($r['contest_alias']);
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }

        // Only admin is alowed to make modifications
        if (!Authorization::isContestAdmin($r['current_identity_id'], $contest)) {
            throw new ForbiddenAccessException();
        }

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
        Validators::isStringNonEmpty($r['contest_alias'], 'contest_alias');

        $group = GroupsDAO::FindByAlias($r['group']);

        if ($group == null) {
            throw new InvalidParameterException('invalidParameters');
        }

        try {
            $contest = ContestsDAO::getByAlias($r['contest_alias']);
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }

        // Only admins are allowed to modify contest
        if (!Authorization::isContestAdmin($r['current_identity_id'], $contest)) {
            throw new ForbiddenAccessException();
        }

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
        Validators::isStringNonEmpty($r['contest_alias'], 'contest_alias');

        $group = GroupsDAO::FindByAlias($r['group']);

        if ($group == null) {
            throw new InvalidParameterException('invalidParameters');
        }

        try {
            $contest = ContestsDAO::getByAlias($r['contest_alias']);
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }

        // Only admin is alowed to make modifications
        if (!Authorization::isContestAdmin($r['current_identity_id'], $contest)) {
            throw new ForbiddenAccessException();
        }

        ACLController::removeGroup($contest->acl_id, $group->group_id);

        return ['status' => 'ok'];
    }

    /**
     * Validate the Clarifications request
     *
     * @param Request $r
     * @throws InvalidDatabaseOperationException
     */
    private static function validateClarifications(Request $r) {
        // Check contest_alias
        Validators::isStringNonEmpty($r['contest_alias'], 'contest_alias');

        try {
            $r['contest'] = ContestsDAO::getByAlias($r['contest_alias']);
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }

        if ($r['contest'] == null) {
            throw new NotFoundException('contestNotFound');
        }

        Validators::isNumber($r['offset'], 'offset', false /* optional */);
        Validators::isNumber($r['rowcount'], 'rowcount', false /* optional */);
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
        self::validateClarifications($r);

        $is_contest_director = Authorization::isContestAdmin(
            $r['current_identity_id'],
            $r['contest']
        );

        try {
            $clarifications = ClarificationsDAO::GetProblemsetClarifications(
                $r['contest']->problemset_id,
                $is_contest_director,
                $r['current_identity_id'],
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
        self::validateDetails($r);

        $params = ScoreboardParams::fromContest($r['contest']);
        $params['admin'] = (
            Authorization::isContestAdmin($r['current_identity_id'], $r['contest']) &&
            !ContestsDAO::isVirtual($r['contest'])
        );
        $params['show_all_runs'] = !ContestsDAO::isVirtual($r['contest']);
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
        self::validateBasicDetails($r);

        // If true, will override Scoreboard Pertentage to 100%
        $showAllRuns = false;

        if (is_null($r['token'])) {
            // Get the current user
            self::authenticateRequest($r);

            self::canAccessContest($r);

            if (Authorization::isContestAdmin($r['current_identity_id'], $r['contest'])) {
                $showAllRuns = true;
            }
        } else {
            if ($r['token'] === $r['problemset']->scoreboard_url) {
                $showAllRuns = false;
            } elseif ($r['token'] === $r['problemset']->scoreboard_url_admin) {
                $showAllRuns = true;
            } else {
                throw new ForbiddenAccessException('invalidScoreboardUrl');
            }
        }

        // Create scoreboard
        $params = ScoreboardParams::fromContest($r['contest']);
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

        Validators::isStringNonEmpty($r['contest_aliases'], 'contest_aliases');
        $contest_aliases = explode(',', $r['contest_aliases']);

        Validators::isStringNonEmpty($r['usernames_filter'], 'usernames_filter', false);

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

        Validators::isStringNonEmpty($r['contest_alias'], 'contest_alias');

        try {
            $contest = ContestsDAO::getByAlias($r['contest_alias']);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        if (is_null($contest)) {
            throw new NotFoundException('contestNotFound');
        }

        if (!Authorization::isContestAdmin($r['current_identity_id'], $contest)) {
            throw new ForbiddenAccessException();
        }

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

        Validators::isStringNonEmpty($r['contest_alias'], 'contest_alias');

        if (is_null($r['resolution'])) {
            throw new InvalidParameterException('invalidParameters');
        }

        try {
            $contest = ContestsDAO::getByAlias($r['contest_alias']);
        } catch (Exception $e) {
            throw new NotFoundException($e);
        }

        if (is_null($contest)) {
            throw new NotFoundException('contestNotFound');
        }

        if (!Authorization::isContestAdmin($r['current_identity_id'], $contest)) {
            throw new ForbiddenAccessException();
        }

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
            'admin_id' => $r['current_user_id'],
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

        Validators::isStringNonEmpty($r['contest_alias'], 'contest_alias');

        try {
            $contest = ContestsDAO::getByAlias($r['contest_alias']);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        if (is_null($contest)) {
            throw new NotFoundException('contestNotFound');
        }

        if (!Authorization::isContestAdmin($r['current_identity_id'], $contest)) {
            throw new ForbiddenAccessException();
        }

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

        Validators::isStringNonEmpty($r['contest_alias'], 'contest_alias');

        try {
            $contest = ContestsDAO::getByAlias($r['contest_alias']);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        if (!Authorization::isContestAdmin($r['current_identity_id'], $contest)) {
            throw new ForbiddenAccessException();
        }

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
        self::validateCreateOrUpdate($r, true /* is update */);

        self::forbiddenInVirtual($r['contest']);

        // Update contest DAO
        if (!is_null($r['admission_mode'])) {
            // If going public
            if (self::isPublic($r['admission_mode'])) {
                self::validateContestCanBePublic($r['contest']);
            }

            $r['contest']->admission_mode = $r['admission_mode'];
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
                return $value == 'NULL' ? null : $value;
            }],
            'scoreboard',
            'points_decay_factor',
            'partial_score',
            'submissions_gap',
            'feedback',
            'penalty'               => ['transform' => function ($value) {
                return max(0, intval($value));
            }],
            'penalty_type',
            'penalty_calc_policy',
            'show_scoreboard_after',
            'languages' => ['transform' => function ($value) {
                if (!is_array($value)) {
                    return $value;
                }
                return join(',', $value);
            }],
            'admission_mode',
        ];
        self::updateValueProperties($r, $r['contest'], $valueProperties);

        $original_contest = ContestsDAO::getByPK($r['contest']->contest_id);

        // Push changes
        try {
            // Begin a new transaction
            ContestsDAO::transBegin();

            // Save the contest object with data sent by user to the database
            self::updateContest($r['contest'], $original_contest, $r['current_user_id']);

            // Save the problemset object with data sent by user to the database
            $problemset = ProblemsetsDAO::getByPK($r['contest']->problemset_id);
            $problemset->needs_basic_information = $r['basic_information'] ?? 0;
            $problemset->requests_user_information = $r['requests_user_information'] ?? 'no';
            ProblemsetsDAO::save($problemset);

            if (!is_null($r['problems'])) {
                // Get current problems
                $currentProblemIds = ProblemsetProblemsDAO::getIdByProblemset($r['contest']->problemset_id);
                // Check who needs to be deleted and who needs to be added
                $to_delete = array_diff($currentProblemIds, self::$problems_id);
                $to_add = array_diff(self::$problems_id, $currentProblemIds);

                foreach ($to_add as $problem) {
                    ProblemsetProblemsDAO::save(new ProblemsetProblems([
                        'problemset_id' => $r['contest']->problemset_id,
                        'problem_id' => $problem,
                        'points' => $r['problems'][$problem]['points']
                    ]));
                }

                foreach ($to_delete as $problem) {
                    ProblemsetProblemsDAO::delete(new ProblemsetProblems([
                        'problemset_id' => $r['contest']->problemset_id,
                        'problem_id' => $problem,
                    ]));
                }
            }

            // End transaction
            ContestsDAO::transEnd();
        } catch (Exception $e) {
            // Operation failed in the data layer, rollback transaction
            ContestsDAO::transRollback();

            throw new InvalidDatabaseOperationException($e);
        }

        // Expire contest-info cache
        Cache::deleteFromCache(Cache::CONTEST_INFO, $r['contest_alias']);

        // Expire contest scoreboard cache
        Scoreboard::invalidateScoreboardCache(ScoreboardParams::fromContest($r['contest']));

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
     * @throws InvalidDatabaseOperationException
     * @throws NotFoundException
     * @throws ForbiddenAccessException
     */
    private static function validateRuns(Request $r) {
        // Defaults for offset and rowcount
        if (!isset($r['offset'])) {
            $r['offset'] = 0;
        }
        if (!isset($r['rowcount'])) {
            $r['rowcount'] = 100;
        }

        Validators::isStringNonEmpty($r['contest_alias'], 'contest_alias');

        try {
            $r['contest'] = ContestsDAO::getByAlias($r['contest_alias']);
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }

        if (is_null($r['contest'])) {
            throw new NotFoundException('contestNotFound');
        }

        if (!Authorization::isContestAdmin($r['current_identity_id'], $r['contest'])) {
            throw new ForbiddenAccessException('userNotAllowed');
        }

        Validators::isNumber($r['offset'], 'offset', false);
        Validators::isNumber($r['rowcount'], 'rowcount', false);
        Validators::isInEnum($r['status'], 'status', ['new', 'waiting', 'compiling', 'running', 'ready'], false);
        Validators::isInEnum($r['verdict'], 'verdict', ['AC', 'PA', 'WA', 'TLE', 'MLE', 'OLE', 'RTE', 'RFE', 'CE', 'JE', 'NO-AC'], false);

        // Check filter by problem, is optional
        if (!is_null($r['problem_alias'])) {
            Validators::isStringNonEmpty($r['problem_alias'], 'problem');

            try {
                $r['problem'] = ProblemsDAO::getByAlias($r['problem_alias']);
            } catch (Exception $e) {
                // Operation failed in the data layer
                throw new InvalidDatabaseOperationException($e);
            }

            if (is_null($r['problem'])) {
                throw new NotFoundException('problemNotFound');
            }
        }

        Validators::isInEnum($r['language'], 'language', array_keys(RunController::$kSupportedLanguages), false);

        // Get user if we have something in username
        if (!is_null($r['username'])) {
            $r['identity'] = IdentityController::resolveIdentity($r['username']);
        }
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
        self::validateRuns($r);

        // Get our runs
        try {
            $runs = RunsDAO::GetAllRuns(
                $r['contest']->problemset_id,
                $r['status'],
                $r['verdict'],
                !is_null($r['problem']) ? $r['problem']->problem_id : null,
                $r['language'],
                !is_null($r['identity']) ? $r['identity']->identity_id : null,
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
     * @param Request $r
     * @throws InvalidDatabaseOperationException
     * @throws ForbiddenAccessException
     */
    private static function validateStats(Request $r) {
        Validators::isStringNonEmpty($r['contest_alias'], 'contest_alias');

        try {
            $r['contest'] = ContestsDAO::getByAlias($r['contest_alias']);
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }

        // This API is Contest Admin only
        if (is_null($r['contest']) || !Authorization::isContestAdmin($r['current_identity_id'], $r['contest'])) {
            throw new ForbiddenAccessException('userNotAllowed');
        }
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

        self::validateStats($r);

        try {
            // Array of GUIDs of pending runs
            $pendingRunsGuids = RunsDAO::GetPendingRunsOfProblemset($r['contest']->problemset_id);

            // Count of pending runs (int)
            $totalRunsCount = (int)RunsDAO::CountTotalRunsOfProblemset($r['contest']->problemset_id);

            // Wait time
            $waitTimeArray = RunsDAO::GetLargestWaitTimeOfProblemset($r['contest']->problemset_id);

            // List of verdicts
            $verdictCounts = [];

            foreach (self::$verdicts as $verdict) {
                $verdictCounts[$verdict] = (int)RunsDAO::CountTotalRunsOfProblemsetByVerdict($r['contest']->problemset_id, $verdict);
            }

            // Get max points posible for contest
            $totalPoints = ProblemsetProblemsDAO::getMaxPointsByProblemset($r['contest']->problemset_id);

            // Get scoreboard to calculate distribution
            $distribution = [];
            for ($i = 0; $i < 101; $i++) {
                $distribution[$i] = 0;
            }

            $sizeOfBucket = $totalPoints / 100;
            $scoreboardResponse = self::apiScoreboard($r);
            foreach ($scoreboardResponse['ranking'] as $results) {
                $distribution[(int)($results['total']['points'] / $sizeOfBucket)]++;
            }
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }

        return [
            'total_runs' => $totalRunsCount,
            'pending_runs' => $pendingRunsGuids,
            'max_wait_time' => empty($waitTimeArray) ? 0 : $waitTimeArray[1],
            'max_wait_time_guid' => empty($waitTimeArray) ? 0 : $waitTimeArray[0]->guid,
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

        self::validateStats($r);

        $params = ScoreboardParams::fromContest($r['contest']);
        $params['admin'] = true;
        $params['auth_token'] = $r['auth_token'];
        $scoreboard = new Scoreboard($params);

        // Check the filter if we have one
        Validators::isStringNonEmpty($r['filterBy'], 'filterBy', false /* not required */);

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

        self::validateStats($r);

        // Get full Report API of the contest
        $reportRequest = new Request([
                    'contest_alias' => $r['contest_alias'],
                    'auth_token' => $r['auth_token'],
                ]);
        $contestReport = self::apiReport($reportRequest);

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
                if (!isset($problemData['run_details']['cases']) || count($problemData['run_details']['cases']) === 0) {
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

        self::validateStats($r);

        // Get our runs
        try {
            $runs = RunsDAO::getByContest($r['contest']->contest_id);
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }

        $zip = new ZipStream($r['contest_alias'] . '.zip');

        // Add runs to zip
        $table = "guid,user,problem,verdict,points\n";
        foreach ($runs as $run) {
            $zip->add_file_from_path(
                'runs/' . $run->guid,
                RunController::getSubmissionPath($run)
            );

            $columns[0] = 'username';
            $columns[1] = 'alias';
            $usernameProblemData = $run->asFilteredArray($columns);

            $table .= $run->guid . ',' . $usernameProblemData['username'] . ',' . $usernameProblemData['alias'] . ',' . $run->verdict . ',' . $run->contest_score;
            $table .= "\n";
        }

        $zip->add_file('summary.csv', $table);

        // Return zip
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
                if (Authorization::isSystemAdmin($r['current_identity_id'])) {
                    return [
                        'status' => 'ok',
                        'admin' => true
                    ];
                }
            }

            self::validateDetails($r);

            return [
                'status' => 'ok',
                'admin' => $r['contest_admin']
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

        if (!Authorization::isSystemAdmin($r['current_identity_id'])) {
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
        Validators::isInEnum($r['value'], 'value', ['0', '1']);

        $r['contest']->recommended = $r['value'];

        try {
            ContestsDAO::save($r['contest']);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        return ['status' => 'ok'];
    }

    public static function isPublic($admission_mode) {
        return $admission_mode != 'private';
    }
}
