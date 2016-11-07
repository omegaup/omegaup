<?php

class InterviewController extends Controller {
    private static function validateCreateOrUpdate(Request $r, $is_update = false) {
        // Interview specific validations. Everything else is validated in ContestController::apiCreate
        $is_required = !$is_update;

        // Only site-admins and interviewers can create interviews for now
        if (!Authorization::isSystemAdmin($r['current_user_id']) && !UsersDAO::IsUserInterviewer($r['current_user']->user_id)) {
            throw new ForbiddenAccessException();
        }

        Validators::isStringNonEmpty($r['title'], 'title', $is_required);
        Validators::isStringNonEmpty($r['description'], 'description', false);
        Validators::isNumberInRange($r['duration'], 'duration', 60, 60 * 5, false);
        Validators::isValidAlias($r['alias'], 'alias', $is_required);
    }

    public static function apiCreate(Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        self::authenticateRequest($r);

        self::validateCreateOrUpdate($r, false);

        // Create the contest that will back this interview
        $r['public'] = false;
        $r['title'] = $r['title'];
        $r['description'] = array_key_exists('description', $r) ? $r['description'] : $r['title'];
        $r['start_time'] = time();
        $r['finish_time'] = strtotime('+1 year');
        $r['window_length'] = $r['duration'];
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
        $r['contestant_must_register'] = 0;

        $createdContest = ContestController::apiCreate($r);

        self::$log->info('Created new interview ' . $r['alias']);

        return array('status' => 'ok');
    }

    public static function apiAddUsers(Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        // Authenticate logged user
        self::authenticateRequest($r);

        Validators::isStringNonEmpty($r['usernameOrEmailsCSV'], 'usernameOrEmailsCSV', true);
        $usersToAdd = explode(',', $r['usernameOrEmailsCSV']);

        foreach ($usersToAdd as $addThisUser) {
            $requestToInternal = new Request($r);
            $requestToInternal['usernameOrEmail'] = $addThisUser;

            self::addUserInternal($requestToInternal);
        }

        return array ('status' => 'ok');
    }

