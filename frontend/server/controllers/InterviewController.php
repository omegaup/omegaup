<?php

class InterviewController extends Controller {
    private static function validateCreateOrUpdate(Request $r, $is_update = false) {
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

        $problemset = new Problemsets();
        $acl = new ACLs(array(
            'owner_id' => $r['current_user']->user_id,
        ));
        $interview = new Interviews(array(
            'alias' => $r['alias'],
            'title' => $r['title'],
            'description' => array_key_exists('description', $r) ? $r['description'] : $r['title'],
            'window_length' => $r['duration'],
        ));

        try {
            InterviewsDAO::transBegin();

            ACLsDAO::save($acl);
            $interview->acl_id = $acl->acl_id;
            ProblemsetsDAO::save($problemset);
            $interview->problemset_id = $problemset->problemset_id;
            InterviewsDAO::save($interview);

            InterviewsDAO::transEnd();
        } catch (Exception $e) {
            // Operation failed in the data layer, rollback transaction
            InterviewsDAO::transRollback();

            // Alias may be duplicated, 1062 error indicates that
            if (strpos($e->getMessage(), '1062') !== false) {
                throw new DuplicatedEntryInDatabaseException('aliasInUse', $e);
            } else {
                throw new InvalidDatabaseOperationException($e);
            }
        }

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
            $r['interview'] = InterviewsDAO::getByAlias($r['interview_alias']);
        } catch (Exception $e) {
            // Operation failed in the data layer
            throw new InvalidDatabaseOperationException($e);
        }

        if (is_null($r['interview'])) {
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
                           . ' <a href="https://omegaup.com/api/user/verifyemail/id/' . $newUserRequest['user']->verification_id . '/redirecttointerview/' . $r['interview']->alias . '">'
                           . ' https://omegaup.com/api/user/verifyemail/id/' . $newUserRequest['user']->verification_id . '/redirecttointerview/' . $r['interview']->alias . '</a>'
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
                           . ' <a href="https://omegaup.com/interview/' . $r['interview']->alias . '/arena">'
                           . ' https://omegaup.com/interview/' . $r['interview']->alias . '/arena</a>';
        }

        if (is_null($r['user'])) {
            throw new NotFoundException('userOrMailNotFound');
        }

        // Only director is allowed to add people to interview
        if (!Authorization::isInterviewAdmin($r['current_user_id'], $r['interview'])) {
            throw new ForbiddenAccessException();
        }

        // add the user to the interview
        try {
            ProblemsetUsersDAO::save(new ProblemsetUsers(array(
                'problemset_id' => $r['interview']->problemset_id,
                'user_id' => $r['user']->user_id,
                'access_time' => '0000-00-00 00:00:00',
                'score' => '0',
                'time' => '0',
            )));
        } catch (Exception $e) {
            // Operation failed in the data layer
            self::$log->error('Failed to create new ProblemsetUser: ' . $e->getMessage());
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

        $interview = InterviewsDAO::getByAlias($r['interview_alias']);
        if (is_null($interview)) {
            throw new NotFoundException('interviewNotFound');
        }

        // Only admins can view interview details
        if (!Authorization::isInterviewAdmin($r['current_user_id'], $interview)) {
            throw new ForbiddenAccessException();
        }

        $thisResult['description'] = $interview->description;
        $thisResult['contest_alias'] = $interview->alias;

        try {
            $db_results = ProblemsetUsersDAO::search(new ProblemsetUsers(array(
                'problemset_id' => $interview->problemset_id,
            )));
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

            $problemsetOpened = UserController::userOpenedProblemset($interview->problemset_id, $user_id);
            $users[] = array(
                        'user_id' => $user_id,
                        'username' => $user->username,
                        'access_time' => $result->access_time,
                        'email' => $email->email,
                        'opened_interview' => $problemsetOpened,
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
