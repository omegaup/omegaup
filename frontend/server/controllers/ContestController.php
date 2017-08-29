<?php

require_once('libs/dao/Contests.dao.php');

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
            $cache_key = "$active_contests-$recommended-$page-$page_size";
            if ($r['current_user_id'] === null) {
                // Get all public contests
                Cache::getFromCacheOrSet(
                    Cache::CONTESTS_LIST_PUBLIC,
                    $cache_key,
                    $r,
                    function (Request $r) use ($page, $page_size, $active_contests, $recommended) {
                            return ContestsDAO::getAllPublicContests($page, $page_size, $active_contests, $recommended);
                    },
                    $contests
                );
            } elseif (Authorization::isSystemAdmin($r['current_user_id'])) {
                // Get all contests
                Cache::getFromCacheOrSet(
                    Cache::CONTESTS_LIST_SYSTEM_ADMIN,
                    $cache_key,
                    $r,
                    function (Request $r) use ($page, $page_size, $active_contests, $recommended) {
                            return ContestsDAO::getAllContests($page, $page_size, $active_contests, $recommended);
                    },
                    $contests
                );
            } else {
                // Get all public+private contests
                $contests = ContestsDAO::getAllContestsForUser($r['current_user_id'], $page, $page_size, $active_contests, $recommended);
            }
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        // Filter returned values by these columns
        $relevantColumns = [
            'contest_id',
            'title',
            'description',
            'start_time',
            'finish_time',
            'public',
            'alias',
            'window_length',
            'recommended',
            ];

        $addedContests = [];
        foreach ($contests as $c) {
            $contestInfo = $c->asFilteredArray($relevantColumns);

            $contestInfo['duration'] = (is_null($c->window_length) ?
                                $c->finish_time - $c->start_time : ($c->window_length * 60));

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
        $relevant_columns = ['title', 'alias', 'start_time', 'finish_time', 'public', 'scoreboard_url', 'scoreboard_url_admin'];
        $contests = null;
        try {
            if (Authorization::isSystemAdmin($r['current_user_id'])) {
                $contests = ContestsDAO::getAll(
                    $page,
                    $pageSize,
                    'contest_id',
                    'DESC'
                );
            } else {
                $contests = ContestsDAO::getAllContestsAdminedByUser(
                    $r['current_user_id'],
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
     * Returns a list of contests where current user is the director
     *
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     */
    public static function apiMyList(Request $r) {
        self::authenticateRequest($r);

        Validators::isNumber($r['page'], 'page', false);
        Validators::isNumber($r['page_size'], 'page_size', false);

        $page = (isset($r['page']) ? intval($r['page']) : 1);
        $pageSize = (isset($r['page_size']) ? intval($r['page_size']) : 1000);

        // Create array of relevant columns
        $relevant_columns = ['title', 'alias', 'start_time', 'finish_time', 'public', 'scoreboard_url', 'scoreboard_url_admin'];
        $contests = null;
        try {
            $contests = ContestsDAO::getAllContestsOwnedByUser(
                $r['current_user_id'],
                $page,
                $pageSize
            );
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
     * Checks if user can access contests: If the contest is private then the user
     * must be added to the contest (an entry ProblemsetUsers must exists) OR the user
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

        if ($r['contest']->public != 1) {
            try {
                if (is_null(ProblemsetUsersDAO::getByPK($r['current_user_id'], $r['contest']->problemset_id))
                        && !Authorization::isContestAdmin($r['current_user_id'], $r['contest'])) {
                    throw new ForbiddenAccessException('userNotAllowed');
                }
            } catch (ApiException $e) {
                // Propagate exception
                throw $e;
            } catch (Exception $e) {
                // Operation failed in the data layer
                throw new InvalidDatabaseOperationException($e);
            }
        } else {
            if ($r['contest']->contestant_must_register == '1') {
                if (!Authorization::isContestAdmin($r['current_user_id'], $r['contest'])) {
                    $req = ProblemsetUserRequestDAO::getByPK($r['current_user_id'], $r['contest']->problemset_id);

                    if (is_null($req) || ($req->accepted === '0')) {
                        throw new ForbiddenAccessException('contestNotRegistered');
                    }
                }
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
            $r['contest'] = ContestsDAO::getByAlias($r['contest_alias']);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        if (is_null($r['contest'])) {
            throw new NotFoundException('contestNotFound');
        }
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
        return $r['contest']->public == 1 ||
            !is_null(ProblemsetUsersDAO::getByPK(
                $r['current_user_id'],
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

        try {
            // Half-authenticate, in case there is no session in place.
            $session = SessionController::apiCurrentSession($r)['session'];
            if ($session['valid'] && !is_null($session['user'])) {
                $r['current_user'] = $session['user'];
                $r['current_user_id'] = $session['user']->user_id;
            } else {
                // No session, show the intro (if public), so that they can login.
                return $r['contest']->public ? ContestController::SHOW_INTRO : !ContestController::SHOW_INTRO;
            }
            self::canAccessContest($r);
        } catch (Exception $e) {
            // Could not access contest. Private contests must not be leaked, so
            // unless they were manually added beforehand, show them a 404 error.
            if (!ContestController::isInvitedToContest($r)) {
                throw $e;
            }
            self::$log->error('Exception while trying to verify access: ' . $e);
            return ContestController::SHOW_INTRO;
        }

        $cs = SessionController::apiCurrentSession()['session'];

        // You already started the contest.
        $contestOpened = ProblemsetUsersDAO::getByPK(
            $r['current_user_id'],
            $r['contest']->problemset_id
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

            $r['contest_admin'] = Authorization::isContestAdmin($r['current_user_id'], $r['contest']);
            if (!ContestsDAO::hasStarted($r['contest']) && !$r['contest_admin']) {
                $exception = new PreconditionFailedException('contestNotStarted');
                $exception->addCustomMessageToArray('start_time', strtotime($r['contest']->start_time));

                throw $exception;
            }
        } else {
            if ($r['token'] === $r['contest']->scoreboard_url_admin) {
                $r['contest_admin'] = true;
            } elseif ($r['token'] !== $r['contest']->scoreboard_url) {
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
        $relevant_columns = ['title', 'description', 'start_time', 'finish_time', 'window_length', 'alias', 'scoreboard', 'points_decay_factor', 'partial_score', 'submissions_gap', 'feedback', 'penalty', 'time_start', 'penalty_type', 'penalty_calc_policy', 'public', 'show_scoreboard_after', 'contestant_must_register'];

        // Initialize response to be the contest information
        $result = $r['contest']->asFilteredArray($relevant_columns);

        $current_ses = SessionController::getCurrentSession($r);
        $result['contestant_must_register'] = ($result['contestant_must_register'] == '1');

        if ($current_ses['valid'] && $result['contestant_must_register']) {
            $registration = ProblemsetUserRequestDAO::getByPK($current_ses['user']->user_id, $r['contest']->problemset_id);

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
            ProblemsetUserRequestDAO::save(new ProblemsetUserRequest([
                'user_id' => $r['current_user_id'],
                'problemset_id' => $r['contest']->problemset_id,
                'request_time' => gmdate('Y-m-d H:i:s'),
            ]));
        } catch (Exception $e) {
            self::$log->error('Failed to create new ProblemsetUserRequest: ' . $e->getMessage());
            throw new InvalidDatabaseOperationException($e);
        }

        return ['status' => 'ok'];
    }

    /**
     * Joins a contest - explicitly adds a user to a contest.
     *
     * @param Request $r
     */
    public static function apiOpen(Request $r) {
        self::validateDetails($r);
        ProblemsetUsersDAO::CheckAndSaveFirstTimeAccess(
            $r['current_user_id'],
            $r['contest']->problemset_id,
            true
        );
        self::$log->info("User '{$r['current_user']->username}' joined contest '{$r['contest']->alias}'");
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
                'points_decay_factor',
                'partial_score',
                'submissions_gap',
                'feedback',
                'penalty',
                'time_start',
                'penalty_type',
                'penalty_calc_policy',
                'public',
                'show_scoreboard_after',
                'contestant_must_register',
                'languages',
                'problemset_id'];

            // Initialize response to be the contest information
            $result = $r['contest']->asFilteredArray($relevant_columns);

            $result['start_time'] = strtotime($result['start_time']);
            $result['finish_time'] = strtotime($result['finish_time']);

            try {
                $acl = ACLsDAO::getByPK($r['contest']->acl_id);
                $result['director'] = UsersDAO::getByPK($acl->owner_id)->username;
            } catch (Exception $e) {
                // Operation failed in the data layer
                throw new InvalidDatabaseOperationException($e);
            }

            // Get problems of the contest
            $key_problemsInContest = new ProblemsetProblems(
                [
                        'problemset_id' => $r['contest']->problemset_id
                    ]
            );

            try {
                $problemsInContest = ProblemsetProblemsDAO::search($key_problemsInContest, 'order');
            } catch (Exception $e) {
                // Operation failed in the data layer
                throw new InvalidDatabaseOperationException($e);
            }

            // Add info of each problem to the contest
            $problemsResponseArray = [];

            // Set of columns that we want to show through this API. Doesn't include the SOURCE
            $relevant_columns = ['title', 'alias', 'validator', 'time_limit',
                'overall_wall_time_limit', 'extra_wall_time', 'memory_limit',
                'visits', 'submissions', 'accepted', 'dificulty', 'order',
                'languages'];
            $letter = 0;

            foreach ($problemsInContest as $problemkey) {
                try {
                    // Get the data of the problem
                    $temp_problem = ProblemsDAO::getByPK($problemkey->problem_id);
                } catch (Exception $e) {
                    // Operation failed in the data layer
                    throw new InvalidDatabaseOperationException($e);
                }

                // Add the 'points' value that is stored in the ContestProblem relationship
                $temp_array = $temp_problem->asFilteredArray($relevant_columns);
                $temp_array['points'] = $problemkey->points;
                $temp_array['letter'] = ContestController::columnName($letter++);
                if (!empty($result['languages'])) {
                    $temp_array['languages'] = join(',', array_intersect(
                        explode(',', $result['languages']),
                        explode(',', $temp_array['languages'])
                    ));
                }

                // Save our array into the response
                array_push($problemsResponseArray, $temp_array);
            }

            // Add problems to response
            $result['problems'] = $problemsResponseArray;

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

        if (is_null($r['token'])) {
            // Adding timer info separately as it depends on the current user and we don't
            // want this to get generally cached for everybody
            // Save the time of the first access
            try {
                $problemset_user = ProblemsetUsersDAO::CheckAndSaveFirstTimeAccess(
                    $r['current_user_id'],
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
            $result['admin'] = Authorization::isContestAdmin($r['current_user_id'], $r['contest']);

            // Log the operation.
            ProblemsetAccessLogDAO::save(new ProblemsetAccessLog([
                'user_id' => $r['current_user_id'],
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

        if (!Authorization::isContestAdmin($r['current_user_id'], $r['contest'])) {
            throw new ForbiddenAccessException();
        }

        $result = [];
        self::getCachedDetails($r, $result);

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

        $problemset = ProblemsetsDAO::getByPK($r['contest']->problemset_id);
        $accesses = ProblemsetAccessLogDAO::GetAccessForProblemset($problemset);
        $submissions = SubmissionLogDAO::GetSubmissionsForProblemset($problemset);

        // Merge both logs.
        $result['events'] = [];
        $lenAccesses = count($accesses);
        $lenSubmissions = count($submissions);
        $iAccesses = 0;
        $iSubmissions = 0;

        while ($iAccesses < $lenAccesses && $iSubmissions < $lenSubmissions) {
            if ($accesses[$iAccesses]['time'] < $submissions[$iSubmissions]['time']) {
                array_push($result['events'], ContestController::processAccess(
                    $accesses[$iAccesses++]
                ));
            } else {
                array_push($result['events'], ContestController::processSubmission(
                    $submissions[$iSubmissions++]
                ));
            }
        }

        while ($iAccesses < $lenAccesses) {
            array_push($result['events'], ContestController::processAccess(
                $accesses[$iAccesses++]
            ));
        }

        while ($iSubmissions < $lenSubmissions) {
            array_push($result['events'], ContestController::processSubmission(
                $submissions[$iSubmissions++]
            ));
        }

        // Anonimize data.
        $ipMapping = [];
        foreach ($result['events'] as &$entry) {
            if (!array_key_exists($entry['ip'], $ipMapping)) {
                $ipMapping[$entry['ip']] = count($ipMapping);
            }
            $entry['ip'] = $ipMapping[$entry['ip']];
        }

        $result['status'] = 'ok';
        return $result;
    }

    private static function processAccess(&$access) {
        return [
            'username' => $access['username'],
            'time' => (int)$access['time'],
            'ip' => (int)$access['ip'],
            'event' => [
                'name' => 'open',
            ],
        ];
    }

    private static function processSubmission(&$submission) {
        return [
            'username' => $submission['username'],
            'time' => (int)$submission['time'],
            'ip' => (int)$submission['ip'],
            'event' => [
                'name' => 'submit',
                'problem' => $submission['alias'],
            ],
        ];
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

        $contest->public = $r['public'];
        $contest->title = $r['title'];
        $contest->description = $r['description'];
        $contest->start_time = gmdate('Y-m-d H:i:s', $r['start_time']);
        $contest->finish_time = gmdate('Y-m-d H:i:s', $r['finish_time']);
        $contest->window_length = $r['window_length'] === 'NULL' ? null : $r['window_length'];
        $contest->rerun_id = 0; // NYI
        $contest->alias = $r['alias'];
        $contest->scoreboard = $r['scoreboard'];
        $contest->points_decay_factor = $r['points_decay_factor'];
        $contest->partial_score = is_null($r['partial_score']) ? '1' : $r['partial_score'];
        $contest->submissions_gap = $r['submissions_gap'];
        $contest->feedback = $r['feedback'];
        $contest->penalty = max(0, intval($r['penalty']));
        $contest->penalty_type = $r['penalty_type'];
        $contest->penalty_calc_policy = is_null($r['penalty_calc_policy']) ? 'sum' : $r['penalty_calc_policy'];
        $contest->languages = empty($r['languages']) ? null : $r['languages'];
        $contest->scoreboard_url = SecurityTools::randomString(30);
        $contest->scoreboard_url_admin = SecurityTools::randomString(30);

        if (!is_null($r['show_scoreboard_after'])) {
            $contest->show_scoreboard_after = $r['show_scoreboard_after'];
        } else {
            $contest->show_scoreboard_after = '1';
        }

        if ($r['public'] == 1 && is_null($r['problems'])) {
            throw new InvalidParameterException('contestPublicRequiresProblem');
        }

        $acl = new ACLs();
        $acl->owner_id = $r['current_user_id'];

        // Push changes
        try {
            // Begin a new transaction
            ContestsDAO::transBegin();

            ACLsDAO::save($acl);
            $contest->acl_id = $acl->acl_id;

            $problemset = new Problemsets([
                'acl_id' => $acl->acl_id
            ]);
            ProblemsetsDAO::save($problemset);
            $contest->problemset_id = $problemset->problemset_id;

            // Save the contest object with data sent by user to the database
            ContestsDAO::save($contest);

            // If the contest is private, add the list of allowed users
            if ($r['public'] != 1 && $r['hasPrivateUsers']) {
                foreach ($r['private_users_list'] as $userkey) {
                    // Create a temp DAO for the relationship
                    $temp_user_contest = new ProblemsetUsers([
                                'problemset_id' => $problemset->problemset_id,
                                'user_id' => $userkey,
                                'access_time' => null,
                                'score' => 0,
                                'time' => 0
                            ]);

                    // Save the relationship in the DB
                    ProblemsetUsersDAO::save($temp_user_contest);
                }
            }

            if (!is_null($r['problems'])) {
                foreach ($r['problems'] as $problem) {
                    $problemset_problem = new ProblemsetProblems([
                                'problemset_id' => $problemset->problemset_id,
                                'problem_id' => $problem['id'],
                                'points' => $problem['points']
                            ]);

                    ProblemsetProblemsDAO::save($problemset_problem);
                }
            }

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

        self::$log->info('New Contest Created: ' . $r['alias']);
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

            if (!Authorization::isContestAdmin($r['current_user_id'], $r['contest'])) {
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

        Validators::isInEnum($r['public'], 'public', ['0', '1'], $is_required);
        Validators::isValidAlias($r['alias'], 'alias', $is_required);
        Validators::isNumberInRange($r['scoreboard'], 'scoreboard', 0, 100, $is_required);
        Validators::isNumberInRange($r['points_decay_factor'], 'points_decay_factor', 0, 1, $is_required);
        Validators::isInEnum($r['partial_score'], 'partial_score', ['0', '1'], false);
        Validators::isNumberInRange($r['submissions_gap'], 'submissions_gap', 0, $contest_length, $is_required);

        Validators::isInEnum($r['feedback'], 'feedback', ['no', 'yes', 'partial'], $is_required);
        Validators::isInEnum($r['penalty_type'], 'penalty_type', ['contest_start', 'problem_open', 'runtime', 'none'], $is_required);
        Validators::isInEnum($r['penalty_calc_policy'], 'penalty_calc_policy', ['sum', 'max'], false);

        // Check that the users passed through the private_users parameter are valid
        if (!is_null($r['public']) && $r['public'] != 1 && !is_null($r['private_users'])) {
            // Validate that the request is well-formed
            $r['private_users_list'] = json_decode($r['private_users']);
            if (is_null($r['private_users_list'])) {
                throw new InvalidParameterException('parameterInvalid', 'private_users');
            }

            // Validate that all users exists in the DB
            foreach ($r['private_users_list'] as $userkey) {
                if (is_null(UsersDAO::getByPK($userkey))) {
                    throw new InvalidParameterException('parameterNotFound', 'private_users');
                }
            }

            // Turn on flag to add private users later
            $r['hasPrivateUsers'] = true;
        }

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
                ProblemsetController::validateAddProblemToProblemset(null, $p, $r['current_user_id']);
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
        if (!Authorization::isContestAdmin($r['current_user_id'], $contest)) {
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

        $problemset = ProblemsetsDAO::getByPK($params['contest']->problemset_id);

        if (ProblemsetProblemsDAO::countProblemsetProblems($problemset)
                >= MAX_PROBLEMS_IN_CONTEST) {
            throw new PreconditionFailedException('contestAddproblemTooManyProblems');
        }

        ProblemsetController::addProblem(
            $params['contest']->problemset_id,
            $params['problem'],
            $r['current_user_id'],
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
        if (!Authorization::isContestAdmin($r['current_user_id'], $contest)) {
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

        if ($problem->visibility == ProblemController::VISIBILITY_BANNED) {
            throw new ForbiddenAccessException('problemIsBanned');
        }
        if (!ProblemsDAO::isVisible($problem) && !Authorization::isProblemAdmin($r['current_user_id'], $problem)) {
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
        if (!Authorization::isContestAdmin($r['current_user_id'], $contest)) {
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
            !Authorization::isSystemAdmin($r['current_user_id'])) {
            throw new ForbiddenAccessException('cannotRemoveProblemWithSubmissions');
        }

        if ($contest->public == 1) {
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
        if (!Authorization::isContestAdmin($r['current_user_id'], $r['contest'])) {
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
            ProblemsetUsersDAO::save(new ProblemsetUsers([
                'problemset_id' => $r['contest']->problemset_id,
                'user_id' => $r['user']->user_id,
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
            ProblemsetUsersDAO::delete(new ProblemsetUsers([
                'problemset_id' => $r['contest']->problemset_id,
                'user_id' => $r['user']->user_id,
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
            $r['contest'] = ContestsDAO::getByAlias($r['contest_alias']);
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }

        // Only director is allowed to create problems in contest
        if (!Authorization::isContestAdmin($r['current_user_id'], $r['contest'])) {
            throw new ForbiddenAccessException();
        }

        $user_role = new UserRoles();
        $user_role->acl_id = $r['contest']->acl_id;
        $user_role->user_id = $user->user_id;
        $user_role->role_id = Authorization::ADMIN_ROLE;

        // Save the contest to the DB
        try {
            UserRolesDAO::save($user_role);
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }

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
            $r['contest'] = ContestsDAO::getByAlias($r['contest_alias']);
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }

        // Only admin is alowed to make modifications
        if (!Authorization::isContestAdmin($r['current_user_id'], $r['contest'])) {
            throw new ForbiddenAccessException();
        }

        // Check if admin to delete is actually an admin
        if (!Authorization::isContestAdmin($user->user_id, $r['contest'])) {
            throw new NotFoundException();
        }

        $user_role = new UserRoles();
        $user_role->acl_id = $r['contest']->acl_id;
        $user_role->user_id = $user->user_id;
        $user_role->role_id = Authorization::ADMIN_ROLE;

        // Delete the role
        try {
            UserRolesDAO::delete($user_role);
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }

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
            $r['contest'] = ContestsDAO::getByAlias($r['contest_alias']);
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }

        // Only admins are allowed to modify contest
        if (!Authorization::isContestAdmin($r['current_user_id'], $r['contest'])) {
            throw new ForbiddenAccessException();
        }

        $group_role = new GroupRoles();
        $group_role->acl_id = $r['contest']->acl_id;
        $group_role->group_id = $group->group_id;
        $group_role->role_id = Authorization::ADMIN_ROLE;

        // Save the contest to the DB
        try {
            GroupRolesDAO::save($group_role);
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }

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
            $r['contest'] = ContestsDAO::getByAlias($r['contest_alias']);
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }

        // Only admin is alowed to make modifications
        if (!Authorization::isContestAdmin($r['current_user_id'], $r['contest'])) {
            throw new ForbiddenAccessException();
        }

        $group_role = new GroupRoles();
        $group_role->acl_id = $r['contest']->acl_id;
        $group_role->group_id = $group->group_id;
        $group_role->role_id = Authorization::ADMIN_ROLE;

        // Delete the role
        try {
            GroupRolesDAO::delete($group_role);
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }

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
            $r['current_user_id'],
            $r['contest']
        );

        try {
            $clarifications = ClarificationsDAO::GetProblemsetClarifications(
                $r['contest']->problemset_id,
                $is_contest_director,
                $r['current_user_id'],
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
        $params['show_all_runs'] = Authorization::isContestAdmin($r['current_user_id'], $r['contest']);
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

        // If true, will override Scoreboard Pertentage to 100%
        $showAllRuns = false;

        if (is_null($r['token'])) {
            // Get the current user
            self::authenticateRequest($r);

            self::canAccessContest($r);

            if (Authorization::isContestAdmin($r['current_user_id'], $r['contest'])) {
                $showAllRuns = true;
            }
        } else {
            if ($r['token'] === $r['contest']->scoreboard_url) {
                $showAllRuns = false;
            } elseif ($r['token'] === $r['contest']->scoreboard_url_admin) {
                $showAllRuns = true;
            } else {
                throw new ForbiddenAccessException('invalidScoreboardUrl');
            }
        }

        // Create scoreboard
        $params = ScoreboardParams::fromContest($r['contest']);
        $params['show_all_runs'] = $showAllRuns;
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

        if (!Authorization::isContestAdmin($r['current_user_id'], $contest)) {
            throw new ForbiddenAccessException();
        }

        try {
            $db_results = ProblemsetUserRequestDAO::getRequestsForProblemset($contest->problemset_id);
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

            $result = new ProblemsetUserRequest($result);
            $user_id = $result->user_id;
            $user = UsersDAO::getByPK($user_id);

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

        if (!Authorization::isContestAdmin($r['current_user_id'], $contest)) {
            throw new ForbiddenAccessException();
        }

        $targetUser = UsersDAO::FindByUsername($r['username']);

        $request = ProblemsetUserRequestDAO::getByPK($targetUser->user_id, $contest->problemset_id);

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

        ProblemsetUserRequestDAO::save($request);

        // Save this action in the history
        ProblemsetUserRequestHistoryDAO::save(new ProblemsetUserRequestHistory([
            'user_id' => $request->user_id,
            'problemset_id' => $contest->problemset_id,
            'time' => $request->last_update,
            'admin_id' => $r['current_user_id'],
            'accepted' => $request->accepted,
        ]));

        self::$log->info('Arbitrated contest for user, new accepted user_id='
                                . $targetUser->user_id . ', state=' . $resolution);

        return ['status' => 'ok'];
    }

    /**
     * Returns ALL users participating in a contest
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

        if (!Authorization::isContestAdmin($r['current_user_id'], $contest)) {
            throw new ForbiddenAccessException();
        }

        // Get users from DB
        $problemset_user = new ProblemsetUsers();
        $problemset_user->problemset_id = $contest->problemset_id;

        try {
            $db_results = ProblemsetUsersDAO::search($problemset_user);
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }

        $users = [];

        // Add all users to an array
        foreach ($db_results as $result) {
            $user_id = $result->user_id;
            $user = UsersDAO::getByPK($user_id);
            $users[] = ['user_id' => $user_id, 'username' => $user->username, 'access_time' => $result->access_time, 'country' => $user->country_id];
        }

        $response = [];
        $response['users'] = $users;
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

        if (!Authorization::isContestAdmin($r['current_user_id'], $contest)) {
            throw new ForbiddenAccessException();
        }

        $response = [];
        $response['admins'] = UserRolesDAO::getContestAdmins($contest);
        $response['group_admins'] = GroupRolesDAO::getContestAdmins($contest);
        $response['status'] = 'ok';

        return $response;
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

        // Update contest DAO
        if (!is_null($r['public'])) {
            // If going public
            if ($r['public'] == 1) {
                self::validateContestCanBePublic($r['contest']);
            }

            $r['contest']->public = $r['public'];
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
            'contestant_must_register',
        ];
        self::updateValueProperties($r, $r['contest'], $valueProperties);

        // Push changes
        try {
            // Begin a new transaction
            ContestsDAO::transBegin();

            // Save the contest object with data sent by user to the database
            ContestsDAO::save($r['contest']);

            // If the contest is private, add the list of allowed users
            if (!is_null($r['public']) && $r['public'] != 1 && $r['hasPrivateUsers']) {
                // Get current users
                $problemset_user = new ProblemsetUsers(['problemset_id' => $r['contest']->problemset_id]);
                $current_users = ProblemsetUsersDAO::search($problemset_user);
                $current_users_id = [];

                foreach ($current_users as $cu) {
                    array_push($current_users_id, $current_users->user_id);
                }

                // Check who needs to be deleted and who needs to be added
                $to_delete = array_diff($current_users_id, $r['private_users_list']);
                $to_add = array_diff($r['private_users_list'], $current_users_id);

                // Add users in the request
                foreach ($to_add as $userkey) {
                    // Create a temp DAO for the relationship
                    $temp_user_contest = new ProblemsetUsers([
                                'problemset_id' => $r['contest']->problemset_id,
                                'user_id' => $userkey,
                                'access_time' => null,
                                'score' => 0,
                                'time' => 0
                            ]);

                    // Save the relationship in the DB
                    ProblemsetUsersDAO::save($temp_user_contest);
                }

                // Delete users
                foreach ($to_delete as $userkey) {
                    // Create a temp DAO for the relationship
                    $temp_user_contest = new ProblemsetUsers([
                                'problemset_id' => $r['contest']->problemset_id,
                                'user_id' => $userkey,
                            ]);

                    // Delete the relationship in the DB
                    ProblemsetUsersDAO::delete(ProblemsetUsersDAO::search($temp_user_contest));
                }
            }

            if (!is_null($r['problems'])) {
                // Get current problems
                $p_key = new Problems(['contest_id' => $r['contest']->contest_id]);
                $current_problems = ProblemsDAO::search($p_key);
                $current_problems_id = [];

                foreach ($current_problems as $p) {
                    array_push($current_problems_id, $p->problem_id);
                }

                // Check who needs to be deleted and who needs to be added
                $to_delete = array_diff($current_problems_id, self::$problems_id);
                $to_add = array_diff(self::$problems_id, $current_problems_id);

                foreach ($to_add as $problem) {
                    $contest_problem = new ProblemsetProblems([
                                'problemset_id' => $r['contest']->problemset_id,
                                'problem_id' => $problem,
                                'points' => $r['problems'][$problem]['points']
                            ]);

                    ProblemsetProblemsDAO::save($contest_problem);
                }

                foreach ($to_delete as $problem) {
                    $contest_problem = new ProblemsetProblems([
                                'problemset_id' => $r['contest']->problemset_id,
                                'problem_id' => $problem,
                            ]);

                    ProblemsetProblemsDAO::delete(ProblemsetProblemsDAO::search($contest_problem));
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

        if (!Authorization::isContestAdmin($r['current_user_id'], $r['contest'])) {
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

        Validators::isInEnum($r['language'], 'language', ['c', 'cpp', 'cpp11', 'java', 'py', 'rb', 'pl', 'cs', 'pas', 'kp', 'kj'], false);

        // Get user if we have something in username
        if (!is_null($r['username'])) {
            $r['user'] = UserController::resolveUser($r['username']);
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
                !is_null($r['user']) ? $r['user']->user_id : null,
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
        if (is_null($r['contest']) || !Authorization::isContestAdmin($r['current_user_id'], $r['contest'])) {
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
            $totalRunsCount = RunsDAO::CountTotalRunsOfProblemset($r['contest']->problemset_id);

            // Wait time
            $waitTimeArray = RunsDAO::GetLargestWaitTimeOfProblemset($r['contest']->problemset_id);

            // List of verdicts
            $verdict_counts = [];

            foreach (self::$verdicts as $verdict) {
                $verdict_counts[$verdict] = RunsDAO::CountTotalRunsOfProblemsetByVerdict($r['contest']->problemset_id, $verdict);
            }

            // Get max points posible for contest
            $key = new ProblemsetProblems(['problemset_id' => $r['contest']->problemset_id]);
            $problemsetProblems = ProblemsetProblemsDAO::search($key);
            $totalPoints = 0;
            foreach ($problemsetProblems as $cP) {
                $totalPoints += $cP->points;
            }

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

        // Para darle gusto al Alanboy, regresando array
        return [
            'total_runs' => $totalRunsCount,
            'pending_runs' => $pendingRunsGuids,
            'max_wait_time' => is_null($waitTimeArray) ? 0 : $waitTimeArray[1],
            'max_wait_time_guid' => is_null($waitTimeArray) ? 0 : $waitTimeArray[0]->guid,
            'verdict_counts' => $verdict_counts,
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
        $params['show_all_runs'] = true;
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
        $relevant_columns = ['run_id', 'guid', 'language', 'status',
            'verdict', 'runtime', 'penalty', 'memory', 'score', 'contest_score',
            'time', 'submit_delay', 'Users.username', 'Problems.alias'];
        try {
            $runs = RunsDAO::search(new Runs([
                                'contest_id' => $r['contest']->contest_id
                            ]), 'time', 'DESC', $relevant_columns);
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

        // Add problem cases to zip
        $problemset = ProblemsetsDAO::getByPK($r['contest']->problemset_id);
        $contest_problems = ProblemsetProblemsDAO::GetRelevantProblems($problemset);
        foreach ($contest_problems as $problem) {
            $zip->add_file_from_path($problem->alias . '_cases.zip', PROBLEMS_PATH . '/' . $problem->alias . '/cases.zip');
        }

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
                if (Authorization::isSystemAdmin($r['current_user_id'])) {
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

        if (!Authorization::isSystemAdmin($r['current_user_id'])) {
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
}