    private static function addUserInternal($r) {
        Validators::isStringNonEmpty($r['interview_alias'], 'interview_alias');
        Validators::isStringNonEmpty($r['usernameOrEmail'], 'usernameOrEmail');

        // Does the interview exist ?
        try {
            $r['contest'] = ContestsDAO::getByAlias($r['interview_alias']);
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }

        if (is_null($r['contest'])) {
            throw new NotFoundException('interviewNotFound');
        }

        // Does the user exist ?
        try {
            $r['user'] = null;
            $r['user'] = UserController::resolveUser($r['usernameOrEmail']);
        } catch (NotFoundException $e) {
            // this is fine, we'll create an account for this user
        }

        global $smarty;
        $r['mail_subject'] = $smarty->getConfigVariable('interviewInvitationEmailSubject');

        if (is_null($r['user'])) {
            // create a new user
            self::$log->info('Could not find user, this must be a new email, registering: ' . $r['usernameOrEmail']);

            $newUserRequest = new Request($r);
            $newUserRequest['email'] = $r['usernameOrEmail'];
            $newUserRequest['username'] = UserController::makeUsernameFromEmail($r['usernameOrEmail']);
            $newUserRequest['password'] = SecurityTools::randomString(8);
            $newUserRequest['skip_verification_email'] = 1;

            UserController::apiCreate($newUserRequest);

            // Email to new OmegaUp users
            $r['mail_body'] = $smarty->getConfigVariable('interviewInvitationEmailBodyIntro')
                           . '<br>'
                           . ' <a href="https://omegaup.com/api/user/verifyemail/id/' . $newUserRequest['user']->verification_id . '/redirecttointerview/' . $r['contest']->alias . '">'
                           . ' https://omegaup.com/api/user/verifyemail/id/' . $newUserRequest['user']->verification_id . '/redirecttointerview/' . $r['contest']->alias . '</a>'
                           . '<br>';

            $r['mail_body'] .= $smarty->getConfigVariable('interviewUseTheFollowingLoginInfoEmail')
                            . '<br>'
                            . $smarty->getConfigVariable('profileUsername')
                            . ' : '
                            . $newUserRequest['username']
                            . '<br>'
                            . $smarty->getConfigVariable('loginPassword')
                            . ' : '
                            . $newUserRequest['password']
                            . '<br>';

            $r['user'] = $newUserRequest['user'];
        } else {
            // Email to current OmegaUp user
            $r['mail_body'] = $smarty->getConfigVariable('interviewInvitationEmailBodyIntro')
                           . ' <a href="https://omegaup.com/interview/' . $r['contest']->alias . '/arena">'
                           . ' https://omegaup.com/interview/' . $r['contest']->alias . '/arena</a>';
        }

        if (is_null($r['user'])) {
            throw new NotFoundException('userOrMailNotFound');
        }

        // Only director is allowed to add people to interview
        if (!Authorization::isContestAdmin($r['current_user_id'], $r['contest'])) {
            throw new ForbiddenAccessException();
        }

        // add the user to the interview (contest)
        $contestUser = new ContestsUsers();
        $contestUser->contest_id = $r['contest']->contest_id;
        $contestUser->user_id = $r['user']->user_id;
        $contestUser->access_time = '0000-00-00 00:00:00';
        $contestUser->score = '0';
        $contestUser->time = '0';

        try {
            ContestsUsersDAO::save($contestUser);
        } catch (Exception $e) {
            // Operation failed in the data layer
            self::$log->error('Failed to create new ContestUser: ' . $e->getMessage());
            throw new InvalidDatabaseOperationException($e);
        }

        try {
            $r['email'] = EmailsDAO::getByPK($r['user']->main_email_id);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        UserController::sendEmail($r);

        self::$log->info('Added ' . $r['username'] . ' to interview.');

        return true;
    }

    public static function apiDetails(Request $r) {
        self::authenticateRequest($r);

        $thisResult = array();

        $backingContest = ContestsDAO::getByAlias($r['interview_alias']);
        if (is_null($backingContest)) {
            throw new NotFoundException('interviewNotFound');
        }

        // Only proceed if this is indeed an interview
        if (!InterviewsDAO::IsContestInterview($backingContest)) {
            throw new NotFoundException('interviewNotFound');
        }

        // Only admins can view interview details
        if (!Authorization::isContestAdmin($r['current_user_id'], $backingContest)) {
            throw new ForbiddenAccessException();
        }

        $thisResult['description'] = $backingContest->description;
        $thisResult['contest_alias'] = $backingContest->alias;

        $candidatesQuery = new ContestsUsers();
        $candidatesQuery->contest_id = $backingContest->contest_id;

        try {
            $db_results = ContestsUsersDAO::search($candidatesQuery);
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }

        $users = array();

        // Add all users to an array
        foreach ($db_results as $result) {
            // @TODO: Slow queries ahead
            $user_id = $result->user_id;
            $user = UsersDAO::getByPK($user_id);

            try {
                $email = EmailsDAO::getByPK($user->main_email_id);
            } catch (Exception $e) {
                throw new InvalidDatabaseOperationException($e);
            }

            $userOpenedContest = UserController::userOpenedContest($backingContest->contest_id, $user_id);
            $users[] = array(
                        'user_id' => $user_id,
                        'username' => $user->username,
                        'access_time' => $result->access_time,
                        'email' => $email->email,
                        'opened_interview' => $userOpenedContest,
                        'country' => $user->country_id);
        }

        $thisResult['users'] = $users;

        return $thisResult;
    }

    public static function apiList(Request $r) {
        self::authenticateRequest($r);

        $interviews = null;

        try {
            $interviews = InterviewsDAO::getMyInterviews($r['current_user_id']);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        $response['results'] = $interviews;

        return $response;
    }

    public static function showIntro(Request $r) {
        return ContestController::showContestIntro($r);
    }
}
