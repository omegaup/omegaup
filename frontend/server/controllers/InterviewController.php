<?php

class InterviewController extends Controller {
    private static function validateCreateOrUpdate(\OmegaUp\Request $r, $is_update = false) {
        $is_required = !$is_update;

        // Only site-admins and interviewers can create interviews for now
        if (!Authorization::isSystemAdmin($r->identity) &&
            !UsersDAO::IsUserInterviewer($r->user->user_id)) {
            throw new ForbiddenAccessException();
        }

        Validators::validateStringNonEmpty($r['title'], 'title', $is_required);
        Validators::validateStringNonEmpty($r['description'], 'description', false);
        $r->ensureInt('duration', 60, 60 * 5, false);
        Validators::validateValidAlias($r['alias'], 'alias', $is_required);
    }

    public static function apiCreate(\OmegaUp\Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        self::authenticateRequest($r);

        self::validateCreateOrUpdate($r, false);

        $acl = new \OmegaUp\DAO\VO\ACLs([
            'owner_id' => $r->user->user_id,
        ]);
        $interview = new \OmegaUp\DAO\VO\Interviews([
            'alias' => $r['alias'],
            'title' => $r['title'],
            'description' => array_key_exists('description', $r) ? $r['description'] : $r['title'],
            'window_length' => $r['duration'],
        ]);

        try {
            \OmegaUp\DAO\DAO::transBegin();

            ACLsDAO::create($acl);
            $interview->acl_id = $acl->acl_id;

            $problemset = new \OmegaUp\DAO\VO\Problemsets([
                'acl_id' => $acl->acl_id,
                'type' => 'Interview',
                'scoreboard_url' => SecurityTools::randomString(30),
                'scoreboard_url_admin' => SecurityTools::randomString(30),
            ]);
            ProblemsetsDAO::create($problemset);
            $interview->problemset_id = $problemset->problemset_id;
            InterviewsDAO::create($interview);

            // Update interview_id in problemset object
            $problemset->interview_id = $interview->interview_id;
            ProblemsetsDAO::update($problemset);

            \OmegaUp\DAO\DAO::transEnd();
        } catch (Exception $e) {
            // Operation failed in the data layer, rollback transaction
            \OmegaUp\DAO\DAO::transRollback();

            if (\OmegaUp\DAO\DAO::isDuplicateEntryException($e)) {
                throw new DuplicatedEntryInDatabaseException('aliasInUse', $e);
            }
            throw $e;
        }

        self::$log->info('Created new interview ' . $r['alias']);

        return ['status' => 'ok'];
    }

    public static function apiAddUsers(\OmegaUp\Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        // Authenticate logged user
        self::authenticateRequest($r);

        Validators::validateStringNonEmpty($r['usernameOrEmailsCSV'], 'usernameOrEmailsCSV', true);
        $usersToAdd = explode(',', $r['usernameOrEmailsCSV']);

        foreach ($usersToAdd as $addThisUser) {
            $requestToInternal = new \OmegaUp\Request($r);
            $requestToInternal['usernameOrEmail'] = $addThisUser;
            $requestToInternal->user = $r->user;
            $requestToInternal->identity = $r->identity;

            self::addUserInternal($requestToInternal);
        }

        return  ['status' => 'ok'];
    }

