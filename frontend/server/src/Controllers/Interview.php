<?php

 namespace OmegaUp\Controllers;

class Interview extends \OmegaUp\Controllers\Controller {
    /**
     * @return array{status: string}
     *
     * @omegaup-request-param null|string $alias
     * @omegaup-request-param null|string $description
     * @omegaup-request-param int $duration
     * @omegaup-request-param string $title
     */
    public static function apiCreate(\OmegaUp\Request $r): array {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();

        $r->ensureMainUserIdentity();

        // Only site-admins and interviewers can create interviews for now
        if (
            !\OmegaUp\Authorization::isSystemAdmin($r->identity) &&
            !\OmegaUp\DAO\Users::IsUserInterviewer($r->user->user_id)
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        $interviewAlias = $r->ensureString(
            'alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['title'],
            'title'
        );
        \OmegaUp\Validators::validateOptionalStringNonEmpty(
            $r['description'],
            'description'
        );
        $r->ensureOptionalInt('duration', 60, 60 * 5);

        $acl = new \OmegaUp\DAO\VO\ACLs([
            'owner_id' => $r->user->user_id,
        ]);
        $interview = new \OmegaUp\DAO\VO\Interviews([
            'alias' => $interviewAlias,
            'title' => $r['title'],
            'description' => $r['description'] ?? $r['title'],
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
                'scoreboard_url_admin' => \OmegaUp\SecurityTools::randomString(
                    30
                ),
            ]);
            \OmegaUp\DAO\Problemsets::create($problemset);
            $interview->problemset_id = $problemset->problemset_id;
            \OmegaUp\DAO\Interviews::create($interview);

            // Update interview_id in problemset object
            $problemset->interview_id = $interview->interview_id;
            \OmegaUp\DAO\Problemsets::update($problemset);

            \OmegaUp\DAO\DAO::transEnd();
        } catch (\Exception $e) {
            // Operation failed in the data layer, rollback transaction
            \OmegaUp\DAO\DAO::transRollback();

            if (\OmegaUp\DAO\DAO::isDuplicateEntryException($e)) {
                throw new \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException(
                    'aliasInUse',
                    $e
                );
            }
            throw $e;
        }

        self::$log->info("Created new interview {$r['alias']}");

        return [
            'status' => 'ok',
        ];
    }

    /**
     * @return array{status: string}
     *
     * @omegaup-request-param string $interview_alias
     * @omegaup-request-param string $usernameOrEmailsCSV
     */
    public static function apiAddUsers(\OmegaUp\Request $r): array {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();

        // Authenticate logged user
        $r->ensureIdentity();

        $interviewAlias = $r->ensureString(
            'interview_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $interview = \OmegaUp\DAO\Interviews::getByAlias($interviewAlias);
        if (is_null($interview)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'interviewNotFound'
            );
        }

        // Only director is allowed to add people to interview
        if (
            !\OmegaUp\Authorization::isInterviewAdmin(
                $r->identity,
                $interview
            )
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        \OmegaUp\Validators::validateStringNonEmpty(
            $r['usernameOrEmailsCSV'],
            'usernameOrEmailsCSV'
        );
        foreach (explode(',', $r['usernameOrEmailsCSV']) as $usernameOrEmail) {
            self::addUserInternal($usernameOrEmail, $interview);
        }

        return  [
            'status' => 'ok',
        ];
    }

    private static function addUserInternal(
        string $usernameOrEmail,
        \OmegaUp\DAO\VO\Interviews $interview
    ): void {
        // Does the user exist ?
        /** @var ?\OmegaUp\DAO\VO\Users */
        $user = null;
        try {
            $user = \OmegaUp\Controllers\User::resolveUser($usernameOrEmail);
        } catch (\OmegaUp\Exceptions\NotFoundException $e) {
            // this is fine, we'll create an account for this user
        }

        $subject = \OmegaUp\Translations::getInstance()->get(
            'interviewInvitationEmailSubject'
        );

        if (is_null($user)) {
            // create a new user
            self::$log->info(
                "Could not find user, this must be a new email, registering: {$usernameOrEmail}"
            );

            $username = \OmegaUp\Controllers\User::makeUsernameFromEmail(
                $usernameOrEmail
            );
            $password = \OmegaUp\SecurityTools::randomString(8);

            \OmegaUp\Controllers\User::createUser(
                new \OmegaUp\CreateUserParams([
                    'email' => $usernameOrEmail,
                    'username' => $username,
                    'password' => $password,
                ]),
                /*ignorePassword=*/false,
                /*forceVerification=*/false
            );
            $user = \OmegaUp\DAO\Users::findByUsername($username);
            if (is_null($user)) {
                throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
            }

            // Email to new OmegaUp users
            $body = \OmegaUp\Translations::getInstance()->get(
                'interviewInvitationEmailBodyIntro'
            )  . '<br>'
               . " <a href=\"https://omegaup.com/user/verifyemail/{$user->verification_id}/redirecttointerview/{$interview->alias}/\">"
               . " https://omegaup.com/user/verifyemail/{$user->verification_id}/redirecttointerview/{$interview->alias}/</a>"
               . '<br>';

            $body .= \OmegaUp\Translations::getInstance()->get(
                'interviewEmailDraft'
            )   . '<br>'
                . \OmegaUp\Translations::getInstance()->get(
                    'profileUsername'
                )
                . " : {$username}<br>"
                . \OmegaUp\Translations::getInstance()->get(
                    'loginPassword'
                )
                . " : {$password}<br>";
        } else {
            // Email to current OmegaUp user
            $body = \OmegaUp\Translations::getInstance()->get(
                'interviewInvitationEmailBodyIntro'
            )  . " <a href=\"https://omegaup.com/interview/{$interview->alias}/arena/\">"
               . "https://omegaup.com/interview/{$interview->alias}/arena/</a>";
        }

        if (is_null($user->main_email_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'userOrMailNotFound'
            );
        }

        // add the user to the interview
        \OmegaUp\DAO\ProblemsetIdentities::create(new \OmegaUp\DAO\VO\ProblemsetIdentities([
            'problemset_id' => $interview->problemset_id,
            'identity_id' => $user->main_identity_id,
            'access_time' => null,
            'score' => '0',
            'time' => '0',
        ]));
        $email = \OmegaUp\DAO\Emails::getByPK($user->main_email_id);
        if (is_null($email) || is_null($email->email)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'userOrMailNotFound'
            );
        }
        \OmegaUp\Email::sendEmail([$email->email], $subject, $body);

        self::$log->info("Added {$usernameOrEmail} to interview.");
    }

