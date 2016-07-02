<?php

class InterviewController extends Controller {
    private static function validateCreateOrUpdate(Request $r, $is_update = false) {
        // Interview specific validations. Everything else is validated in ContestController::apiCreate
        $is_required = !$is_update;

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

    private static function userFromEmail($email) {
        $newUsername = substr($email, 0, strpos($email, '@'));
        $newUsername = str_replace('-', '_', $newUsername);
        return $newUsername . time();
    }

    private static function addUserInternal($r) {
        // Does the user exist ?
        try {
            $r['user'] = UserController::resolveUser($r['usernameOrEmail']);
        } catch (NotFoundException $e) {
            // this is fine
        }

        if (is_null($r['user'])) {
            // user does not exist, create a new user
            self::$log->info('Could not find user, this must be a new email, register: ' . $r['usernameOrEmail']);

            $newUserRequest = $r;
            $newUserRequest['email'] = $r['usernameOrEmail'];
            $newUserRequest['username'] = self::userFromEmail($r['usernameOrEmail']);
            $newUserRequest['password'] = self::randomString(8);
            $newUser = UserController::apiCreate($newUserRequest);

            $r['user'] = $newUserRequest['username'];
        }

        self::validateAddUser($r);

        // add the user to the interview (contest)
        $contestUser = new ContestsUsers();
        $contestUser->setContestId($r['contest']->getContestId());
        $contestUser->setUserId($r['user']->getUserId());
        $contestUser->setAccessTime('0000-00-00 00:00:00');
        $contestUser->setScore('0');
        $contestUser->setTime('0');

        // Save the contest to the DB
        try {
            ContestsUsersDAO::save($contestUser);
        } catch (Exception $e) {
            // Operation failed in the data layer
            self::$log->error('Failed to create new ContestUser: ' . $e->getMessage());
            throw new InvalidDatabaseOperationException($e);
        }

        self::$log->info('Added ' . $newUserRequest['username'] . ' to interview.');

        return true;
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

    private static function userOpenedContest($contest_id, $user_id) {
        // You already started the contest.
        $contestOpened = ContestsUsersDAO::getByPK(
            $user_id,
            $contest_id
        );

        if (!is_null($contestOpened) && $contestOpened->access_time != '0000-00-00 00:00:00') {
            return true;
        }

        return false;
    }

    public static function apiDetails(Request $r) {
        try {
            self::authenticateRequest($r);
        } catch (UnauthorizedException $e) {
            // Do nothing.
        }

        $thisResult = array();

        $backingContest = ContestsDAO::getByAlias($r['interview_alias']);
        if (is_null($backingContest)) {
            throw new NotFoundException();
        }

        $thisResult['description'] = $backingContest->description;
        $thisResult['contest_alias'] = $backingContest->alias;

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
            // @TODO: Slow queries ahead
            $user_id = $result->getUserId();
            $user = UsersDAO::getByPK($user_id);

            try {
                $email = EmailsDAO::getByPK($user->getMainEmailId());
            } catch (Exception $e) {
                throw new InvalidDatabaseOperationException($e);
            }

            $userOpenedContest = self::userOpenedContest($backingContest->contest_id, $user_id);
            $users[] = array(
                        'user_id' => $user_id,
                        'username' => $user->getUsername(),
                        'access_time' => $result->access_time,
                        'email' => $email->getEmail(),
                        'opened_interview' => $userOpenedContest,
                        'country' => $user->getCountryId());
        }

        $thisResult['users'] = $users;

        return $thisResult;
    }

    private static function sendInvitationEmail(Request $r) {
        if (!OMEGAUP_EMAIL_SEND_EMAILS) {
            return;
        }

        try {
            $email = EmailsDAO::getByPK($r['user']->getMainEmailId());
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        self::$log->info('Sending email to user.');
        if (self::$sendEmailOnVerify) {
            $mail = new PHPMailer();
            $mail->IsSMTP();
            $mail->Host = OMEGAUP_EMAIL_SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Password = OMEGAUP_EMAIL_SMTP_PASSWORD;
            $mail->From = OMEGAUP_EMAIL_SMTP_FROM;
            $mail->Port = 465;
            $mail->SMTPSecure = 'ssl';
            $mail->Username = OMEGAUP_EMAIL_SMTP_FROM;

            $mail->FromName = OMEGAUP_EMAIL_SMTP_FROM;
            $mail->AddAddress($email->getEmail());
            $mail->isHTML(true);
            $mail->Subject = 'Bienvenido a Omegaup!';
            $mail->Body = 'Bienvenido a Omegaup! Por favor ingresa a la siguiente dirección para hacer login y verificar tu email: <a href="https://omegaup.com/api/user/verifyemail/id/' . $r['user']->getVerificationId() . '"> https://omegaup.com/api/user/verifyemail/id/' . $r['user']->getVerificationId() . '</a>';

            if (!$mail->Send()) {
                self::$log->error('Failed to send mail: ' . $mail->ErrorInfo);
                throw new EmailVerificationSendException();
            }
        }
    }

    public static function apiList(Request $r) {
        self::authenticateRequest($r);

        $interviews = null;

        $current_ses = SessionController::getCurrentSession($r);

        try {
            $interviews = ContestsDAO::getMyInterviews($current_ses['id']);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        $response['results'] = $interviews;

        return $response;
    }

    public static function showContestIntro(Request $r) {
        return ContestController::showContestIntro($r);
    }

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