    private static function addUserInternal($r) {
        Validators::validateStringNonEmpty($r['interview_alias'], 'interview_alias');
        Validators::validateStringNonEmpty($r['usernameOrEmail'], 'usernameOrEmail');

        // Does the interview exist ?
        $r['interview'] = InterviewsDAO::getByAlias($r['interview_alias']);
        if (is_null($r['interview'])) {
            throw new \OmegaUp\Exceptions\NotFoundException('interviewNotFound');
        }

        // Does the user exist ?
        try {
            $r['user'] = null;
            $r['user'] = UserController::resolveUser($r['usernameOrEmail']);
        } catch (\OmegaUp\Exceptions\NotFoundException $e) {
            // this is fine, we'll create an account for this user
        }

        $subject = \OmegaUp\Translations::getInstance()->get('interviewInvitationEmailSubject');

        if (is_null($r['user'])) {
            // create a new user
            self::$log->info('Could not find user, this must be a new email, registering: ' . $r['usernameOrEmail']);

            $newUserRequest = new \OmegaUp\Request($r);
            $newUserRequest['email'] = $r['usernameOrEmail'];
            $newUserRequest['username'] = UserController::makeUsernameFromEmail($r['usernameOrEmail']);
            $newUserRequest['password'] = SecurityTools::randomString(8);

            UserController::apiCreate($newUserRequest);

            // Email to new OmegaUp users
            $body = \OmegaUp\Translations::getInstance()->get('interviewInvitationEmailBodyIntro')
                           . '<br>'
                           . ' <a href="https://omegaup.com/api/user/verifyemail/id/' . $newUserRequest['user']->verification_id . '/redirecttointerview/' . $r['interview']->alias . '">'
                           . ' https://omegaup.com/api/user/verifyemail/id/' . $newUserRequest['user']->verification_id . '/redirecttointerview/' . $r['interview']->alias . '</a>'
                           . '<br>';

            $body .= \OmegaUp\Translations::getInstance()->get('interviewEmailDraft')
                            . '<br>'
                            . \OmegaUp\Translations::getInstance()->get('profileUsername')
                            . ' : '
                            . $newUserRequest['username']
                            . '<br>'
                            . \OmegaUp\Translations::getInstance()->get('loginPassword')
                            . ' : '
                            . $newUserRequest['password']
                            . '<br>';

            $r['user'] = $newUserRequest['user'];
        } else {
            // Email to current OmegaUp user
            $body = \OmegaUp\Translations::getInstance()->get('interviewInvitationEmailBodyIntro')
                           . ' <a href="https://omegaup.com/interview/' . $r['interview']->alias . '/arena">'
                           . ' https://omegaup.com/interview/' . $r['interview']->alias . '/arena</a>';
        }

        if (is_null($r['user'])) {
            throw new \OmegaUp\Exceptions\NotFoundException('userOrMailNotFound');
        }

        // Only director is allowed to add people to interview
        if (is_null($r->identity)
            || !Authorization::isInterviewAdmin($r->identity, $r['interview'])
        ) {
            throw new ForbiddenAccessException();
        }

        // add the user to the interview
        ProblemsetIdentitiesDAO::create(new \OmegaUp\DAO\VO\ProblemsetIdentities([
            'problemset_id' => $r['interview']->problemset_id,
            'identity_id' => $r['user']->main_identity_id,
            'access_time' => null,
            'score' => '0',
            'time' => '0',
        ]));
        $email = EmailsDAO::getByPK($r['user']->main_email_id);
        if (is_null($email)) {
            throw new \OmegaUp\Exceptions\NotFoundException('userOrMailNotFound');
        }

        include_once 'libs/Email.php';
        Email::sendEmail($email, $subject, $body);

        self::$log->info('Added ' . $r['username'] . ' to interview.');

        return true;
    }

    public static function apiDetails(\OmegaUp\Request $r) {
        self::authenticateRequest($r);

        $interview = InterviewsDAO::getByAlias($r['interview_alias']);
        if (is_null($interview)) {
            return [
                'exists' => false,
                'status' => 'ok',
            ];
        }

        // Only admins can view interview details
        if (!Authorization::isInterviewAdmin($r->identity, $interview)) {
            throw new ForbiddenAccessException();
        }

        $problemsetIdentities = ProblemsetIdentitiesDAO::getIdentitiesByProblemset($interview->problemset_id);

        $users = [];

        // Add all users to an array
        foreach ($problemsetIdentities as $identity) {
            $users[] = [
                        'user_id' => $identity['user_id'],
                        'username' => $identity['username'],
                        'access_time' => $identity['access_time'],
                        'email' => $identity['email'],
                        'opened_interview' => !is_null($identity['access_time']),
                        'country' => $identity['country_id'],
                    ];
        }

        return [
            'description' => $interview->description,
            'contest_alias' => $interview->alias,
            'problemset_id' => $interview->problemset_id,
            'users' => $users,
            'exists' => true,
            'status' => 'ok',
        ];
    }

    public static function apiList(\OmegaUp\Request $r) {
        self::authenticateRequest($r);

        $interviews = null;

        return [
            'status' => 'ok',
            'result' => InterviewsDAO::getMyInterviews($r->user->user_id),
        ];
    }

    public static function showIntro(\OmegaUp\Request $r) {
        $contest = ContestController::validateContest($r['contest_alias'] ?? '');
        // TODO: Arreglar esto para que Problemsets se encargue de obtener
        //       la info correcta
        return ContestController::shouldShowIntro($r, $contest);
    }
}
