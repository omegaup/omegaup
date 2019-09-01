<?php

class InterviewController extends \OmegaUp\Controllers\Controller {
    private static function validateCreateOrUpdate(\OmegaUp\Request $r, $is_update = false) {
        $is_required = !$is_update;

        // Only site-admins and interviewers can create interviews for now
        if (!\OmegaUp\Authorization::isSystemAdmin($r->identity) &&
            !\OmegaUp\DAO\Users::IsUserInterviewer($r->user->user_id)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        \OmegaUp\Validators::validateOptionalStringNonEmpty($r['title'], 'title', $is_required);
        \OmegaUp\Validators::validateOptionalStringNonEmpty($r['description'], 'description');
        $r->ensureInt('duration', 60, 60 * 5, false);
        \OmegaUp\Validators::validateValidAlias($r['alias'], 'alias', $is_required);
    }

    public static function apiCreate(\OmegaUp\Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
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

            \OmegaUp\DAO\ACLs::create($acl);
            $interview->acl_id = $acl->acl_id;

            $problemset = new \OmegaUp\DAO\VO\Problemsets([
                'acl_id' => $acl->acl_id,
                'type' => 'Interview',
                'scoreboard_url' => \OmegaUp\SecurityTools::randomString(30),
                'scoreboard_url_admin' => \OmegaUp\SecurityTools::randomString(30),
            ]);
            \OmegaUp\DAO\Problemsets::create($problemset);
            $interview->problemset_id = $problemset->problemset_id;
            \OmegaUp\DAO\Interviews::create($interview);

            // Update interview_id in problemset object
            $problemset->interview_id = $interview->interview_id;
            \OmegaUp\DAO\Problemsets::update($problemset);

            \OmegaUp\DAO\DAO::transEnd();
        } catch (Exception $e) {
            // Operation failed in the data layer, rollback transaction
            \OmegaUp\DAO\DAO::transRollback();

            if (\OmegaUp\DAO\DAO::isDuplicateEntryException($e)) {
                throw new \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException('aliasInUse', $e);
            }
            throw $e;
        }

        self::$log->info('Created new interview ' . $r['alias']);

        return ['status' => 'ok'];
    }

    public static function apiAddUsers(\OmegaUp\Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        // Authenticate logged user
        self::authenticateRequest($r);

        \OmegaUp\Validators::validateStringNonEmpty($r['usernameOrEmailsCSV'], 'usernameOrEmailsCSV');
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
        \OmegaUp\Validators::validateStringNonEmpty($r['interview_alias'], 'interview_alias');
        \OmegaUp\Validators::validateStringNonEmpty($r['usernameOrEmail'], 'usernameOrEmail');

        // Does the interview exist ?
        $r['interview'] = \OmegaUp\DAO\Interviews::getByAlias($r['interview_alias']);
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

        $subject = \OmegaUp\Translations::getInstance()->get('interviewInvitationEmailSubject')
            ?: 'interviewInvitationEmailSubject';

        if (is_null($r['user'])) {
            // create a new user
            self::$log->info('Could not find user, this must be a new email, registering: ' . $r['usernameOrEmail']);

            $newUserRequest = new \OmegaUp\Request($r);
            $newUserRequest['email'] = $r['usernameOrEmail'];
            $newUserRequest['username'] = UserController::makeUsernameFromEmail($r['usernameOrEmail']);
            $newUserRequest['password'] = \OmegaUp\SecurityTools::randomString(8);

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
            || !\OmegaUp\Authorization::isInterviewAdmin($r->identity, $r['interview'])
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        // add the user to the interview
        \OmegaUp\DAO\ProblemsetIdentities::create(new \OmegaUp\DAO\VO\ProblemsetIdentities([
            'problemset_id' => $r['interview']->problemset_id,
            'identity_id' => $r['user']->main_identity_id,
            'access_time' => null,
            'score' => '0',
            'time' => '0',
        ]));
        $email = \OmegaUp\DAO\Emails::getByPK($r['user']->main_email_id);
        if (is_null($email) || is_null($email->email)) {
            throw new \OmegaUp\Exceptions\NotFoundException('userOrMailNotFound');
        }
        \OmegaUp\Email::sendEmail([$email->email], $subject, $body);

        self::$log->info('Added ' . $r['username'] . ' to interview.');

        return true;
    }

    public static function apiDetails(\OmegaUp\Request $r) {
        self::authenticateRequest($r);

        $interview = \OmegaUp\DAO\Interviews::getByAlias($r['interview_alias']);
        if (is_null($interview)) {
            return [
                'exists' => false,
                'status' => 'ok',
            ];
        }

        // Only admins can view interview details
        if (!\OmegaUp\Authorization::isInterviewAdmin($r->identity, $interview)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        $problemsetIdentities = \OmegaUp\DAO\ProblemsetIdentities::getIdentitiesByProblemset($interview->problemset_id);

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
            'result' => \OmegaUp\DAO\Interviews::getMyInterviews($r->user->user_id),
        ];
    }

    public static function showIntro(\OmegaUp\Request $r) {
        $contest = ContestController::validateContest($r['contest_alias'] ?? '');
        // TODO: Arreglar esto para que Problemsets se encargue de obtener
        //       la info correcta
        return ContestController::shouldShowIntro($r, $contest);
    }
}
