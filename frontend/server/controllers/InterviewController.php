<?php

class InterviewController extends Controller {
    public static function apiCreate(Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        self::authenticateRequest($r);

        // Create the contest that will back this interview
        $r['public'] = false;
        $r['title'] = $r['title'];
        $r['description'] = $r['title'];
        $r['start_time'] = time();
        $r['finish_time'] = strtotime('+1 year');
        $r['window_length'] = $r['duration'];
        $r['alias'] = str_replace(' ', '', $r['title']);
        $r['scoreboard'] = 0;
        $r['points_decay_factor'] = 0;
        $r['partial_score'] = 0;
        $r['submissions_gap'] = 0;
        $r['feedback'] = 'no';
        $r['penalty'] = 0;
        $r['penalty_type'] = 'none';
        $r['penalty_calc_policy'] = 'sum';
        $r['languages'] = null;
        $r['interview'] = true;
        $r['contestant_must_register'] = true;

        $createdContest = ContestController::apiCreate($r);

        $interview = new Interview();
        $interview->user_id = $r['current_user_id'];
        //$interview->title = $r['title'];
        //$interview->duration = $r['duration'];
        $interview->contest_id = $r['contest_id'];
        InterviewDAO::save($interview);

        self::$log->info('Created new interview  ' . $r['alias']);

        return array('status' => 'ok');
    }

    /**
     * Adds a user to a interview.
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

        // Does the user exist ?
        try {
            $r['user'] = UserController::resolveUser($r['usernameOrEmail']);
        } catch (NotFoundException $e) {
            // this is fine
        }

        if (is_null($r['user'])) {
            // user does not exist, create a new user
            $newUserRequest = $r;
            $newUserRequest['email'] = $r['usernameOrEmail'];
            $newUserRequest['username'] = 'user'.time(); //$r["usernameOrEmail"];
            $newUserRequest['password'] = 'user'.time();
            UserController::apiCreate($newUserRequest);
        }

        // Authenticate logged user
        self::authenticateRequest($r);
        self::validateAddUser($r);

        $contest_user = new ContestsUsers();
        $contest_user->setContestId($r['contest']->getContestId());
        $contest_user->setUserId($r['user']->getUserId());
        $contest_user->setAccessTime('0000-00-00 00:00:00');
        $contest_user->setScore('0');
        $contest_user->setTime('0');

        // Save the contest to the DB
        try {
            ContestsUsersDAO::save($contest_user);
        } catch (Exception $e) {
            // Operation failed in the data layer
            self::$log->error('Failed to create new ContestUser: ' . $e->getMessage());
            throw new InvalidDatabaseOperationException($e);
        }

        return array('status' => 'ok');
    }

    public static function apiDetails(Request $r) {
        try {
            self::authenticateRequest($r);
        } catch (UnauthorizedException $e) {
            // Do nothing.
        }

        //self::getCachedDetails($r, $result);

        $thisResult = array();

        $backingContest = InterviewDAO::getBackingContestByAlias($r['interview_alias']);
        if (is_null($backingContest)) {
            throw new NotFoundException();
        }

        $thisResult['description'] = $backingContest->description;
        $thisResult['contest_alias'] = $backingContest->alias;

        // assumming interview exists ?
        $interview = InterviewDAO::search(array('contest_id' => $backingContest->contest_id))[0];

        // get current candidates
        $thisResult['title'] = $interview->title;
        //$thisResult["duration"] = $interview>duration;
        $thisResult['created'] = $interview->time;

        $candidatesQuery = new ContestsUsers();
        $candidatesQuery->setContestId($backingContest->contest_id);

        try {
            $db_results = ContestsUsersDAO::search($candidatesQuery);
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }

        $users = array();

        // Add all users to an array
        foreach ($db_results as $result) {
            $user_id = $result->getUserId();
            $user = UsersDAO::getByPK($user_id);
            $users[] = array(
                        'user_id' => $user_id,
                        'username' => $user->getUsername(),
                        'access_time' => $result->access_time,
                        'email' => $user->getMainEmailId(),
                        'country' => $user->getCountryId());
        }

        $thisResult['users'] = $users;

        return $thisResult;
    }

    public static function apiList(Request $r) {
        self::authenticateRequest($r);

        $interviews = null;

        $current_ses = SessionController::getCurrentSession($r);

        try {
            $interviews = InterviewDAO::getMyInterviews($current_ses['id']);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        $response['results'] = $interviews;

        return $response;
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
            $session = SessionController::apiCurrentSession($r);
            if ($session['valid'] && !is_null($session['user'])) {
                $r['current_user'] = $session['user'];
                $r['current_user_id'] = $session['user']->user_id;
            } else {
                // No session, show the intro (if public), so that they can login.
                return $r['contest']->public ? ContestController::SHOW_INTRO : !ContestController::SHOW_INTRO;
            }
            ContestController::canAccessContest($r);
        } catch (Exception $e) {
            // Could not access contest. Private contests must not be leaked, so
            // unless they were manually added beforehand, show them a 404 error.
            if (!ContestController::isInvitedToContest($r)) {
                throw $e;
            }
            self::$log->error('Exception while trying to verify access: ' . $e);
            return ContestController::SHOW_INTRO;
        }

        $cs = SessionController::apiCurrentSession();

        // You already started the contest.
        $contestOpened = ContestsUsersDAO::getByPK(
            $r['current_user_id'],
            $r['contest']->getContestId()
        );
        if (!is_null($contestOpened) &&
            $contestOpened->access_time != '0000-00-00 00:00:00') {
            self::$log->debug('Not intro because you already started the contest');
            return !ContestController::SHOW_INTRO;
        }

        return ContestController::SHOW_INTRO;
    }

//
//    /**
//     * Validates add/remove user request
//     *
//     * @param Request $r
//     * @throws InvalidDatabaseOperationException
//     * @throws InvalidParameterException
//     * @throws ForbiddenAccessException
//     */
    private static function validateAddUser(Request $r) {
        $r['user'] = null;

        // Check contest_alias
        Validators::isStringNonEmpty($r['interview_alias'], 'interview_alias');

        $r['user'] = UserController::resolveUser($r['usernameOrEmail']);

        if (is_null($r['user'])) {
            throw new NotFoundException('userOrMailNotFound');
        }

        try {
            $r['contest'] = ContestsDAO::getByAlias($r['interview_alias']);
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }

        if (is_null($r['contest'])) {
            throw new NotFoundException('contestNotFound');
        }

        // Only director is allowed to create problems in contest
        if (!Authorization::IsContestAdmin($r['current_user_id'], $r['contest'])) {
            throw new ForbiddenAccessException();
        }
    }
}