    /**
     * @omegaup-request-param string $interview_alias
     *
     * @return array{description: null|string, contest_alias: null|string, problemset_id: int|null, users: list<array{user_id: int|null, username: string, access_time: \OmegaUp\Timestamp|null, email: null|string, opened_interview: bool, country: null|string}>}
     */
    public static function apiDetails(\OmegaUp\Request $r): array {
        $r->ensureIdentity();
        $interviewAlias = $r->ensureString(
            'interview_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );

        $interview = \OmegaUp\DAO\Interviews::getByAlias($interviewAlias);
        if (is_null($interview)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'interviewNotFound'
            );
        }

        // Only admins can view interview details
        if (
            !\OmegaUp\Authorization::isInterviewAdmin(
                $r->identity,
                $interview
            )
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        $problemsetIdentities = \OmegaUp\DAO\ProblemsetIdentities::getIdentitiesByProblemset(
            intval($interview->problemset_id)
        );

        $users = [];

        // Add all users to an array
        foreach ($problemsetIdentities as $identity) {
            $users[] = [
                'user_id' => $identity['user_id'],
                'username' => $identity['username'],
                'access_time' => $identity['access_time'],
                'email' => $identity['email'],
                'opened_interview' => !is_null(
                    $identity['access_time']
                ),
                'country' => $identity['country_id'],
            ];
        }

        return [
            'description' => $interview->description,
            'contest_alias' => $interview->alias,
            'problemset_id' => $interview->problemset_id,
            'users' => $users,
        ];
    }

    /**
     * @return array{result: list<array{acl_id: int, alias: string, description: string, interview_id: int, problemset_id: int, title: string, window_length: int}>}
     */
    public static function apiList(\OmegaUp\Request $r): array {
        $r->ensureMainUserIdentity();

        return [
            'result' => \OmegaUp\DAO\Interviews::getMyInterviews(
                $r->user->user_id
            ),
        ];
    }

    /**
     * @omegaup-request-param string $contest_alias
     */
    public static function showIntro(\OmegaUp\Request $r): bool {
        $contestAlias = $r->ensureString(
            'contest_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        $contest = \OmegaUp\Controllers\Contest::validateContest($contestAlias);
        try {
            $r->ensureIdentity();
        } catch (\OmegaUp\Exceptions\UnauthorizedException $e) {
            if ($contest->admission_mode === 'private') {
                throw $e;
            }
            // Request can proceed unauthenticated.
        }
        // TODO: Arreglar esto para que Problemsets se encargue de obtener
        //       la info correcta
        return \OmegaUp\Controllers\Contest::shouldShowIntro(
            $r->identity,
            $contest
        );
    }
}
