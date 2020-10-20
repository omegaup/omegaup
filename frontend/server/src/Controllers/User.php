<?php

namespace OmegaUp\Controllers;

/**
 *  UserController
 *
 * @psalm-type PageItem=array{class: string, label: string, page: int, url?: string}
 * @psalm-type AuthorsRank=array{ranking: list<array{author_ranking: int|null, author_score: float, classname: string, country_id: null|string, name: null|string, username: string}>, total: int}
 * @psalm-type AuthorsRankWithQualityProblems=array{ranking: list<array{author_ranking: int, name: null|string, username: string}>}
 * @psalm-type AuthorRankTablePayload=array{length: int, page: int, ranking: AuthorsRank, pagerItems: list<PageItem>}
 * @psalm-type Badge=array{assignation_time: \OmegaUp\Timestamp|null, badge_alias: string, first_assignation: \OmegaUp\Timestamp|null, owners_count: int, total_users: int}
 * @psalm-type CommonPayload=array{omegaUpLockDown: bool, bootstrap4: bool, inContest: bool, isLoggedIn: bool, isReviewer: bool, gravatarURL51: string, currentUsername: string, userClassname: string, userCountry: string, profileProgress: float, isMainUserIdentity: bool, isAdmin: bool, lockDownImage: string, navbarSection: string}
 * @psalm-type UserRankInfo=array{name: string, problems_solved: int, rank: int, author_ranking: int|null}
 * @psalm-type UserRank=array{rank: list<array{classname: string, country_id: null|string, name: null|string, problems_solved: int, ranking: null|int, score: float, user_id: int, username: string}>, total: int}
 * @psalm-type Problem=array{title: string, alias: string, submissions: int, accepted: int, difficulty: float}
 * @psalm-type UserProfile=array{birth_date: \OmegaUp\Timestamp|null, classname: string, country: string, country_id: null|string, email: null|string, gender: null|string, graduation_date: \OmegaUp\Timestamp|null, gravatar_92: string, hide_problem_tags: bool, is_private: bool, locale: string, name: null|string, preferred_language: null|string, scholar_degree: null|string, school: null|string, school_id: int|null, state: null|string, state_id: null|string, username: null|string, verified: bool}
 * @psalm-type UserListItem=array{label: string, value: string}
 * @psalm-type UserRankTablePayload=array{availableFilters: array{country?: null|string, school?: null|string, state?: null|string}, filter: string, isIndex: false, isLogged: bool, length: int, page: int, ranking: UserRank, pagerItems: list<PageItem>}
 * @psalm-type CoderOfTheMonth=array{category: string, classname: string, coder_of_the_month_id: int, country_id: string, description: null|string, interview_url: null|string, problems_solved: int, ranking: int, school_id: int|null, score: float, selected_by: int|null, time: string, user_id: int, username: string}
 * @psalm-type CoderOfTheMonthList=list<array{username: string, country_id: string, gravatar_32: string, date: string, classname: string}>
 * @psalm-type IndexPayload=array{coderOfTheMonthData: array{all: UserProfile|null, female: UserProfile|null}, currentUserInfo: array{username?: string}, userRank: list<CoderOfTheMonth>, schoolOfTheMonthData: array{country_id: null|string, country: null|string, name: string, school_id: int, state: null|string}|null, schoolRank: list<array{name: string, ranking: int, school_id: int, school_of_the_month_id: int, score: float}>}
 * @psalm-type CoderOfTheMonthPayload=array{codersOfCurrentMonth: CoderOfTheMonthList, codersOfPreviousMonth: CoderOfTheMonthList, candidatesToCoderOfTheMonth: list<array{category: string, classname: string, coder_of_the_month_id: int, country_id: string, description: null|string, interview_url: null|string, problems_solved: int, ranking: int, school_id: int|null, score: float, selected_by: int|null, time: string, username: string}>, isMentor: bool, category: string, options?: array{canChooseCoder: bool, coderIsSelected: bool}}
 * @psalm-type UserProfileInfo=array{birth_date: \OmegaUp\Timestamp|null, classname: string, country: null|string, country_id: null|string, email?: null|string, gender: null|string, graduation_date: \OmegaUp\Timestamp|null|string, gravatar_92: null|string, hide_problem_tags: bool, is_private: bool, locale: null|string, name: null|string, preferred_language: null|string, rankinfo: array{author_ranking: int|null, name: null|string, problems_solved: int|null, rank: int|null}, scholar_degree: null|string, school: null|string, school_id: int|null, state: null|string, state_id: null|string, username: null|string, verified: bool|null, programming_languages: array<string,string>}
 * @psalm-type UserProfileContests=array<string, array{data: array{alias: string, title: string, start_time: \OmegaUp\Timestamp, finish_time: \OmegaUp\Timestamp, last_updated: \OmegaUp\Timestamp}, place: int}>
 * @psalm-type UserProfileStats=array{date: null|string, runs: int, verdict: string}
 * @psalm-type UserListItem=array{label: string, value: string}
 * @psalm-type UserProfileDetailsPayload=array{statusError?: string, profile: UserProfileInfo, contests: UserProfileContests, solvedProblems: list<Problem>, unsolvedProblems: list<Problem>, createdProblems: list<Problem>, stats: list<UserProfileStats>, badges: list<string>, ownedBadges: list<Badge>, programmingLanguages: array<string,string>}
 */
class User extends \OmegaUp\Controllers\Controller {
    /** @var bool */
    public static $sendEmailOnVerify = true;

    /** @var null|string */
    public static $permissionKey = null;

    /** @var \OmegaUp\UrlHelper */
    public static $urlHelper;

    const ALLOWED_SCHOLAR_DEGREES = [
        'none', 'early_childhood', 'pre_primary', 'primary', 'lower_secondary',
        'upper_secondary', 'post_secondary', 'tertiary', 'bachelors', 'master',
        'doctorate',
    ];
    const ALLOWED_GENDER_OPTIONS = [
        'female','male','other','decline',
    ];

    const SENDY_SUCCESS = '1';

    // Languages
    const LANGUAGE_ES = 1;
    const LANGUAGE_EN = 2;
    const LANGUAGE_PT = 3;
    const LANGUAGE_PSEUDO = 4;

    const ALLOWED_CODER_OF_THE_MONTH_CATEGORIES = [
        'all', 'female',
    ];

    /**
     * Entry point for Create a User API
     *
     * @throws \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException
     * @return array{username: string}
     */
    public static function apiCreate(\OmegaUp\Request $r): array {
        /**
         * @psalm-suppress ArgumentTypeCoercion this tries to convert
         * array<string, string> to array{...?: string}, which is okay.
         */
        $createUserParams = new \OmegaUp\CreateUserParams($r->toStringArray());
        self::createUser(
            $createUserParams,
            /*ignorePassword=*/false,
            /*forceVerification=*/false
        );
        return [
            'username' => strval($createUserParams->username),
        ];
    }

    public static function createUser(
        \OmegaUp\CreateUserParams $createUserParams,
        bool $ignorePassword,
        bool $forceVerification
    ): void {
        // Check password
        $hashedPassword = null;
        if (!$ignorePassword) {
            \OmegaUp\SecurityTools::testStrongPassword(
                strval($createUserParams->password)
            );
            $hashedPassword = \OmegaUp\SecurityTools::hashString(
                strval($createUserParams->password)
            );
        }

        // Does username or email already exists?
        $identity = \OmegaUp\DAO\Identities::findByUsername(
            $createUserParams->username
        );
        $identityByEmail = \OmegaUp\DAO\Identities::findByEmail(
            $createUserParams->email
        );

        if (!is_null($identityByEmail)) {
                // Check if the same user had already tried to create this account.
            if (
                !is_null($identityByEmail->password) &&
                !is_null($identity) &&
                $identity->user_id === $identityByEmail->user_id &&
                \OmegaUp\SecurityTools::compareHashedStrings(
                    strval($createUserParams->password),
                    strval($identity->password)
                )
            ) {
                return;
            }
            // Given that the user has already been created, and we
            // have no way of validating if this request was made by
            // the same person, let's just bail out.
            throw new \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException(
                'mailInUse'
            );
        }

        if (!is_null($identity)) {
            throw new \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException(
                'usernameInUse'
            );
        }

        // Prepare DAOs
        $identityData = [
            'username' => $createUserParams->username,
            'password' => $hashedPassword,
        ];
        $userData = [
            'verified' => 0,
            'verification_id' => \OmegaUp\SecurityTools::randomString(50),
            'is_private' => boolval($createUserParams->isPrivate),
        ];
        if (!is_null($createUserParams->name)) {
            $identityData['name'] = $createUserParams->name;
        }
        if (!is_null($createUserParams->gender)) {
            $identityData['gender'] = $createUserParams->gender;
        }
        if (!is_null($createUserParams->facebookUserId)) {
            $userData['facebook_user_id'] = $createUserParams->facebookUserId;
        }
        /** @psalm-suppress TypeDoesNotContainType OMEGAUP_VALIDATE_CAPTCHA may be defined as true in tests. */
        if ($forceVerification) {
            $userData['verified'] = 1;
        } elseif (OMEGAUP_VALIDATE_CAPTCHA) {
            // Validate captcha
            if (empty($createUserParams->recaptcha)) {
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'parameterNotFound',
                    'recaptcha'
                );
            }

            $url = 'https://www.google.com/recaptcha/api/siteverify';
            $data = [
                'secret' => OMEGAUP_RECAPTCHA_SECRET,
                'response' => $createUserParams->recaptcha,
                'remoteip' => (
                    \OmegaUp\Request::getServerVar('REMOTE_ADDR') ?? ''
                ),
            ];

            // use key 'http' even if you send the request to https://...
            $options = [
                'http' => [
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method'  => 'POST',
                    'content' => http_build_query($data),
                ],
            ];
            $context  = stream_context_create($options);
            $result = file_get_contents($url, false, $context);

            if ($result === false) {
                self::$log->error('POST Request to Google Recaptcha failed.');
                throw new \OmegaUp\Exceptions\CaptchaVerificationFailedException();
            }

            /** @var null|mixed */
            $resultAsJson = json_decode($result, /*assoc=*/true);
            if (is_null($resultAsJson)) {
                self::$log->error('Captcha response was not a json');
                self::$log->error("Here is the result: {$result}");
                throw new \OmegaUp\Exceptions\CaptchaVerificationFailedException();
            }

            if (
                !is_array($resultAsJson) ||
                !array_key_exists('success', $resultAsJson) ||
                !boolval($resultAsJson['success'])
            ) {
                self::$log->error('Captcha response said no');
                throw new \OmegaUp\Exceptions\CaptchaVerificationFailedException();
            }
        }

        $user = new \OmegaUp\DAO\VO\Users($userData);
        $identity = new \OmegaUp\DAO\VO\Identities($identityData);

        $email = new \OmegaUp\DAO\VO\Emails([
            'email' => $createUserParams->email,
        ]);

        // Save objects into DB
        try {
            \OmegaUp\DAO\DAO::transBegin();

            \OmegaUp\DAO\Users::create($user);

            $email->user_id = $user->user_id;
            \OmegaUp\DAO\Emails::create($email);
            if (empty($email->email_id)) {
                throw new \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException(
                    'mailInUse'
                );
            }
            $user->main_email_id = $email->email_id;

            $identity->user_id = $user->user_id;
            \OmegaUp\DAO\Identities::create($identity);
            $user->main_identity_id = $identity->identity_id;

            \OmegaUp\DAO\Users::update($user);

            if ($user->verified) {
                self::$log->info(
                    "Identity {$identity->username} created, trusting e-mail"
                );
            } else {
                self::$log->info(
                    "Identity {$identity->username} created, sending verification mail"
                );

                self::sendVerificationEmail($user);
            }

            \OmegaUp\DAO\DAO::transEnd();
        } catch (\Exception $e) {
            \OmegaUp\DAO\DAO::transRollback();
            throw $e;
        }
    }

    /**
     * Registers the created user to Sendy
     */
    private static function registerToSendy(
        \OmegaUp\DAO\VO\Users $user,
        \OmegaUp\DAO\VO\Identities $identity
    ): bool {
        if (!OMEGAUP_EMAIL_SENDY_ENABLE) {
            return false;
        }

        self::$log->info('Adding user to Sendy.');

        if (is_null($user->main_email_id)) {
            return false;
        }

        // Get email
        try {
            $email = \OmegaUp\DAO\Emails::getByPK($user->main_email_id);
            if (is_null($email) || is_null($email->email)) {
                return false;
            }
        } catch (\Exception $e) {
            self::$log->warn('Email lookup failed', $e);
            return false;
        }

        //Subscribe
        $postdata = http_build_query(
            [
                'name' => $identity->username,
                'email' => $email->email,
                'list' => OMEGAUP_EMAIL_SENDY_LIST,
                'boolean' => 'true' /* get a plaintext response, API: https://sendy.co/api */
                ]
        );
        $opts = [
            'http' => [
                'method'  => 'POST',
                'header'  => 'Content-type: application/x-www-form-urlencoded',
                'content' => $postdata]
        ];

        $context  = stream_context_create($opts);
        $result = self::$urlHelper->fetchUrl(
            OMEGAUP_EMAIL_SENDY_SUBSCRIBE_URL,
            $context
        );

        //check result and redirect
        self::$log->info('Sendy response: ' . $result);
        if ($result === \OmegaUp\Controllers\User::SENDY_SUCCESS) {
            self::$log->info('Success adding user to Sendy.');
        } else {
            self::$log->info('Failure adding user to Sendy.');
            return false;
        }

        return true;
    }

    /**
     * Send the mail with verification link to the user in the Request
     *
     * @throws \OmegaUp\Exceptions\EmailVerificationSendException
     */
    private static function sendVerificationEmail(\OmegaUp\DAO\VO\Users $user): void {
        if (is_null($user->main_email_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'userOrMailNotFound'
            );
        }
        $email = \OmegaUp\DAO\Emails::getByPK($user->main_email_id);
        if (is_null($email) || is_null($email->email)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'userOrMailNotFound'
            );
        }

        if (!self::$sendEmailOnVerify) {
            self::$log->info(
                'Not sending email beacause sendEmailOnVerify = FALSE'
            );
            return;
        }

        $subject = \OmegaUp\Translations::getInstance()->get(
            'verificationEmailSubject'
        );
        $body = \OmegaUp\ApiUtils::formatString(
            \OmegaUp\Translations::getInstance()->get('verificationEmailBody'),
            [
                'verification_id' => strval($user->verification_id),
            ]
        );

        \OmegaUp\Email::sendEmail([$email->email], $subject, $body);
    }

    /**
     * Check if email of user in request has been verified
     *
     * @throws \OmegaUp\Exceptions\EmailNotVerifiedException
     */
    public static function checkEmailVerification(
        \OmegaUp\DAO\VO\Users $user,
        \OmegaUp\DAO\VO\Identities $identity
    ): void {
        if ($user->verified != '0') {
            // Already verified, nothing to do.
            return;
        }
        if (!OMEGAUP_FORCE_EMAIL_VERIFICATION) {
            return;
        }
        self::$log->info("User {$identity->username} not verified.");

        if (is_null($user->verification_id)) {
            self::$log->info('User does not have verification id. Generating.');

            try {
                $user->verification_id = \OmegaUp\SecurityTools::randomString(
                    50
                );
                \OmegaUp\DAO\Users::update($user);
            } catch (\Exception $e) {
                self::$log->info("Unable to save verification ID: $e");
            }

            self::sendVerificationEmail($user);
        }

        throw new \OmegaUp\Exceptions\EmailNotVerifiedException();
    }

    /**
     * Exposes API /user/login
     * Expects in request:
     * user
     * password
     *
     * @omegaup-request-param string $password
     * @omegaup-request-param string $usernameOrEmail
     *
     * @param \OmegaUp\Request $r
     *
     * @return array{auth_token: string}
     */
    public static function apiLogin(\OmegaUp\Request $r): array {
        return [
            'auth_token' => \OmegaUp\Controllers\Session::nativeLogin($r),
        ];
    }

    /**
     * Changes the password of a user
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return array{status: string}
     *
     * @omegaup-request-param string $old_password
     * @omegaup-request-param null|string $password
     * @omegaup-request-param mixed $permission_key
     * @omegaup-request-param string $username
     */
    public static function apiChangePassword(\OmegaUp\Request $r): array {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();
        $r->ensureMainUserIdentity();

        $password = $r->ensureOptionalString(
            'password',
            /*$required=*/false,
            fn (string $password) => \OmegaUp\Validators::stringNonEmpty(
                $password
            )
        );
        $hashedPassword = null;
        $user = $r->user;
        $identity = $r->identity;
        if (isset($r['username']) && $r['username'] !== $identity->username) {
            // This is usable only in tests.
            if (
                is_null(self::$permissionKey) ||
                self::$permissionKey != $r['permission_key']
            ) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException();
            }
            \OmegaUp\Validators::validateStringNonEmpty(
                $r['username'],
                'username'
            );

            $user = \OmegaUp\DAO\Users::FindByUsername($r['username']);
            if (is_null($user) || is_null($user->main_identity_id)) {
                throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
            }
            $identity = \OmegaUp\DAO\Identities::getByPK(
                $user->main_identity_id
            );
            if (is_null($identity)) {
                throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
            }

            if (!is_null($password)) {
                \OmegaUp\SecurityTools::testStrongPassword($password);
                $hashedPassword = \OmegaUp\SecurityTools::hashString(
                    $password
                );
            }
        } else {
            if (is_null($user->main_identity_id)) {
                throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
            }
            $identity = \OmegaUp\DAO\Identities::getByPK(
                $user->main_identity_id
            );
            if (is_null($identity)) {
                throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
            }

            if (!is_null($identity->password)) {
                // Check the old password
                \OmegaUp\Validators::validateStringNonEmpty(
                    $r['old_password'],
                    'old_password'
                );

                $old_password_valid = \OmegaUp\SecurityTools::compareHashedStrings(
                    $r['old_password'],
                    $identity->password
                );

                if ($old_password_valid === false) {
                    throw new \OmegaUp\Exceptions\InvalidParameterException(
                        'parameterInvalid',
                        'old_password'
                    );
                }
            }

            if (is_null($password)) {
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'parameterEmpty',
                    'password'
                );
            }
            \OmegaUp\SecurityTools::testStrongPassword($password);
            $hashedPassword = \OmegaUp\SecurityTools::hashString(
                $password
            );
        }

        $identity->password = $hashedPassword;

        \OmegaUp\DAO\Identities::update($identity);

        return ['status' => 'ok'];
    }

    /**
     * Verifies the user given its verification id
     *
     * @param \OmegaUp\Request $r
     *
     * @throws \OmegaUp\Exceptions\ApiException
     * @throws \OmegaUp\Exceptions\NotFoundException
     *
     * @return array{status: 'ok'}
     *
     * @omegaup-request-param string $id
     * @omegaup-request-param null|string $usernameOrEmail
     */
    public static function apiVerifyEmail(\OmegaUp\Request $r): array {
        \OmegaUp\Validators::validateOptionalStringNonEmpty(
            $r['usernameOrEmail'],
            'usernameOrEmail'
        );

        if (isset($r['usernameOrEmail'])) {
            // Admin can override verification by sending username
            $r->ensureIdentity();

            if (!\OmegaUp\Authorization::isSupportTeamMember($r->identity)) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException();
            }

            self::$log->info("Admin verifying user... {$r['usernameOrEmail']}");
            $user = self::resolveUser($r['usernameOrEmail']);
        } else {
            // Normal user verification path
            \OmegaUp\Validators::validateStringNonEmpty($r['id'], 'id');
            $user = \OmegaUp\DAO\Users::getByVerification($r['id']);
        }

        if (is_null($user) || is_null($user->main_identity_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'verificationIdInvalid'
            );
        }
        $identity = \OmegaUp\DAO\Identities::getByPK(
            $user->main_identity_id
        );
        if (is_null($identity)) {
            throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
        }

        $user->verified = true;
        $user->verification_id = null;
        \OmegaUp\DAO\Users::update($user);

        self::$log->info(
            "User verification complete for {$identity->username}"
        );

        // Expire profile cache

        \OmegaUp\Cache::deleteFromCache(
            \OmegaUp\Cache::USER_PROFILE,
            strval($identity->username)
        );

        return ['status' => 'ok'];
    }

    /**
     * Registers to the mailing list all users that have not been added before. Admin only
     *
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return array{users: array<string, bool>}
     */
    public static function apiMailingListBackfill(\OmegaUp\Request $r): array {
        $r->ensureIdentity();

        if (!\OmegaUp\Authorization::isSystemAdmin($r->identity)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        $usersAdded = [];

        $usersMissing = \OmegaUp\DAO\Users::getVerified(
            true, // verified
            false // in_mailing_list
        );

        foreach ($usersMissing as $user) {
            if (is_null($user->main_identity_id)) {
                throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
            }
            $identity = \OmegaUp\DAO\Identities::getByPK(
                $user->main_identity_id
            );
            if (is_null($identity)) {
                throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
            }
            $registered = self::registerToSendy($user, $identity);

            if ($registered) {
                $user->in_mailing_list = true;
                \OmegaUp\DAO\Users::update($user);
            }

            $usersAdded[strval($identity->username)] = $registered;
        }

        return [
            'users' => $usersAdded
        ];
    }

    /**
     * Given a username or a email, returns the user object
     *
     * @param ?string $userOrEmail
     * @return \OmegaUp\DAO\VO\Users
     * @throws \OmegaUp\Exceptions\ApiException
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     */
    public static function resolveUser(?string $userOrEmail): \OmegaUp\DAO\VO\Users {
        \OmegaUp\Validators::validateStringNonEmpty(
            $userOrEmail,
            'usernameOrEmail'
        );
        $user = \OmegaUp\DAO\Users::FindByUsername($userOrEmail);
        if (!is_null($user)) {
            return $user;
        }
        $user = \OmegaUp\DAO\Users::findByEmail($userOrEmail);
        if (!is_null($user)) {
            return $user;
        }
        throw new \OmegaUp\Exceptions\NotFoundException('userOrMailNotFound');
    }

    /**
     * Resets the password of the OMI user and adds the user to the private
     * contest.
     * If the user does not exists, we create him.
     *
     * @param \OmegaUp\Request $r
     * @param string $username
     * @param string $password
     *
     * @return bool
     *
     * @omegaup-request-param string $auth_token
     * @omegaup-request-param mixed $change_password
     * @omegaup-request-param string $id
     * @omegaup-request-param string $old_password
     * @omegaup-request-param null|string $password
     * @omegaup-request-param string $permission_key
     * @omegaup-request-param string $username
     * @omegaup-request-param null|string $usernameOrEmail
     */
    private static function omiPrepareUser(
        \OmegaUp\Request $r,
        $username,
        $password
    ): bool {
        $user = \OmegaUp\DAO\Users::FindByUsername($username);
        if (is_null($user)) {
            self::$log->info('Creating user: ' . $username);
            $createRequest = new \OmegaUp\Request([
                'username' => $username,
                'password' => $password,
                'email' => $username . '@omi.com',
                'permission_key' => $r['permission_key']
            ]);

            \OmegaUp\Controllers\User::$sendEmailOnVerify = false;
            self::apiCreate($createRequest);
            return true;
        } elseif (
            is_null($r['change_password']) ||
            $r['change_password'] !== 'false'
        ) {
            if (!$user->verified) {
                self::apiVerifyEmail(new \OmegaUp\Request([
                    'auth_token' => $r['auth_token'],
                    'usernameOrEmail' => $username
                ]));
            }
            \OmegaUp\Validators::validateStringNonEmpty(
                $r['auth_token'],
                'auth_token'
            );
            \OmegaUp\Validators::validateStringNonEmpty(
                $r['permission_key'],
                'permission_key'
            );

            // Pwd changes are by default unless explictly disabled
            $resetRequest = new \OmegaUp\Request();
            $resetRequest['auth_token'] = $r['auth_token'];
            $resetRequest['username'] = $username;
            $resetRequest['password'] = $password;
            $resetRequest['permission_key'] = $r['permission_key'];
            self::apiChangePassword($resetRequest);
            return true;
        }

        return false;
    }

    /**
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return array<string, string>
     *
     * @omegaup-request-param string $auth_token
     * @omegaup-request-param mixed $change_password
     * @omegaup-request-param string $contest_alias
     * @omegaup-request-param string $contest_type
     * @omegaup-request-param string $id
     * @omegaup-request-param string $old_password
     * @omegaup-request-param null|string $password
     * @omegaup-request-param string $permission_key
     * @omegaup-request-param string $username
     * @omegaup-request-param null|string $usernameOrEmail
     */
    public static function apiGenerateOmiUsers(\OmegaUp\Request $r): array {
        $r->ensureIdentity();
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['contest_type'],
            'contest_type'
        );
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['auth_token'],
            'auth_token'
        );
        $contestAlias = $r->ensureString(
            'contest_alias',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );

        $response = [];

        $is_system_admin = \OmegaUp\Authorization::isSystemAdmin($r->identity);
        if ($r['contest_type'] == 'OMI') {
            if (
                $r->identity->username != 'andreasantillana'
                && !$is_system_admin
            ) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException();
            }

            // Arreglo de estados de MX
            $keys = [
                'OMI2020-AGU' => 4,
                'OMI2020-BCN' => 4,
                'OMI2020-BCS' => 4,
                'OMI2020-CAM' => 4,
                'OMI2020-CHH' => 4,
                'OMI2020-CHP' => 4,
                'OMI2020-CMX' => 4,
                'OMI2020-COA' => 4,
                'OMI2020-COL' => 4,
                'OMI2020-DUR' => 4,
                'OMI2020-GRO' => 4,
                'OMI2020-GUA' => 4,
                'OMI2020-HID' => 4,
                'OMI2020-JAL' => 4,
                'OMI2020-MEX' => 4,
                'OMI2020-MIC' => 4,
                'OMI2020-MOR' => 4,
                'OMI2020-NAY' => 4,
                'OMI2020-NLE' => 4,
                'OMI2020-OAX' => 4,
                'OMI2020-PUE' => 4,
                'OMI2020-QTO' => 4,
                'OMI2020-ROO' => 4,
                'OMI2020-SIN' => 4,
                'OMI2020-SLP' => 4,
                'OMI2020-SON' => 4,
                'OMI2020-TAB' => 4,
                'OMI2020-TAM' => 4,
                'OMI2020-TLA' => 4,
                'OMI2020-VER' => 4,
                'OMI2020-YUC' => 4,
                'OMI2020-ZAC' => 8,
                'OMI2020-INV' => 4,
            ];
        } elseif ($r['contest_type'] == 'OMIP') {
            if (
                $r->identity->username != 'andreasantillana'
                && !$is_system_admin
            ) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException();
            }

            $keys = [
                'OMIP2020-AGU' => 25,
                'OMIP2020-BCN' => 25,
                'OMIP2020-BCS' => 25,
                'OMIP2020-CAM' => 25,
                'OMIP2020-CHH' => 25,
                'OMIP2020-CHP' => 25,
                'OMIP2020-CMX' => 25,
                'OMIP2020-COA' => 25,
                'OMIP2020-COL' => 25,
                'OMIP2020-DUR' => 25,
                'OMIP2020-GRO' => 25,
                'OMIP2020-GUA' => 25,
                'OMIP2020-HID' => 25,
                'OMIP2020-JAL' => 25,
                'OMIP2020-MEX' => 25,
                'OMIP2020-MIC' => 25,
                'OMIP2020-MOR' => 25,
                'OMIP2020-NAY' => 25,
                'OMIP2020-NLE' => 25,
                'OMIP2020-OAX' => 25,
                'OMIP2020-PUE' => 25,
                'OMIP2020-QTO' => 25,
                'OMIP2020-ROO' => 25,
                'OMIP2020-SIN' => 25,
                'OMIP2020-SLP' => 25,
                'OMIP2020-SON' => 25,
                'OMIP2020-TAB' => 25,
                'OMIP2020-TAM' => 25,
                'OMIP2020-TLA' => 25,
                'OMIP2020-VER' => 25,
                'OMIP2020-YUC' => 25,
                'OMIP2020-ZAC' => 25,
            ];
        } elseif ($r['contest_type'] == 'OMIS') {
            if (
                $r->identity->username != 'andreasantillana'
                && !$is_system_admin
            ) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException();
            }

            $keys = [
                'OMIS2020-AGU' => 25,
                'OMIS2020-BCN' => 25,
                'OMIS2020-BCS' => 25,
                'OMIS2020-CAM' => 25,
                'OMIS2020-CHH' => 25,
                'OMIS2020-CHP' => 25,
                'OMIS2020-CMX' => 25,
                'OMIS2020-COA' => 25,
                'OMIS2020-COL' => 25,
                'OMIS2020-DUR' => 25,
                'OMIS2020-GRO' => 25,
                'OMIS2020-GUA' => 25,
                'OMIS2020-HID' => 25,
                'OMIS2020-JAL' => 25,
                'OMIS2020-MEX' => 25,
                'OMIS2020-MIC' => 25,
                'OMIS2020-MOR' => 25,
                'OMIS2020-NAY' => 25,
                'OMIS2020-NLE' => 25,
                'OMIS2020-OAX' => 25,
                'OMIS2020-PUE' => 25,
                'OMIS2020-QTO' => 25,
                'OMIS2020-ROO' => 25,
                'OMIS2020-SIN' => 25,
                'OMIS2020-SLP' => 25,
                'OMIS2020-SON' => 25,
                'OMIS2020-TAB' => 25,
                'OMIS2020-TAM' => 25,
                'OMIS2020-TLA' => 25,
                'OMIS2020-VER' => 25,
                'OMIS2020-YUC' => 25,
                'OMIS2020-ZAC' => 25,
            ];
        } elseif ($r['contest_type'] == 'OMIPN') {
            if (
                $r->identity->username != 'andreasantillana'
                && !$is_system_admin
            ) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException();
            }

            $keys = [
                'OMIP2020-AGU' => 4,
                'OMIP2020-BCN' => 4,
                'OMIP2020-BCS' => 4,
                'OMIP2020-CAM' => 4,
                'OMIP2020-CHH' => 4,
                'OMIP2020-CHP' => 4,
                'OMIP2020-CMX' => 4,
                'OMIP2020-COA' => 4,
                'OMIP2020-COL' => 4,
                'OMIP2020-DUR' => 4,
                'OMIP2020-GRO' => 4,
                'OMIP2020-GUA' => 4,
                'OMIP2020-HID' => 4,
                'OMIP2020-JAL' => 4,
                'OMIP2020-MEX' => 4,
                'OMIP2020-MIC' => 4,
                'OMIP2020-MOR' => 4,
                'OMIP2020-NAY' => 4,
                'OMIP2020-NLE' => 4,
                'OMIP2020-OAX' => 4,
                'OMIP2020-PUE' => 4,
                'OMIP2020-QTO' => 4,
                'OMIP2020-ROO' => 4,
                'OMIP2020-SIN' => 4,
                'OMIP2020-SLP' => 4,
                'OMIP2020-SON' => 4,
                'OMIP2020-TAB' => 4,
                'OMIP2020-TAM' => 4,
                'OMIP2020-TLA' => 4,
                'OMIP2020-VER' => 4,
                'OMIP2020-YUC' => 4,
                'OMIP2020-ZAC' => 4,
                'OMIP2020-INV' => 4,
            ];
        } elseif ($r['contest_type'] == 'OMISN') {
            if (
                $r->identity->username != 'andreasantillana'
                && !$is_system_admin
            ) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException();
            }

            $keys = [
                'OMIS2020-AGU' => 4,
                'OMIS2020-BCN' => 4,
                'OMIS2020-BCS' => 4,
                'OMIS2020-CAM' => 4,
                'OMIS2020-CHH' => 4,
                'OMIS2020-CHP' => 4,
                'OMIS2020-CMX' => 4,
                'OMIS2020-COA' => 4,
                'OMIS2020-COL' => 4,
                'OMIS2020-DUR' => 4,
                'OMIS2020-GRO' => 4,
                'OMIS2020-GUA' => 4,
                'OMIS2020-HID' => 4,
                'OMIS2020-JAL' => 4,
                'OMIS2020-MEX' => 4,
                'OMIS2020-MIC' => 4,
                'OMIS2020-MOR' => 4,
                'OMIS2020-NAY' => 4,
                'OMIS2020-NLE' => 4,
                'OMIS2020-OAX' => 4,
                'OMIS2020-PUE' => 4,
                'OMIS2020-QTO' => 4,
                'OMIS2020-ROO' => 4,
                'OMIS2020-SIN' => 4,
                'OMIS2020-SLP' => 4,
                'OMIS2020-SON' => 4,
                'OMIS2020-TAB' => 4,
                'OMIS2020-TAM' => 4,
                'OMIS2020-TLA' => 4,
                'OMIS2020-VER' => 4,
                'OMIS2020-YUC' => 4,
                'OMIS2020-ZAC' => 4,
                'OMIS2020-INV' => 4,
            ];
        } elseif ($r['contest_type'] == 'ORIG') {
            if (
                $r->identity->username != 'kuko.coder'
                && !$is_system_admin
            ) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException();
            }

            $keys =  [
                'ORIG1516-CEL' => 38,
                'ORIG1516-DHI' => 15,
                'ORIG1516-GTO' => 14,
                'ORIG1516-IRA' => 37,
                'ORIG1516-PEN' => 22,
                'ORIG1516-LEO' => 43,
                'ORIG1516-SLP' => 14,
                'ORIG1516-SLV' => 14,
                'ORIG1516-URI' => 17,
                'ORIG1516-VDS' => 15,
            ];
        } elseif ($r['contest_type'] == 'OMIZAC-2018') {
            if (
                $r->identity->username != 'rsolis'
                && !$is_system_admin
            ) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException();
            }

            $keys =  [
                'OMIZAC-2018' => 20
            ];
        } elseif ($r['contest_type'] == 'Pr8oUAIE') {
            if (
                $r->identity->username != 'rsolis'
                && !$is_system_admin
            ) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException();
            }

            $keys =  [
                'Pr8oUAIE' => 20
            ];
        } elseif ($r['contest_type'] == 'OMICHH') {
            if (
                $r->identity->username != 'LaloRivero'
                && !$is_system_admin
            ) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException();
            }
            $keys =  [
                'OMICHH_2020' => 50
            ];
        } elseif ($r['contest_type'] == 'OMIZAC') {
            if (
                $r->identity->username != 'rsolis'
                && !$is_system_admin
            ) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException();
            }

            $keys =  [
                'OMIZAC-Prim' => 60,
                'OMIZAC-Sec' => 60,
                'OMIZAC-Prepa' => 60
            ];
        } elseif ($r['contest_type'] == 'ProgUAIE') {
            if (
                $r->identity->username != 'rsolis'
                && !$is_system_admin
            ) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException();
            }

            $keys =  [
                'MS-UAIE' => 60,
                'Prim-UAIE' => 40,
                'Sec-UAIE' => 40,
                'ICPC-UAIE' => 45,
                'Prim-UAIE-Jalpa' => 30,
                'Sec-UAIE-Jalpa' => 30
            ];
        } elseif ($r['contest_type'] == 'OMIAGS-2018') {
            if (
                $r->identity->username != 'EfrenGonzalez'
                && !$is_system_admin
            ) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException();
            }

            $keys =  [
                'OMIAGS-2018' => 30
            ];
        } elseif ($r['contest_type'] == 'OMIAGS-2017') {
            if (
                $r->identity->username != 'EfrenGonzalez'
                && !$is_system_admin
            ) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException();
            }

            $keys =  [
                'OMIAGS-2017' => 30
            ];
        } elseif ($r['contest_type'] == 'OMIP-AGS') {
            if (
                $r->identity->username != 'EfrenGonzalez'
                && !$is_system_admin
            ) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException();
            }

            $keys =  [
                'OMIP-AGS' => 30
            ];
        } elseif ($r['contest_type'] == 'OMIS-AGS') {
            if (
                $r->identity->username != 'EfrenGonzalez'
                && !$is_system_admin
            ) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException();
            }

            $keys =  [
                'OMIS-AGS' => 30
            ];
        } elseif ($r['contest_type'] == 'OSI') {
            if (
                $r->identity->username != 'cope_quintana'
                && !$is_system_admin
            ) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException();
            }

            $keys =  [
                'OSI16' => 120
            ];
        } elseif ($r['contest_type'] == 'UNAMFC') {
            if (
                $r->identity->username != 'manuelalcantara52'
                && $r->identity->username != 'manuel52'
                && !$is_system_admin
            ) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException();
            }
            $keys =  [
                'UNAMFC16' => 65
            ];
        } elseif ($r['contest_type'] == 'OVI') {
            if (
                $r->identity->username != 'covi.academico'
                && !$is_system_admin
            ) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException();
            }
            $keys =  [
                'OVI19' => 200
            ];
        } elseif ($r['contest_type'] == 'UDCCUP') {
            if (
                $r->identity->username != 'Diego_Briaares'
                && !$is_system_admin
            ) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException();
            }
            $keys =  [
                'UDCCUP-2017' => 40
            ];
        } elseif ($r['contest_type'] == 'CCUPITSUR') {
            if (
                $r->identity->username != 'licgerman-yahoo'
                && !$is_system_admin
            ) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException();
            }
            // Arreglo de concurso
            $keys = [
                'CCUPITSUR-16' => 50,
                'CCUPTECNM' => 500,
            ];
        } elseif ($r['contest_type'] == 'CONALEP') {
            if (
                $r->identity->username != 'reyes811'
                && !$is_system_admin
            ) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException();
            }
            $keys =  [
                'OIC-16' => 225
            ];
        } elseif ($r['contest_type'] == 'OMIQROO') {
            if (
                $r->identity->username != 'pablobatun'
                && !$is_system_admin
            ) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException();
            }
            $keys = [
                'OMIROO-Prim-20' => 100,
                'OMIROO-Secu-20' => 100,
                'OMIROO-Prep-20' => 300,
            ];
        } elseif ($r['contest_type'] == 'TEBAEV') {
            if (
                $r->identity->username != 'lacj20'
                && !$is_system_admin
            ) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException();
            }
            $keys = [
                'TEBAEV' => 250,
            ];
        } elseif ($r['contest_type'] == 'PYE-AGS') {
            if (
                $r->identity->username != 'joemmanuel'
                && !$is_system_admin
            ) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException();
            }
            $keys = [
                'PYE-AGS18' => 40,
            ];
        } elseif ($r['contest_type'] == 'CAPKnuth') {
            if (
                $r->identity->username != 'galloska'
                && !$is_system_admin
            ) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException();
            }
            $keys = [
                'ESCOM2018' => 50,
            ];
        } elseif ($r['contest_type'] == 'CAPVirtualKnuth') {
            if (
                $r->identity->username != 'galloska'
                && !$is_system_admin
            ) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException();
            }
            $keys = [
                'Virtual-ESCOM2018' => 50,
            ];
        } elseif ($r['contest_type'] == 'OMI_CHH-2020') {
            if (
                $r->identity->username != 'LaloRivero'
                && !$is_system_admin
            ) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException();
            }

            $keys =  [
                'OMI_CHH-2020' => 50
            ];
        } elseif ($r['contest_type'] == 'OMIP_CHH-2020') {
            if (
                $r->identity->username != 'LaloRivero'
                && !$is_system_admin
            ) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException();
            }

            $keys =  [
                'OMIP_CHH-2020' => 50
            ];
        } elseif ($r['contest_type'] == 'OMIS_CHH-2020') {
            if (
                $r->identity->username != 'LaloRivero'
                && !$is_system_admin
            ) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException();
            }

            $keys =  [
                'OMIS_CHH-2020' => 70
            ];
        } elseif ($r['contest_type'] == 'CONTESTCAC') {
            if (
                $r->identity->username != 'Franco1010'
                && !$is_system_admin
            ) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException();
            }
            $keys = [
                'CAC2019B' => 50,
            ];
        } else {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterNotInExpectedSet',
                'contest_type',
                [
                    'bad_elements' => $r['contest_type'],
                    'expected_set' => 'CONTESTCAC, OMI, OMIAGS, OMIP-AGS, OMIS-AGS, ORIG, OSI, OVI, UDCCUP, CCUPITSUR, CONALEP, OMIQROO, OMIAGS-2017, OMIAGS-2018, PYE-AGS, OMIZAC-2018, Pr8oUAIE, CAPKnuth, CAPVirtualKnuth, OMIZAC, ProgUAIE, CCUPTECNM',
                ]
            );
        }

        self::$permissionKey = $r['permission_key'] = \OmegaUp\SecurityTools::randomString(
            32
        );

        foreach ($keys as $k => $n) {
            $digits = intval(floor(log10($n) + 1));
            for ($i = 1; $i <= $n; $i++) {
                $digit = str_pad(strval($i), $digits, '0', STR_PAD_LEFT);
                $username = "{$k}-{$digit}";
                $password = \OmegaUp\SecurityTools::randomString(8);

                if (self::omiPrepareUser($r, $username, $password)) {
                    $response[$username] = $password;
                }

                // Add user to contest if needed
                if (!is_null($contestAlias)) {
                    $addUserRequest = new \OmegaUp\Request();
                    $addUserRequest['auth_token'] = $r['auth_token'];
                    $addUserRequest['usernameOrEmail'] = $username;
                    $addUserRequest['contest_alias'] = $contestAlias;
                    \OmegaUp\Controllers\Contest::apiAddUser($addUserRequest);
                }
            }
        }

        return $response;
    }

    /**
     * Returns the profile of the user given
     *
     * @return UserProfile
     */
    public static function getProfileImpl(
        \OmegaUp\DAO\VO\Users $user,
        \OmegaUp\DAO\VO\Identities $identity
    ): array {
        $response = [
            'username' => $identity->username,
            'name' => $identity->name,
            'birth_date' => is_null(
                $user->birth_date
            ) ? null : \OmegaUp\DAO\DAO::fromMySQLTimestamp(
                $user->birth_date
            ),
            'gender' => $identity->gender,
            'scholar_degree' => $user->scholar_degree,
            'preferred_language' => $user->preferred_language,
            'is_private' => $user->is_private,
            'verified' => $user->verified == '1',
            'hide_problem_tags' => is_null(
                $user->hide_problem_tags
            ) ? false : $user->hide_problem_tags,
        ];

        $userDb = \OmegaUp\DAO\Users::getExtendedProfileDataByPk(
            intval($user->user_id)
        );
        if (is_null($userDb)) {
            throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
        }

        $response['graduation_date'] = is_null(
            $userDb['graduation_date']
        ) ? null : \OmegaUp\DAO\DAO::fromMySQLTimestamp(
            $userDb['graduation_date']
        );
        $response['email'] = $userDb['email'];
        $response['classname'] = $userDb['classname'];
        $response['country'] = $userDb['country'];
        $response['country_id'] = $userDb['country_id'];
        $response['state'] = $userDb['state'];
        $response['state_id'] = $userDb['state_id'];
        $response['school'] = $userDb['school'];
        $response['school_id'] = $userDb['school_id'];
        $response['locale'] =
        \OmegaUp\Controllers\Identity::convertToSupportedLanguage(
            $userDb['locale']
        );

        $response['gravatar_92'] = 'https://secure.gravatar.com/avatar/' . md5(
            strval($response['email'])
        ) . '?s=92';

        return $response;
    }

    public static function getProfileProgress(
        ?\OmegaUp\DAO\VO\Users $user
    ): float {
        if (
            is_null($user) ||
            is_null($user->main_identity_id) ||
            is_null($user->user_id)
        ) {
            return 0;
        }

        $identity = \OmegaUp\DAO\Identities::getByPK($user->main_identity_id);
        $profile = \OmegaUp\DAO\Users::getExtendedProfileDataByPk(
            $user->user_id
        );
        if (is_null($identity) || is_null($profile)) {
            return 0;
        }
        $fields = [
            'username' => !is_null($identity->username) ? 1 : 0,
            'name' => !is_null($identity->name) ? 1 : 0,
            'birth_date' => !is_null($user->birth_date) ? 1 : 0,
            'gender' => !is_null($identity->gender) ? 1 : 0,
            'scholar_degree' => !is_null($user->scholar_degree) ? 1 : 0,
            'preferred_language' => !is_null($user->preferred_language) ? 1 : 0,
            'verified' => $user->verified ? 1 : 0,
            'graduation_date' => !is_null(
                $profile['graduation_date']
            ) ? 1 : 0,
            'email' => !is_null($profile['email']) ? 1 : 0,
            'country_id' => !is_null($profile['country_id']) ? 1 : 0,
            'state_id' => !is_null($profile['state_id']) ? 1 : 0,
            'school_id' => !is_null($profile['school_id']) ? 1 : 0,
            'locale' => !is_null($profile['locale']) ? 1 : 0,
        ];
        return (array_sum($fields) / count($fields)) * 100;
    }

    /**
     * Get general user info
     *
     * @return UserProfileInfo
     *
     * @omegaup-request-param mixed $category
     * @omegaup-request-param bool|null $omit_rank
     * @omegaup-request-param mixed $username
     */
    public static function apiProfile(\OmegaUp\Request $r): array {
        self::authenticateOrAllowUnauthenticatedRequest($r);

        \OmegaUp\Validators::validateOptionalInEnum(
            $r['category'],
            'category',
            \OmegaUp\Controllers\User::ALLOWED_CODER_OF_THE_MONTH_CATEGORIES
        );
        $category = $r['category'] ?? 'all';

        $identity = self::resolveTargetIdentity($r);
        if (is_null($identity)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterNotFound',
                'Identity'
            );
        }
        return self::getUserProfile(
            $r->identity,
            $identity,
            $r->ensureOptionalBool('omit_rank') ?? false,
            $category
        );
    }

    /**
     * @return UserProfileInfo
     */
    public static function getUserProfile(
        ?\OmegaUp\DAO\VO\Identities $loggedIdentity,
        \OmegaUp\DAO\VO\Identities $identity,
        bool $omitRank = false,
        string $category = 'all'
    ) {
        $user = is_null(
            $identity->user_id
        ) ? null : \OmegaUp\DAO\Users::getByPK(
            $identity->user_id
        );
        if (
            (is_null($loggedIdentity)
            || $loggedIdentity->username != $identity->username)
            && (!is_null($user)
            && $user->is_private == 1)
            && (is_null($loggedIdentity)
            || !\OmegaUp\Authorization::isSystemAdmin($loggedIdentity))
        ) {
            $response = [
                'username' => $identity->username,
                'rankinfo' => [
                    'name' => null,
                    'problems_solved' => null,
                    'rank' => null,
                    'author_ranking' => null,
                ],
                'is_private' => true,
                'birth_date' => null,
                'country' => null,
                'country_id' => null,
                'email' => null,
                'gender' => null,
                'graduation_date' => null,
                'gravatar_92' => null,
                'hide_problem_tags' => false,
                'locale' => null,
                'name' => null,
                'preferred_language' => null,
                'scholar_degree' => null,
                'school' => null,
                'school_id' => null,
                'state' => null,
                'state_id' => null,
                'verified' => null,
            ];
        } else {
            $response = \OmegaUp\Controllers\Identity::getProfile(
                $loggedIdentity,
                $identity,
                $user,
                $omitRank,
                $category
            );
        }
        $response['classname'] = \OmegaUp\DAO\Users::getRankingClassName(
            $identity->user_id
        );
        $response['programming_languages'] = \OmegaUp\Controllers\Run::SUPPORTED_LANGUAGES;
        return $response;
    }

    /**
     * Gets verify status of a user
     *
     * @omegaup-request-param string $email
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     *
     * @return array{username: string, verified: bool}
     */
    public static function apiStatusVerified(\OmegaUp\Request $r) {
        $r->ensureIdentity();

        if (!\OmegaUp\Authorization::isSupportTeamMember($r->identity)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }
        $email = $r->ensureString(
            'email',
            fn (string $email) => \OmegaUp\Validators::email($email)
        );
        $response = \OmegaUp\DAO\Users::getStatusVerified($email);

        if (is_null($response)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'invalidUser'
            );
        }

        return [
            'status' => 'ok',
            'verified' => $response['verified'],
            'username' => $response['username']
        ];
    }
    /**
     * Gets extra information of the identity:
     * - last password change request
     * - verify status
     *
     * @omegaup-request-param string $email
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     *
     * @return array{within_last_day: bool, verified: bool, username: string, last_login: \OmegaUp\Timestamp|null}
     */
    public static function apiExtraInformation(\OmegaUp\Request $r): array {
        $r->ensureIdentity();
        $email = $r->ensureString(
            'email',
            fn (string $email) => \OmegaUp\Validators::email($email)
        );

        if (!\OmegaUp\Authorization::isSupportTeamMember($r->identity)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        $response = \OmegaUp\DAO\Identities::getExtraInformation($email);
        if (is_null($response)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'invalidUser'
            );
        }
        return $response;
    }

    /**
     * Get coder of the month by trying to find it in the table using the first
     * day of the current month. If there's no coder of the month for the given
     * date, calculate it and save it.
     *
     * @param \OmegaUp\Request $r
     *
     * @return array{coderinfo: UserProfile|null}
     *
     * @omegaup-request-param mixed $category
     * @omegaup-request-param null|string $date
     */
    public static function apiCoderOfTheMonth(\OmegaUp\Request $r) {
        \OmegaUp\Validators::validateOptionalStringNonEmpty(
            $r['date'],
            'date'
        );
        \OmegaUp\Validators::validateOptionalInEnum(
            $r['category'],
            'category',
            \OmegaUp\Controllers\User::ALLOWED_CODER_OF_THE_MONTH_CATEGORIES
        );
        return self::getCoderOfTheMonth(
            self::getCurrentMonthFirstDay($r['date']),
            $r['category'] ?? 'all'
        );
    }

    /**
     * @return array{coderinfo: UserProfile|null}
     */
    private static function getCoderOfTheMonth(
        string $firstDay,
        string $category = 'all'
    ): array {
        $codersOfTheMonth = \OmegaUp\DAO\CoderOfTheMonth::getByTime(
            $firstDay,
            $category
        );
        if (empty($codersOfTheMonth)) {
            return [
                'coderinfo' => null,
            ];
        }
        $coderOfTheMonthUserId = $codersOfTheMonth[0]->user_id;
        // If someone was explicitly selected from the list, use that as coder of the month instead of the first place.
        foreach ($codersOfTheMonth as $coder) {
            if (isset($coder->selected_by)) {
                $coderOfTheMonthUserId = $coder->user_id;
                break;
            }
        }

        if (is_null($coderOfTheMonthUserId)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'coderOfTheMonthNotFound'
            );
        }

        // Get the profile of the coder of the month
        $user = \OmegaUp\DAO\Users::getByPK($coderOfTheMonthUserId);
        if (is_null($user) || is_null($user->main_identity_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
        }
        $identity = \OmegaUp\DAO\Identities::getByPK($user->main_identity_id);
        if (is_null($identity)) {
            throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
        }
        $response = [
            'coderinfo' => \OmegaUp\Controllers\User::getProfileImpl(
                $user,
                $identity
            ),
        ];

        // But avoid divulging the email in the response.
        unset($response['coderinfo']['email']);

        return $response;
    }

    /**
     * Returns the list of coders of the month
     *
     * @return array{coders: CoderOfTheMonthList}
     *
     * @omegaup-request-param mixed $category
     * @omegaup-request-param null|string $date
     */
    public static function apiCoderOfTheMonthList(\OmegaUp\Request $r): array {
        \OmegaUp\Validators::validateOptionalDate($r['date'], 'date');
        \OmegaUp\Validators::validateOptionalInEnum(
            $r['category'],
            'category',
            \OmegaUp\Controllers\User::ALLOWED_CODER_OF_THE_MONTH_CATEGORIES
        );
        $category = $r['category'] ?? 'all';
        if (!is_null($r['date'])) {
            $coders = \OmegaUp\DAO\CoderOfTheMonth::getMonthlyList(
                $r['date'],
                $category
            );
        } else {
            $coders = \OmegaUp\DAO\CoderOfTheMonth::getCodersOfTheMonth(
                $category
            );
        }
        return [
            'coders' => self::processCodersList($coders),
        ];
    }

    /**
     * Selects coder of the month for next month.
     *
     * @return array{status: 'ok'}
     *
     * @omegaup-request-param mixed $category
     * @omegaup-request-param string $username
     */
    public static function apiSelectCoderOfTheMonth(\OmegaUp\Request $r): array {
        $r->ensureIdentity();
        $currentTimestamp = \OmegaUp\Time::get();

        if (!\OmegaUp\Authorization::isMentor($r->identity)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'userNotAllowed'
            );
        }
        if (
            !\OmegaUp\Authorization::canChooseCoderOrSchool(
                $currentTimestamp
            )
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'coderOfTheMonthIsNotInPeriodToBeChosen'
            );
        }
        \OmegaUp\Validators::validateStringNonEmpty($r['username'], 'username');
        \OmegaUp\Validators::validateOptionalInEnum(
            $r['category'],
            'category',
            \OmegaUp\Controllers\User::ALLOWED_CODER_OF_THE_MONTH_CATEGORIES
        );
        $category = $r['category'] ?? 'all';

        $currentDate = date('Y-m-d', $currentTimestamp);
        $firstDayOfNextMonth = new \DateTime($currentDate);
        $firstDayOfNextMonth->modify('first day of next month');
        $dateToSelect = $firstDayOfNextMonth->format('Y-m-d');

        $codersOfTheMonth = \OmegaUp\DAO\CoderOfTheMonth::getByTimeAndSelected(
            $dateToSelect,
            /*autoselected=*/false,
            $category
        );
        if (!empty($codersOfTheMonth)) {
            throw new \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException(
                'coderOfTheMonthAlreadySelected'
            );
        }

        $users = \OmegaUp\DAO\CoderOfTheMonth::getCandidatesToCoderOfTheMonth(
            $dateToSelect,
            $category
        );

        if (empty($users)) {
            throw new \OmegaUp\Exceptions\NotFoundException('noCoders');
        }

        try {
            \OmegaUp\DAO\DAO::transBegin();
            foreach ($users as $index => $user) {
                $newCoderOfTheMonth = new \OmegaUp\DAO\VO\CoderOfTheMonth([
                    'coder_of_the_month_id' => $user['coder_of_the_month_id'],
                    'user_id' => $user['user_id'],
                    'school_id' => $user['school_id'],
                    'time' => $user['time'],
                    'ranking' => $user['ranking'],
                    'category' => $user['category'],
                    'score' => $user['score'],
                    'problems_solved' => $user['problems_solved'],
                ]);
                // Only the CoderOfTheMonth selected by the mentor is going to be
                // updated.
                if ($user['username'] !== $r['username']) {
                    continue;
                }
                $newCoderOfTheMonth->selected_by = $r->identity->identity_id;
                \OmegaUp\DAO\CoderOfTheMonth::update($newCoderOfTheMonth);
            }
            \OmegaUp\DAO\DAO::transEnd();
        } catch (\Exception $e) {
            \OmegaUp\DAO\DAO::transRollback();
            throw $e;
        }

        return [
            'status' => 'ok',
        ];
    }

    public static function userOpenedProblemset(
        int $problemsetId,
        int $userId
    ): bool {
        // User already started the problemset.
        $problemsetOpened = \OmegaUp\DAO\ProblemsetIdentities::getByPK(
            $userId,
            $problemsetId
        );

        return (
            !is_null($problemsetOpened) &&
            !is_null($problemsetOpened->access_time)
        );
    }

    /**
     * Get the results for this user in a given interview
     *
     * @param \OmegaUp\Request $r
     *
     * @return array{user_verified: bool, interview_url: string, name_or_username: null|string, opened_interview: bool, finished: bool}
     *
     * @omegaup-request-param string $interview
     * @omegaup-request-param string $username
     */
    public static function apiInterviewStats(\OmegaUp\Request $r): array {
        $r->ensureIdentity();

        $interviewAlias = $r->ensureString(
            'interview',
            fn (string $alias) => \OmegaUp\Validators::alias($alias)
        );
        \OmegaUp\Validators::validateStringNonEmpty($r['username'], 'username');

        $contest = \OmegaUp\DAO\Contests::getByAlias($interviewAlias);
        if (is_null($contest)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'interviewNotFound'
            );
        }

        // Only admins can view interview details
        if (!\OmegaUp\Authorization::isContestAdmin($r->identity, $contest)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        $user = self::resolveTargetUser($r);
        if (is_null($user) || is_null($user->main_identity_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
        }
        $identity = \OmegaUp\DAO\Identities::getByPK($user->main_identity_id);
        if (is_null($identity)) {
            throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
        }

        return [
            'user_verified' => $user->verified,
            'interview_url' => "https://omegaup.com/interview/{$contest->alias}/arena/",
            'name_or_username' => $identity->name ?? $identity->username,
            'opened_interview' => self::userOpenedProblemset(
                intval($contest->problemset_id),
                intval($user->user_id)
            ),
            'finished' => !\OmegaUp\DAO\Problemsets::isSubmissionWindowOpen(
                $contest
            ),
        ];
    }

    /**
     * Get Contests which a certain user has participated in
     *
     * @return array{contests: array<string, array{data: array{alias: string, title: string, start_time: \OmegaUp\Timestamp, finish_time: \OmegaUp\Timestamp, last_updated: \OmegaUp\Timestamp}, place?: int}>}
     *
     * @omegaup-request-param mixed $auth_token
     * @omegaup-request-param string $contest_alias
     * @omegaup-request-param null|string $token
     * @omegaup-request-param mixed $username
     */
    public static function apiContestStats(\OmegaUp\Request $r): array {
        self::authenticateOrAllowUnauthenticatedRequest($r);

        $identity = self::resolveTargetIdentity($r);
        if (is_null($identity) || is_null($identity->identity_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
        }

        // Get contests where identity had at least 1 run
        $contestsParticipated = \OmegaUp\DAO\Contests::getContestsParticipated(
            $identity->identity_id
        );

        /** @var array<string, array{data: array{alias: string, title: string, start_time: \OmegaUp\Timestamp, finish_time: \OmegaUp\Timestamp, last_updated: \OmegaUp\Timestamp}, place?: int}> */
        $contests = [];

        foreach ($contestsParticipated as &$contest) {
            // Get identity ranking
            $scoreboardResponse = \OmegaUp\Controllers\Contest::apiScoreboard(
                new \OmegaUp\Request([
                    'auth_token' => $r['auth_token'],
                    'contest_alias' => $contest['alias'],
                    'token' => $contest['scoreboard_url_admin'],
                ])
            );

            // Avoid divulging the scoreboard URL unnecessarily.
            unset($contest['scoreboard_url_admin']);

            $contests[$contest['alias']] = [
                'data' => $contest,
            ];

            // Grab the place of the current identity in the given contest
            foreach ($scoreboardResponse['ranking'] as $identityData) {
                if (
                    $identityData['username'] == $identity->username &&
                    isset($identityData['place'])
                ) {
                    $contests[$contest['alias']]['place'] = $identityData['place'];
                    break;
                }
            }
        }

        return [
            'contests' => $contests,
        ];
    }

    /**
     * Get Problems solved by user
     *
     * @omegaup-request-param mixed $username
     *
     * @return array{problems: list<Problem>}
     */
    public static function apiProblemsSolved(\OmegaUp\Request $r): array {
        self::authenticateOrAllowUnauthenticatedRequest($r);

        $identity = self::resolveTargetIdentity($r);
        if (is_null($identity) || is_null($identity->identity_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
        }
        $problems = \OmegaUp\DAO\Problems::getProblemsSolved(
            $identity->identity_id
        );

        /** @var list<Problem> */
        $responseProblems = [];
        $relevantColumns = ['title', 'alias', 'submissions', 'accepted', 'difficulty'];
        foreach ($problems as $problem) {
            if (!\OmegaUp\DAO\Problems::isVisible($problem)) {
                continue;
            }
            /** @var Problem */
            $responseProblems[] = $problem->asFilteredArray($relevantColumns);
        }

        return [
            'problems' => $responseProblems,
        ];
    }

    /**
     * Get Problems unsolved by user
     *
     * @omegaup-request-param mixed $username
     *
     * @return array{problems: list<Problem>}
     */
    public static function apiListUnsolvedProblems(\OmegaUp\Request $r): array {
        self::authenticateOrAllowUnauthenticatedRequest($r);

        $identity = self::resolveTargetIdentity($r);
        if (is_null($identity) || is_null($identity->identity_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
        }

        $problems = \OmegaUp\DAO\Problems::getProblemsUnsolvedByIdentity(
            $identity->identity_id
        );

        $relevantColumns = ['title', 'alias', 'submissions', 'accepted', 'difficulty'];
        /** @var list<Problem> */
        $filteredProblems = [];
        foreach ($problems as $problem) {
            if (\OmegaUp\DAO\Problems::isVisible($problem)) {
                /** @var Problem */
                $filteredProblems[] = $problem->asFilteredArray(
                    $relevantColumns
                );
            }
        }

        return [
            'problems' => $filteredProblems,
        ];
    }

    /**
     * Get Problems created by user
     *
     * @omegaup-request-param mixed $username
     *
     * @return array{problems: list<Problem>}
     */
    public static function apiProblemsCreated(\OmegaUp\Request $r): array {
        self::authenticateOrAllowUnauthenticatedRequest($r);

        $identity = self::resolveTargetIdentity($r);
        if (is_null($identity)) {
            throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
        }

        $relevantColumns = ['title', 'alias', 'submissions', 'accepted', 'difficulty'];
        /** @var list<Problem> */
        $filteredProblems = [];
        foreach (
            \OmegaUp\DAO\Problems::getPublicProblemsCreatedByIdentity(
                intval($identity->identity_id)
            ) as $problem
        ) {
            if (\OmegaUp\DAO\Problems::isVisible($problem)) {
                /** @var Problem */
                $filteredProblems[] = $problem->asFilteredArray(
                    $relevantColumns
                );
            }
        }

        return [
            'problems' => $filteredProblems,
        ];
    }

    /**
     * Gets a list of users. This returns an array instead of an object since
     * it is used by typeahead.
     *
     * @omegaup-request-param mixed $query
     * @omegaup-request-param mixed $term
     *
     * @return list<UserListItem>
     */
    public static function apiList(\OmegaUp\Request $r): array {
        $param = '';
        if (is_string($r['term'])) {
            $param = $r['term'];
        } elseif (is_string($r['query'])) {
            $param = $r['query'];
        } else {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterEmpty',
                'query'
            );
        }

        $identities = \OmegaUp\DAO\Identities::findByUsernameOrName($param);
        $response = [];
        foreach ($identities as $identity) {
            $response[] = [
                'label' => strval($identity->username),
                'value' => strval($identity->username)
            ];
        }
        return $response;
    }

    /**
     * Get stats
     *
     * @omegaup-request-param mixed $username
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return array{runs: list<array{date: null|string, runs: int, verdict: string}>}
     */
    public static function apiStats(\OmegaUp\Request $r): array {
        self::authenticateOrAllowUnauthenticatedRequest($r);
        $identity = self::resolveTargetIdentity($r);
        if (is_null($identity)) {
            throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
        }
        $user = null;
        if (!is_null($identity->user_id)) {
            $user = \OmegaUp\DAO\Users::getByPK($identity->user_id);
        }

        if (
            (is_null($r->identity)
             || ($r->identity->username != $identity->username
                 && !\OmegaUp\Authorization::isSystemAdmin($r->identity)))
            && (!is_null($user)
            && $user->is_private == 1)
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'userProfileIsPrivate'
            );
        }

        return [
            'runs' => \OmegaUp\DAO\Runs::countRunsOfIdentityPerDatePerVerdict(
                intval($identity->identity_id)
            ),
        ];
    }

    /**
     * Update basic user profile info when logged with fb/gool
     *
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     *
     * @return array{status: string}
     *
     * @omegaup-request-param string $password
     * @omegaup-request-param string $username
     */
    public static function apiUpdateBasicInfo(\OmegaUp\Request $r): array {
        $r->ensureIdentity();
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['username'],
            'username'
        );
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['password'],
            'password'
        );

        if (self::isNonUserIdentity($r->identity)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'userNotAllowed'
            );
        }

        //Buscar que el nuevo username no este ocupado si es que selecciono uno nuevo
        if ($r['username'] !== $r->identity->username) {
            $testu = \OmegaUp\DAO\Users::FindByUsername($r['username']);

            if (!is_null($testu)) {
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'parameterUsernameInUse',
                    'username'
                );
            }
            $r->identity->username = $r['username'];
        }

        \OmegaUp\SecurityTools::testStrongPassword($r['password']);
        $hashedPassword = \OmegaUp\SecurityTools::hashString(
            strval($r['password'])
        );
        $r->identity->password = $hashedPassword;

        // Update username and password for identity object
        \OmegaUp\DAO\Identities::update($r->identity);

        // Expire profile cache
        \OmegaUp\Cache::deleteFromCache(
            \OmegaUp\Cache::USER_PROFILE,
            $r->identity->username
        );
        \OmegaUp\Controllers\Session::invalidateCache();

        return [
            'status' => 'ok',
        ];
    }

    /**
     * Update user profile
     *
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     *
     * @return array{status: string}
     *
     * @omegaup-request-param mixed $auth_token
     * @omegaup-request-param string $birth_date
     * @omegaup-request-param string $country_id
     * @omegaup-request-param 'decline'|'female'|'male'|'other'|null $gender
     * @omegaup-request-param string $graduation_date
     * @omegaup-request-param bool|null $hide_problem_tags
     * @omegaup-request-param bool|null $is_private
     * @omegaup-request-param string $locale
     * @omegaup-request-param null|string $name
     * @omegaup-request-param null|string $scholar_degree
     * @omegaup-request-param int|null $school_id
     * @omegaup-request-param mixed $school_name
     * @omegaup-request-param string $state_id
     * @omegaup-request-param mixed $username
     */
    public static function apiUpdate(\OmegaUp\Request $r): array {
        $r->ensureMainUserIdentity();

        if (isset($r['username'])) {
            \OmegaUp\Validators::validateValidUsername(
                $r['username'],
                'username'
            );
            $user = \OmegaUp\DAO\Users::FindByUsername($r['username']);
            if ($r['username'] !== $r->identity->username && !is_null($user)) {
                throw new \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException(
                    'usernameInUse'
                );
            }

            if (self::isNonUserIdentity($r->identity)) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                    'userNotAllowed'
                );
            }
        }

        if (!is_null($r['name'])) {
            \OmegaUp\Validators::validateStringOfLengthInRange(
                $r['name'],
                'name',
                1,
                50
            );
            $r->identity->name = $r['name'];
        }

        $state = null;
        if (!is_null($r['country_id']) || !is_null($r['state_id'])) {
            // Both state and country must be specified together.
            \OmegaUp\Validators::validateStringNonEmpty(
                $r['country_id'],
                'country_id'
            );
            \OmegaUp\Validators::validateStringNonEmpty(
                $r['state_id'],
                'state_id'
            );

            $state = \OmegaUp\DAO\States::getByPK(
                $r['country_id'],
                $r['state_id']
            );
            if (is_null($state)) {
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'parameterInvalid',
                    'state_id'
                );
            }
            $r->identity->state_id = $state->state_id;
            $r->identity->country_id = $state->country_id;
        }

        // Save previous values
        $currentIdentitySchool = null;
        $currentGraduationDate = null;
        $currentSchoolId = null;
        if (!is_null($r->identity->current_identity_school_id)) {
            $currentIdentitySchool = \OmegaUp\DAO\IdentitiesSchools::getByPK(
                $r->identity->current_identity_school_id
            );
            if (!is_null($currentIdentitySchool)) {
                $currentSchoolId = $currentIdentitySchool->school_id;
                if (!is_null($currentIdentitySchool->graduation_date)) {
                    $currentGraduationDate = \OmegaUp\DAO\DAO::fromMySQLTimestamp(
                        $currentIdentitySchool->graduation_date
                    );
                }
            }
        }
        $newSchoolId = $currentSchoolId;

        \OmegaUp\Validators::validateOptionalNumber(
            $r['school_id'],
            'school_id'
        );
        if (!is_null($r['school_id'])) {
            $school = \OmegaUp\DAO\Schools::getByPK(intval($r['school_id']));
            if (is_null($school)) {
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'parameterInvalid',
                    'school'
                );
            }
            $newSchoolId = $school->school_id;
        } else {
            // Whether the user has already set a school in DB, but user
            // writes another one
            $newSchoolId = null;
        }

        if (is_null($newSchoolId) && !empty($r['school_name'])) {
            $response = \OmegaUp\Controllers\School::apiCreate(
                new \OmegaUp\Request([
                    'name' => $r['school_name'],
                    'country_id' => !is_null(
                        $state
                    ) ? $state->country_id : null,
                    'state_id' => !is_null($state) ? $state->state_id : null,
                    'auth_token' => $r['auth_token'],
                ])
            );
            $newSchoolId = $response['school_id'];
        }

        \OmegaUp\Validators::validateOptionalStringNonEmpty(
            $r['scholar_degree'],
            'scholar_degree'
        );

        $newGraduationDate = $currentGraduationDate;
        if (!is_null($r['graduation_date'])) {
            if (is_numeric($r['graduation_date'])) {
                $graduationDate = new \OmegaUp\Timestamp(
                    intval($r['graduation_date'])
                );
            } else {
                \OmegaUp\Validators::validateDate(
                    $r['graduation_date'],
                    'graduation_date'
                );
                $graduationDate = new \OmegaUp\Timestamp(
                    strtotime($r['graduation_date'])
                );
            }
            $newGraduationDate = $graduationDate;
        }
        if (!is_null($r['birth_date'])) {
            if (is_numeric($r['birth_date'])) {
                $birthDate = intval($r['birth_date']);
            } else {
                \OmegaUp\Validators::validateDate(
                    $r['birth_date'],
                    'birth_date'
                );
                $birthDate = strtotime($r['birth_date']);
            }

            if ($birthDate >= strtotime('-5 year', \OmegaUp\Time::get())) {
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'birthdayInTheFuture',
                    'birth_date'
                );
            }
            $r['birth_date'] = $birthDate;
        }

        if (!is_null($r['locale'])) {
            // find language in Language
            \OmegaUp\Validators::validateStringNonEmpty(
                $r['locale'],
                'locale'
            );
            $language = \OmegaUp\DAO\Languages::getByName($r['locale']);
            if (is_null($language)) {
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'invalidLanguage',
                    'locale'
                );
            }
            $r->identity->language_id = $language->language_id;
        }

        $r->ensureOptionalBool('is_private');
        $r->ensureOptionalBool('hide_problem_tags');
        if (!is_null($r['gender'])) {
            $r->identity->gender = $r->ensureOptionalEnum(
                'gender',
                \OmegaUp\Controllers\User::ALLOWED_GENDER_OPTIONS
            );
        }

        $userValueProperties = [
            'username',
            'scholar_degree',
            'birth_date' => [
                'transform' => fn (int $value): string => strval(
                    gmdate('Y-m-d', $value)
                ),
            ],
            'preferred_language',
            'is_private',
            'hide_problem_tags',
        ];

        $identityValueProperties = [
            'username',
            'name',
            'country_id',
            'state_id',
            'gender',
        ];

        self::updateValueProperties($r, $r->user, $userValueProperties);
        self::updateValueProperties($r, $r->identity, $identityValueProperties);

        try {
            \OmegaUp\DAO\DAO::transBegin();

            // Update IdentitiesSchools
            if ($newSchoolId !== $currentSchoolId && !is_null($newSchoolId)) {
                // Update end time for current record and create a new one
                $graduationDate = !is_null(
                    $newGraduationDate
                ) ? gmdate(
                    'Y-m-d',
                    $newGraduationDate->time
                ) : null;
                $newIdentitySchool = \OmegaUp\DAO\IdentitiesSchools::createNewSchoolForIdentity(
                    $r->identity,
                    $newSchoolId,
                    $graduationDate
                );
                $r->identity->current_identity_school_id = $newIdentitySchool->identity_school_id;
            } elseif (
                (!is_null($newSchoolId)
                || !is_null($currentSchoolId))
                && ($currentGraduationDate !== $newGraduationDate)
            ) {
                $graduationDate = !is_null(
                    $newGraduationDate
                ) ? gmdate(
                    'Y-m-d',
                    $newGraduationDate->time
                ) : null;
                if (!is_null($currentIdentitySchool)) {
                    // Only update the graduation date
                    $currentIdentitySchool->graduation_date = $graduationDate;
                    \OmegaUp\DAO\IdentitiesSchools::update(
                        $currentIdentitySchool
                    );
                } else {
                    // Create a new record
                    $newIdentitySchool = new \OmegaUp\DAO\VO\IdentitiesSchools([
                        'identity_id' => intval($r->identity->identity_id),
                        'school_id' => $newSchoolId,
                        'graduation_date' => $graduationDate,
                    ]);

                    \OmegaUp\DAO\IdentitiesSchools::create($newIdentitySchool);
                    $r->identity->current_identity_school_id = $newIdentitySchool->identity_school_id;
                }
            }

            \OmegaUp\DAO\Users::update($r->user);
            \OmegaUp\DAO\Identities::update($r->identity);

            \OmegaUp\DAO\DAO::transEnd();
        } catch (\Exception $e) {
            \OmegaUp\DAO\DAO::transRollback();

            throw $e;
        }

        // Expire profile cache
        \OmegaUp\Cache::deleteFromCache(
            \OmegaUp\Cache::USER_PROFILE,
            $r->identity->username
        );
        \OmegaUp\Controllers\Session::invalidateCache();

        return [
            'status' => 'ok',
        ];
    }

    /**
     * Get full rank by problems solved logic. It has its own func so it can be
     * accesed internally without authentication.
     *
     * @return UserRankInfo
     */
    public static function getUserRankInfo(
        \OmegaUp\DAO\VO\Identities $identity
    ) {
        $response = [
            'rank' => 0,
            'name' => strval($identity->name),
            'problems_solved' => 0,
            'author_ranking' => null,
        ];

        if (is_null($identity->user_id)) {
            return $response;
        }

        $userRank = \OmegaUp\DAO\UserRank::getByPK($identity->user_id);
        if (is_null($userRank)) {
            return $response;
        }

        return [
            'rank' => intval($userRank->ranking),
            'name' => strval($identity->name),
            'problems_solved' => $userRank->problems_solved_count,
            'author_ranking' => $userRank->author_ranking,
        ];
    }

    /**
     * Gets the best users of the current month
     *
     * @return list<CoderOfTheMonth>
     */
    public static function getTopCodersOfTheMonth(
        int $rowCount
    ): array {
        $currentDate = new \DateTime(date('Y-m-d', \OmegaUp\Time::get()));
        $firstDayOfNextMonth = $currentDate->modify('first day of next month');
        $date = $firstDayOfNextMonth->format('Y-m-d');
        return \OmegaUp\Cache::getFromCacheOrSet(
            \OmegaUp\Cache::CODERS_OF_THE_MONTH,
            "{$date}-{$rowCount}",
            fn () => \OmegaUp\DAO\CoderOfTheMonth::getCandidatesToCoderOfTheMonth(
                $date,
                'all',
                $rowCount
            ),
            60 * 60 * 12 // 12 hours
        );
    }

    /**
     * Get rank by problems solved logic. It has its own func so it can be
     * accesed internally without authentication.
     *
     * @return UserRank
     */
    public static function getRankByProblemsSolved(
        ?\OmegaUp\DAO\VO\Identities $loggedIdentity,
        string $filteredBy,
        int $offset,
        int $rowCount
    ): array {
        $selectedFilter = self::getSelectedFilter(
            $loggedIdentity,
            $filteredBy
        );
        $rankCacheName = "{$offset}-{$rowCount}-{$filteredBy}-{$selectedFilter['value']}";
        return \OmegaUp\Cache::getFromCacheOrSet(
            \OmegaUp\Cache::PROBLEMS_SOLVED_RANK,
            $rankCacheName,
            /**
             * @return UserRank
             */
            function () use (
                $loggedIdentity,
                $filteredBy,
                $offset,
                $rowCount
            ): array {
                $selectedFilter = self::getSelectedFilter(
                    $loggedIdentity,
                    $filteredBy
                );
                return \OmegaUp\DAO\UserRank::getFilteredRank(
                    $offset,
                    $rowCount,
                    'ranking',
                    'ASC',
                    $selectedFilter['filteredBy'],
                    $selectedFilter['value']
                );
            },
            APC_USER_CACHE_USER_RANK_TIMEOUT
        );
    }

    /**
     * Get authors rank.
     *
     * @return AuthorsRank
     */
    public static function getAuthorsRank(
        int $offset,
        int $rowCount
    ): array {
        return \OmegaUp\Cache::getFromCacheOrSet(
            \OmegaUp\Cache::AUTHORS_RANK,
            "{$offset}-{$rowCount}",
            fn () => \OmegaUp\DAO\UserRank::getAuthorsRank(
                $offset,
                $rowCount
            ),
            APC_USER_CACHE_USER_RANK_TIMEOUT
        );
    }

    /**
     * Get authors of quality problems
     *
     * @return AuthorsRankWithQualityProblems
     */
    public static function getAuthorsRankWithQualityProblems(
        int $offset,
        int $rowCount
    ): array {
        return \OmegaUp\Cache::getFromCacheOrSet(
            \OmegaUp\Cache::AUTHORS_RANK_WITH_QUALITY_PROBLEMS,
            "{$offset}-{$rowCount}",
            fn () => \OmegaUp\DAO\UserRank::getAuthorsRankWithQualityProblems(
                $offset,
                $rowCount
            ),
            APC_USER_CACHE_USER_RANK_TIMEOUT
        );
    }

    /**
     * Prepare all the properties to be sent to the
     * author rank table view via smarty.
     *
     * @return array{smartyProperties: array{payload: AuthorRankTablePayload, title: \OmegaUp\TranslationString}, entrypoint: string}
     *
     * @omegaup-request-param int|null $length
     * @omegaup-request-param int|null $page
     */
    public static function getAuthorRankForSmarty(\OmegaUp\Request $r) {
        $page = $r->ensureOptionalInt('page') ?? 1;
        $length = $r->ensureOptionalInt('length') ?? 100;

        $authorsRanking = self::getAuthorsRank(
            $page,
            $length
        );
        return [
            'smartyProperties' => [
                'payload' => [
                    'page' => $page,
                    'length' => $length,
                    'ranking' => $authorsRanking,
                    'pagerItems' => \OmegaUp\Pager::paginateWithUrl(
                        $authorsRanking['total'],
                        $length,
                        $page,
                        '/rank/authors/',
                        /*$adjacent=*/5,
                        /*$params=*/[]
                    ),
                ],
                'title' => new \OmegaUp\TranslationString(
                    'omegaupTitleAuthorsRank'
                ),
            ],
            'entrypoint' => 'authors_rank',
        ];
    }

    /**
     * Expires the known ranks
     *
     * @TODO: This should be called only in the grader->frontend callback and only IFF
     * verdict = AC (and not test run)
     *
     * @return void
     */
    public static function deleteProblemsSolvedRankCacheList(): void {
        \OmegaUp\Cache::invalidateAllKeys(\OmegaUp\Cache::PROBLEMS_SOLVED_RANK);
        \OmegaUp\Cache::invalidateAllKeys(
            \OmegaUp\Cache::CONTESTANT_SCOREBOARD_PREFIX
        );
        \OmegaUp\Cache::invalidateAllKeys(
            \OmegaUp\Cache::ADMIN_SCOREBOARD_PREFIX
        );
    }

    /**
     * Updates the main email of the current user
     *
     * @omegaup-request-param string $email
     *
     * @param \OmegaUp\Request $r
     *
     * @return array{status: string}
     */
    public static function apiUpdateMainEmail(\OmegaUp\Request $r): array {
        $r->ensureMainUserIdentity();

        $emailParam = $r->ensureString(
            'email',
            fn (string $email) => \OmegaUp\Validators::email($email)
        );

        try {
            \OmegaUp\DAO\DAO::transBegin();

            // Update email
            if (!is_null($r->user->main_email_id)) {
                $email = \OmegaUp\DAO\Emails::getByPK($r->user->main_email_id);
                if (!is_null($email)) {
                    $email->email = $emailParam;
                    \OmegaUp\DAO\Emails::update($email);
                }
            } else {
                $email = new \OmegaUp\DAO\VO\Emails([
                    'user_id' => $r->user->user_id,
                    'email' => $emailParam,
                ]);
                \OmegaUp\DAO\Emails::create($email);
                $r->user->main_email_id = $email->email_id;
                \OmegaUp\DAO\Users::update($r->user);
            }

            // Add verification_id if not there
            if ($r->user->verified == '0') {
                self::$log->info('User not verified.');

                if (is_null($r->user->verification_id)) {
                    self::$log->info(
                        'User does not have verification id. Generating.'
                    );

                    try {
                        $r->user->verification_id = \OmegaUp\SecurityTools::randomString(
                            50
                        );
                        \OmegaUp\DAO\Users::update($r->user);
                    } catch (\Exception $e) {
                        // best effort, eat exception
                    }
                }
            }

            \OmegaUp\DAO\DAO::transEnd();
        } catch (\Exception $e) {
            \OmegaUp\DAO\DAO::transRollback();
            if (\OmegaUp\DAO\DAO::isDuplicateEntryException($e)) {
                throw new \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException(
                    'mailInUse',
                    $e
                );
            }
            throw $e;
        }

        // Delete profile cache
        \OmegaUp\Cache::deleteFromCache(
            \OmegaUp\Cache::USER_PROFILE,
            $r->identity->username
        );

        // Send verification email
        self::sendVerificationEmail($r->user);

        return [
            'status' => 'ok',
        ];
    }

    public static function makeUsernameFromEmail(string $email): string {
        $positionSymbolAt = strpos($email, '@');
        if ($positionSymbolAt === false) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterInvalid',
                'email'
            );
        }
        $newUsername = substr($email, 0, $positionSymbolAt);
        $newUsername = str_replace('-', '_', $newUsername);
        $newUsername = str_replace('.', '_', $newUsername);
        return $newUsername . \OmegaUp\Time::get();
    }

    /**
     * Parses and validates a filter string to be used for event notification
     * filtering.
     *
     * The Request must have a 'filter' key with comma-delimited URI paths
     * representing the resources the caller is interested in receiving events
     * for. If the caller has enough privileges to receive notifications for
     * ALL the requested filters, the request will return successfully,
     * otherwise an exception will be thrown.
     *
     * This API does not need authentication to be used. This allows to track
     * contest updates with an access token.
     *
     * @return array{user: null|string, admin: bool, problem_admin: list<string>, contest_admin: list<string>, problemset_admin: list<int>}
     *
     * @omegaup-request-param null|string $auth_token
     * @omegaup-request-param null|string $contest_admin
     * @omegaup-request-param null|string $contest_alias
     * @omegaup-request-param string $filter
     * @omegaup-request-param int $problemset_id
     * @omegaup-request-param mixed $token
     * @omegaup-request-param mixed $tokens
     */
    public static function apiValidateFilter(\OmegaUp\Request $r): array {
        \OmegaUp\Validators::validateStringNonEmpty($r['filter'], 'filter');
        \OmegaUp\Validators::validateOptionalStringNonEmpty(
            $r['auth_token'],
            'auth_token'
        );

        $response = [
            'user' => null,
            'admin' => false,
            'problem_admin' => [],
            'contest_admin' => [],
            'problemset_admin' => [],
        ];

        $session = \OmegaUp\Controllers\Session::getCurrentSession(
            $r
        );
        $identity = $session['identity'];
        if (!is_null($identity)) {
            $response['user'] = $identity->username;
            $response['admin'] = $session['is_admin'];
        }

        $filters = explode(',', $r['filter']);
        foreach ($filters as $filter) {
            $tokens = explode('/', $filter);
            if (count($tokens) < 2 || $tokens[0] != '') {
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'parameterInvalid',
                    'filter'
                );
            }
            switch ($tokens[1]) {
                case 'all-events':
                    if (count($tokens) != 2) {
                        throw new \OmegaUp\Exceptions\InvalidParameterException(
                            'parameterInvalid',
                            'filter'
                        );
                    }
                    if (!$session['is_admin']) {
                        throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                            'userNotAllowed'
                        );
                    }
                    break;
                case 'user':
                    if (count($tokens) != 3) {
                        throw new \OmegaUp\Exceptions\InvalidParameterException(
                            'parameterInvalid',
                            'filter'
                        );
                    }
                    if (is_null($identity)) {
                        throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                            'userNotAllowed'
                        );
                    }
                    if ($tokens[2] != $identity->username && !$session['is_admin']) {
                        throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                            'userNotAllowed'
                        );
                    }
                    break;
                case 'contest':
                    if (count($tokens) < 3) {
                        throw new \OmegaUp\Exceptions\InvalidParameterException(
                            'parameterInvalid',
                            'filter'
                        );
                    }
                    $r2 = new \OmegaUp\Request([
                        'contest_alias' => $tokens[2],
                    ]);
                    if (isset($r['auth_token'])) {
                        $r2['auth_token'] = $r['auth_token'];
                    }
                    if (count($tokens) >= 4) {
                        $r2['token'] = $tokens[3];
                    }
                    $contestResponse = \OmegaUp\Controllers\Contest::validateDetails(
                        $r2
                    );
                    if ($contestResponse['contest_admin']) {
                        $response['contest_admin'][] = $contestResponse['contest_alias'];
                        $response['problemset_admin'][] = intval(
                            $contestResponse['contest']->problemset_id
                        );
                    }
                    break;
                case 'problemset':
                    if (count($tokens) < 3) {
                        throw new \OmegaUp\Exceptions\InvalidParameterException(
                            'parameterInvalid',
                            'filter'
                        );
                    }
                    [
                        'request' => $r2,
                    ] = \OmegaUp\Controllers\Problemset::wrapRequest(new \OmegaUp\Request([
                        'problemset_id' => $tokens[2],
                        'auth_token' => $r['auth_token'],
                        'tokens' => $tokens,
                    ]));
                    $contestAlias = $r2->ensureOptionalString(
                        'contest_alias',
                        /*$required=*/false,
                        fn (string $alias) => \OmegaUp\Validators::alias($alias)
                    );
                    if (
                        !empty($contestAlias) &&
                        ($r2->ensureOptionalBool('contest_admin') ?? false)
                    ) {
                        $response['contest_admin'][] = $contestAlias;
                        $response['problemset_admin'][] = intval($tokens[2]);
                    }
                    break;
                case 'problem':
                    if (count($tokens) != 3) {
                        throw new \OmegaUp\Exceptions\InvalidParameterException(
                            'parameterInvalid',
                            'filter'
                        );
                    }
                    $problem = \OmegaUp\DAO\Problems::getByAlias($tokens[2]);
                    if (is_null($problem)) {
                        throw new \OmegaUp\Exceptions\NotFoundException(
                            'problemNotFound'
                        );
                    }
                    if (
                        !is_null($identity) &&
                        \OmegaUp\Authorization::isProblemAdmin(
                            $identity,
                            $problem
                        )
                    ) {
                        $response['problem_admin'][] = $tokens[2];
                    } elseif (!\OmegaUp\DAO\Problems::isVisible($problem)) {
                        throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                            'problemIsPrivate'
                        );
                    }

                    break;
            }
        }

        return $response;
    }

    private static function validateAddRemoveRole(
        \OmegaUp\DAO\VO\Identities $identity,
        string $roleName
    ): \OmegaUp\DAO\VO\Roles {
        if (
            !\OmegaUp\Authorization::isSystemAdmin($identity) &&
            !OMEGAUP_ALLOW_PRIVILEGE_SELF_ASSIGNMENT
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'userNotAllowed'
            );
        }

        $role = \OmegaUp\DAO\Roles::getByName($roleName);
        if (
            $role->role_id === \OmegaUp\Authorization::ADMIN_ROLE
            && !OMEGAUP_ALLOW_PRIVILEGE_SELF_ASSIGNMENT
        ) {
            // System-admin role cannot be added/removed from the UI, only when
            // OMEGAUP_ALLOW_PRIVILEGE_SELF_ASSIGNMENT flag is on.
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'userNotAllowed'
            );
        }
        return $role;
    }

    private static function validateAddRemoveGroup(
        string $groupName
    ): \OmegaUp\DAO\VO\Groups {
        if (!OMEGAUP_ALLOW_PRIVILEGE_SELF_ASSIGNMENT) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'userNotAllowed'
            );
        }

        $group = \OmegaUp\DAO\Groups::getByName($groupName);
        if (is_null($group)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterNotFound',
                'group'
            );
        }
        return $group;
    }

    /**
     * Adds the role to the user.
     *
     * @return array{status: string}
     *
     * @omegaup-request-param string $role
     */
    public static function apiAddRole(\OmegaUp\Request $r): array {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();
        $r->ensureMainUserIdentity();
        \OmegaUp\Validators::validateStringNonEmpty($r['role'], 'role');

        $role = self::validateAddRemoveRole($r->identity, $r['role']);

        \OmegaUp\DAO\UserRoles::create(new \OmegaUp\DAO\VO\UserRoles([
            'user_id' => $r->user->user_id,
            'role_id' => $role->role_id,
            'acl_id' => \OmegaUp\Authorization::SYSTEM_ACL,
        ]));

        return [
            'status' => 'ok',
        ];
    }

    /**
     * Removes the role from the user.
     *
     * @return array{status: string}
     *
     * @omegaup-request-param string $role
     */
    public static function apiRemoveRole(\OmegaUp\Request $r): array {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();
        $r->ensureMainUserIdentity();
        \OmegaUp\Validators::validateStringNonEmpty($r['role'], 'role');

        $role = self::validateAddRemoveRole($r->identity, $r['role']);

        \OmegaUp\DAO\UserRoles::delete(new \OmegaUp\DAO\VO\UserRoles([
            'user_id' => $r->user->user_id,
            'role_id' => $role->role_id,
            'acl_id' => \OmegaUp\Authorization::SYSTEM_ACL,
        ]));

        return [
            'status' => 'ok',
        ];
    }

    /**
     * Adds the identity to the group.
     *
     * @return array{status: string}
     *
     * @omegaup-request-param string $group
     */
    public static function apiAddGroup(\OmegaUp\Request $r): array {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();
        $r->ensureMainUserIdentity();
        $groupAlias = $r->ensureString(
            'group',
            fn (string $alias) => \OmegaUp\Validators::namespacedAlias($alias)
        );
        $group = self::validateAddRemoveGroup($groupAlias);
        \OmegaUp\DAO\GroupsIdentities::create(
            new \OmegaUp\DAO\VO\GroupsIdentities([
                'identity_id' => $r->identity->identity_id,
                'group_id' => $group->group_id,
            ])
        );

        return [
            'status' => 'ok',
        ];
    }

    /**
     * Removes the user to the group.
     *
     * @return array{status: string}
     *
     * @omegaup-request-param string $group
     */
    public static function apiRemoveGroup(\OmegaUp\Request $r): array {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();
        $r->ensureMainUserIdentity();
        $groupAlias = $r->ensureString(
            'group',
            fn (string $alias) => \OmegaUp\Validators::namespacedAlias($alias)
        );
        $group = self::validateAddRemoveGroup($groupAlias);

        \OmegaUp\DAO\GroupsIdentities::delete(new \OmegaUp\DAO\VO\GroupsIdentities([
            'identity_id' => intval($r->identity->identity_id),
            'group_id' => $group->group_id
        ]));

        return [
            'status' => 'ok',
        ];
    }

    /**
     * Adds the experiment to the user.
     *
     * @omegaup-request-param string $experiment
     *
     * @return array{status: string}
     */
    public static function apiAddExperiment(\OmegaUp\Request $r): array {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();
        $r->ensureMainUserIdentity();
        if (!\OmegaUp\Authorization::isSystemAdmin($r->identity)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        \OmegaUp\DAO\UsersExperiments::create(new \OmegaUp\DAO\VO\UsersExperiments([
            'user_id' => $r->user->user_id,
            'experiment' => $r->ensureEnum(
                'experiment',
                \OmegaUp\Experiments::getInstance()->getAllKnownExperiments()
            ),
        ]));

        return ['status' => 'ok'];
    }

    /**
     * Removes the experiment from the user.
     *
     * @omegaup-request-param string $experiment
     *
     * @return array{status: string}
     */
    public static function apiRemoveExperiment(\OmegaUp\Request $r): array {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();
        $r->ensureMainUserIdentity();
        if (!\OmegaUp\Authorization::isSystemAdmin($r->identity)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        \OmegaUp\DAO\UsersExperiments::delete(
            $r->user->user_id,
            $r->ensureEnum(
                'experiment',
                \OmegaUp\Experiments::getInstance()->getAllKnownExperiments()
            ),
        );

        return ['status' => 'ok'];
    }

    /**
     * Gets the last privacy policy saved in the data base
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return array{policy_markdown: string, has_accepted: bool, git_object_id: string, statement_type: string}
     *
     * @omegaup-request-param string $username
     */
    public static function getPrivacyPolicy(\OmegaUp\Request $r): array {
        $r->ensureIdentity();

        /** @var \OmegaUp\DAO\VO\Identities */
        $identity = self::resolveTargetIdentity($r);

        $lang = 'es';
        if (
            $identity->language_id == \OmegaUp\Controllers\User::LANGUAGE_EN ||
            $identity->language_id == \OmegaUp\Controllers\User::LANGUAGE_PSEUDO
        ) {
            $lang = 'en';
        } elseif ($identity->language_id == \OmegaUp\Controllers\User::LANGUAGE_PT) {
            $lang = 'pt';
        }
        $latestStatement = \OmegaUp\DAO\PrivacyStatements::getLatestPublishedStatement();
        if (is_null($latestStatement)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'privacyStatementNotFound'
            );
        }
        return [
            'policy_markdown' => file_get_contents(
                sprintf(
                    "%s/privacy/privacy_policy/{$lang}.md",
                    strval(OMEGAUP_ROOT)
                )
            ) ?: '',
            'has_accepted' => \OmegaUp\DAO\PrivacyStatementConsentLog::hasAcceptedPrivacyStatement(
                intval($identity->identity_id),
                $latestStatement['privacystatement_id']
            ),
            'git_object_id' => $latestStatement['git_object_id'],
            'statement_type' => 'privacy_policy',
        ];
    }

    /**
     * @return array{filteredBy: ?string, value: null|string|int}
     */
    private static function getSelectedFilter(
        ?\OmegaUp\DAO\VO\Identities $identity,
        string $filteredBy
    ): array {
        if (is_null($identity)) {
            return ['filteredBy' => null, 'value' => null];
        }
        if ($filteredBy === 'country') {
            return [
                'filteredBy' => $filteredBy,
                'value' => $identity->country_id
            ];
        }
        if ($filteredBy === 'state') {
            return [
                'filteredBy' => $filteredBy,
                'value' => "{$identity->country_id}-{$identity->state_id}"
            ];
        }
        if ($filteredBy === 'school') {
            $schoolId = null;
            if (!is_null($identity->current_identity_school_id)) {
                $identitySchool = \OmegaUp\DAO\IdentitiesSchools::getByPK(
                    $identity->current_identity_school_id
                );
                if (!is_null($identitySchool)) {
                    $schoolId = $identitySchool->school_id;
                }
            }

            return [
                'filteredBy' => $filteredBy,
                'value' => $schoolId,
            ];
        }
        return ['filteredBy' => null, 'value' => null];
    }

    /**
     * Gets the last privacy policy accepted by user
     *
     * @param \OmegaUp\Request $r
     *
     * @return array{hasAccepted: bool}
     *
     * @omegaup-request-param string $username
     */
    public static function apiLastPrivacyPolicyAccepted(\OmegaUp\Request $r): array {
        $r->ensureIdentity();

        /** @var \OmegaUp\DAO\VO\Identities */
        $identity = self::resolveTargetIdentity($r);
        $latestStatement = \OmegaUp\DAO\PrivacyStatements::getLatestPublishedStatement();
        if (is_null($latestStatement)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'privacyStatementNotFound'
            );
        }
        return [
            'hasAccepted' => \OmegaUp\DAO\PrivacyStatementConsentLog::hasAcceptedPrivacyStatement(
                intval($identity->identity_id),
                $latestStatement['privacystatement_id']
            ),
        ];
    }

    /**
     * Keeps a record of a user who accepts the privacy policy
     *
     * @param \OmegaUp\Request $r
     *
     * @throws \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException
     *
     * @return array{status: string}
     *
     * @omegaup-request-param string $privacy_git_object_id
     * @omegaup-request-param string $statement_type
     * @omegaup-request-param string $username
     */
    public static function apiAcceptPrivacyPolicy(\OmegaUp\Request $r): array {
        $r->ensureIdentity();
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['privacy_git_object_id'],
            'privacy_git_object_id'
        );
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['statement_type'],
            'statement_type'
        );
        $privacystatementId = \OmegaUp\DAO\PrivacyStatements::getId(
            $r['privacy_git_object_id'],
            $r['statement_type']
        );
        if (is_null($privacystatementId)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'privacyStatementNotFound'
            );
        }
        /** @var \OmegaUp\DAO\VO\Identities */
        $identity = self::resolveTargetIdentity($r);

        try {
            \OmegaUp\DAO\PrivacyStatementConsentLog::saveLog(
                intval($identity->identity_id),
                $privacystatementId
            );
        } catch (\Exception $e) {
            if (\OmegaUp\DAO\DAO::isDuplicateEntryException($e)) {
                throw new \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException(
                    'userAlreadyAcceptedPrivacyPolicy',
                    $e
                );
            }
            throw $e;
        }
        \OmegaUp\Controllers\Session::invalidateCache();

        return ['status' => 'ok'];
    }

    /**
     * Associates an identity to the logged user given the username
     *
     * @param \OmegaUp\Request $r
     *
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     * @throws \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException
     *
     * @return array{status: string}
     *
     * @omegaup-request-param string $password
     * @omegaup-request-param string $username
     */
    public static function apiAssociateIdentity(\OmegaUp\Request $r): array {
        \OmegaUp\Experiments::getInstance()->ensureEnabled(
            \OmegaUp\Experiments::IDENTITIES
        );
        $r->ensureMainUserIdentity();

        \OmegaUp\Validators::validateStringNonEmpty($r['username'], 'username');
        \OmegaUp\Validators::validateStringNonEmpty($r['password'], 'password');

        $identity = \OmegaUp\DAO\Identities::getUnassociatedIdentity(
            $r['username']
        );
        if (is_null($identity) || is_null($identity->identity_id)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterInvalid',
                'username'
            );
        }

        if (
            \OmegaUp\DAO\Identities::isUserAssociatedWithIdentityOfGroup(
                intval(
                    $r->user->user_id
                ),
                intval(
                    $identity->identity_id
                )
            )
        ) {
            throw new \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException(
                'identityAlreadyAssociated'
            );
        }

        /** @var string $identity->password */
        $passwordCheck = \OmegaUp\SecurityTools::compareHashedStrings(
            $r['password'],
            $identity->password
        );

        if ($passwordCheck === false) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterInvalid',
                'password'
            );
        }

        \OmegaUp\DAO\Identities::associateIdentityWithUser(
            $r->user->user_id,
            $identity->identity_id
        );

        return ['status' => 'ok'];
    }

    /**
     * Get the identities that have been associated to the logged user
     *
     * @return array{identities: list<array{username: string, default: bool}>}
     */
    public static function apiListAssociatedIdentities(\OmegaUp\Request $r): array {
        \OmegaUp\Experiments::getInstance()->ensureEnabled(
            \OmegaUp\Experiments::IDENTITIES
        );
        $r->ensureMainUserIdentity();

        return [
            'identities' => \OmegaUp\DAO\Identities::getAssociatedIdentities(
                $r->user->user_id
            ),
        ];
    }

    /**
     * Generate a new gitserver token. This token can be used to authenticate
     * against the gitserver.
     *
     * @return array{token: string}
     */
    public static function apiGenerateGitToken(\OmegaUp\Request $r): array {
        $r->ensureMainUserIdentity();

        $token = \OmegaUp\SecurityTools::randomHexString(40);
        $r->user->git_token = \OmegaUp\SecurityTools::hashString($token);
        \OmegaUp\DAO\Users::update($r->user);

        return [
            'token' => $token,
        ];
    }

    /**
     * Returns true whether user is logged with the main identity
     * @param \OmegaUp\DAO\VO\Users $user
     * @param \OmegaUp\DAO\VO\Identities $identity
     * @return bool
     */
    public static function isMainIdentity(
        \OmegaUp\DAO\VO\Users $user,
        \OmegaUp\DAO\VO\Identities $identity
    ): bool {
        return $identity->identity_id == $user->main_identity_id;
    }

    /**
     * Prepare all the properties to be sent to the rank table view via smarty
     *
     * @return array{smartyProperties: array{payload: UserRankTablePayload, title: \OmegaUp\TranslationString}, entrypoint: string}
     *
     * @omegaup-request-param mixed $filter
     * @omegaup-request-param int $length
     * @omegaup-request-param int $page
     */
    public static function getRankForSmarty(\OmegaUp\Request $r) {
        $r->ensureOptionalInt('page');
        $r->ensureOptionalInt('length');
        \OmegaUp\Validators::validateOptionalInEnum(
            $r['filter'],
            'filter',
            ['', 'country', 'state', 'school']
        );

        $page = is_null($r['page']) ? 1 : intval($r['page']);
        $length = is_null($r['length']) ? 100 : intval($r['length']);
        $filter = strval($r['filter']);

        $availableFilters = [];

        $ranking = self::getRankByProblemsSolved(
            $r->identity,
            $filter,
            $page,
            $length
        );
        $response = [
            'smartyProperties' => [
                'payload' => [
                    'page' => $page,
                    'length' => $length,
                    'filter' => $filter,
                    'availableFilters' => $availableFilters,
                    'isIndex' => false,
                    'isLogged' => false,
                    'ranking' => $ranking,
                    'pagerItems' => \OmegaUp\Pager::paginateWithUrl(
                        $ranking['total'],
                        $length,
                        $page,
                        '/rank/',
                        /*$adjacent=*/5,
                        $filter === '' ? [] : [ 'filter' => $filter ]
                    ),
                ],
                'title' => new \OmegaUp\TranslationString(
                    'omegaupTitleUsersRank'
                )
            ],
            'entrypoint' => 'users_rank',
        ];

        try {
            $r->ensureIdentity();
        } catch (\OmegaUp\Exceptions\UnauthorizedException $e) {
            // Do nothing. Not logged user can access here
            return $response;
        }

        $response['smartyProperties']['payload']['isLogged'] = true;
        if (!is_null($r->identity->country_id)) {
            $availableFilters['country'] =
                \OmegaUp\Translations::getInstance()->get(
                    'wordsFilterByCountry'
                );
        }
        if (!is_null($r->identity->state_id)) {
            $availableFilters['state'] =
                \OmegaUp\Translations::getInstance()->get(
                    'wordsFilterByState'
                );
        }

        $schoolId = null;
        if (!is_null($r->identity->current_identity_school_id)) {
            $identitySchool = \OmegaUp\DAO\IdentitiesSchools::getByPK(
                $r->identity->current_identity_school_id
            );
            if (!is_null($identitySchool)) {
                $schoolId = $identitySchool->school_id;
            }
        }
        if (!is_null($schoolId)) {
            $availableFilters['school'] =
                \OmegaUp\Translations::getInstance()->get(
                    'wordsFilterBySchool'
                );
        }
        $response['smartyProperties']['payload']['availableFilters'] = $availableFilters;
        $response['smartyProperties']['payload']['ranking'] = self::getRankByProblemsSolved(
            $r->identity,
            $filter,
            $page,
            $length
        );
        return $response;
    }

    /**
     * @omegaup-request-param mixed $category
     * @omegaup-request-param null|string $date
     *
     * @return array{entrypoint: string, smartyProperties: array{fullWidth: bool, payload: IndexPayload, title: \OmegaUp\TranslationString}}
     */
    public static function getIndexDetailsForSmarty(\OmegaUp\Request $r) {
        try {
            $r->ensureIdentity();
        } catch (\OmegaUp\Exceptions\UnauthorizedException $e) {
            // Not logged, but there is no problem with this
            /**
             * @var null $r->identity
             * @var null $r->identity->username
             */
        }
        $date = $r->ensureOptionalString(
            'date',
            /*$required=*/false,
            fn (string $date): bool => \OmegaUp\Validators::stringNonEmpty(
                $date
            )
        );
        $firstDay = self::getCurrentMonthFirstDay($date);
        $rowCount = 5;

        \OmegaUp\Validators::validateOptionalInEnum(
            $r['category'],
            'category',
            \OmegaUp\Controllers\User::ALLOWED_CODER_OF_THE_MONTH_CATEGORIES
        );
        $category = $r['category'] ?? 'all';

        return [
            'smartyProperties' => [
                'payload' => [
                    'coderOfTheMonthData' => [
                        'all' => self::getCoderOfTheMonth(
                            $firstDay,
                            'all'
                        )['coderinfo'],
                        'female' => self::getCoderOfTheMonth(
                            $firstDay,
                            'female'
                        )['coderinfo']
                    ],
                    'schoolOfTheMonthData' => \OmegaUp\Controllers\School::getSchoolOfTheMonth()['schoolinfo'],
                    'userRank' => self::getTopCodersOfTheMonth(
                        $rowCount
                    ),
                    'schoolRank' => \OmegaUp\Controllers\School::getTopSchoolsOfTheMonth(
                        $rowCount
                    ),
                    'currentUserInfo' => (
                        !is_null($r->identity) &&
                        !is_null($r->identity->username)
                    ) ? [
                        'username' => $r->identity->username,
                    ] : [],
                ],
                'fullWidth' => true,
                'title' => new \OmegaUp\TranslationString(
                    'omegaupTitleCommonIndex'
                ),
            ],
            'entrypoint' => 'common_index',
        ];
    }

    /**
     * Prepare all the properties to be sent to the rank table view via smarty
     *
     * @omegaup-request-param mixed $category
     *
     * @return array{smartyProperties: array{payload: CoderOfTheMonthPayload, title: \OmegaUp\TranslationString}, entrypoint: string}
     */
    public static function getCoderOfTheMonthDetailsForSmarty(
        \OmegaUp\Request $r
    ): array {
        try {
            $r->ensureIdentity();
        } catch (\OmegaUp\Exceptions\UnauthorizedException $e) {
            // Do nothing. Not logged user can access here
            $r->identity = null;
        }
        $currentTimeStamp = \OmegaUp\Time::get();
        $currentDate = date('Y-m-d', $currentTimeStamp);
        $firstDayOfNextMonth = new \DateTime($currentDate);
        $firstDayOfNextMonth->modify('first day of next month');
        $dateToSelect = $firstDayOfNextMonth->format('Y-m-d');

        $isMentor = !is_null(
            $r->identity
        ) && \OmegaUp\Authorization::isMentor(
            $r->identity
        );

        \OmegaUp\Validators::validateOptionalInEnum(
            $r['category'],
            'category',
            \OmegaUp\Controllers\User::ALLOWED_CODER_OF_THE_MONTH_CATEGORIES
        );
        $category = $r['category'] ?? 'all';

        $candidates = \OmegaUp\DAO\CoderOfTheMonth::getCandidatesToCoderOfTheMonth(
            $dateToSelect,
            $category
        );
        $bestCoders = [];
        foreach ($candidates as $candidate) {
            /** @psalm-suppress InvalidArrayOffset Even though $candidate does have this index, psalm cannot see it :/ */
            unset($candidate['user_id']);
            $bestCoders[] = $candidate;
        }

        $response = [
            'codersOfCurrentMonth' => self::processCodersList(
                \OmegaUp\DAO\CoderOfTheMonth::getCodersOfTheMonth(
                    $category
                )
            ),
            'codersOfPreviousMonth' => self::processCodersList(
                \OmegaUp\DAO\CoderOfTheMonth::getMonthlyList(
                    $currentDate,
                    $category
                )
            ),
            'candidatesToCoderOfTheMonth' => $bestCoders,
            'isMentor' => $isMentor,
            'category' => $category,
        ];

        if (!$isMentor) {
            return [
                'smartyProperties' => [
                    'payload' => $response,
                    'title' => (
                        (strval($category) === 'female') ?
                        new \OmegaUp\TranslationString(
                            'omegaupTitleCodersofthemonthFemale'
                        ) :
                        new \OmegaUp\TranslationString(
                            'omegaupTitleCodersofthemonth'
                        )
                    ),
                ],
                'entrypoint' => 'coder_of_the_month',
            ];
        }

        $response['options'] = [
            'canChooseCoder' =>
                \OmegaUp\Authorization::canChooseCoderOrSchool(
                    $currentTimeStamp
                ),
            'coderIsSelected' =>
                !empty(
                    \OmegaUp\DAO\CoderOfTheMonth::getByTimeAndSelected(
                        $dateToSelect,
                        false,
                        $category
                    )
                ),
        ];
        return [
            'smartyProperties' => [
                'payload' => $response,
                'title' => (
                    (strval($category) === 'female') ?
                    new \OmegaUp\TranslationString(
                        'omegaupTitleCodersofthemonthFemale'
                    ) :
                    new \OmegaUp\TranslationString(
                        'omegaupTitleCodersofthemonth'
                    )
                ),
            ],
            'entrypoint' => 'coder_of_the_month',
        ];
    }

    /**
     * @return array{smartyProperties: array{STATUS_ERROR: string}|array{profile: UserProfileInfo}, template: string}
     *
     * @omegaup-request-param mixed $auth_token
     * @omegaup-request-param string $contest_alias
     * @omegaup-request-param null|string $token
     * @omegaup-request-param mixed $username
     */
    public static function getProfileDetailsForSmarty(\OmegaUp\Request $r) {
        self::authenticateOrAllowUnauthenticatedRequest($r);
        $identity = self::resolveTargetIdentity($r);
        if (is_null($identity)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterNotFound',
                'username'
            );
        }
        try {
            return [
                'smartyProperties' => [
                    'payload' => [
                        'profile' => self::getProfileDetails(
                            $r->identity,
                            $identity
                        ),
                        'contests' => self::apiContestStats($r)['contests'],
                        'solvedProblems' => self::apiProblemsSolved(
                            $r
                        )['problems'],
                        'unsolvedProblems' => self::apiListUnsolvedProblems(
                            $r
                        )['problems'],
                        'createdProblems' => self::apiProblemsCreated(
                            $r
                        )['problems'],
                        'stats' => self::apiStats($r),
                        'badges' => \OmegaUp\Controllers\Badge::getAllBadges(),
                        'ownedBadges' => (
                            is_null($r->user) ?
                            [] :
                            \OmegaUp\DAO\UsersBadges::getUserOwnedBadges(
                                $r->user
                            )
                        ),
                    ],
                    'title' => new \OmegaUp\TranslationString(
                        'omegaupTitleProfile'
                    ),
                ],
                'template' => 'user.profile.tpl',
            ];
        } catch (\OmegaUp\Exceptions\ApiException $e) {
            \OmegaUp\ApiCaller::logException($e);
            return [
                'smartyProperties' => [
                    'payload' => ['statusError' => $e->getErrorMessage()],
                    'title' => new \OmegaUp\TranslationString(
                        'omegaupTitleProfile'
                    )
                ],
                'template' => 'user.profile.tpl',
            ];
        }
    }

    /**
     * @omegaup-request-param mixed $username
     *
     * @return array{smartyProperties: array{STATUS_ERROR: string}|array{COUNTRIES: list<\OmegaUp\DAO\VO\Countries>, PROGRAMMING_LANGUAGES: array<string, string>, profile: UserProfileInfo}, template: string}
     */
    public static function getProfileEditDetailsForSmarty(\OmegaUp\Request $r) {
        try {
            self::authenticateOrAllowUnauthenticatedRequest($r);

            $identity = self::resolveTargetIdentity($r);
            if (is_null($identity)) {
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'parameterNotFound',
                    'Identity'
                );
            }
            $smartyProperties = [
                'profile' => self::getProfileDetails($r->identity, $identity),
                'PROGRAMMING_LANGUAGES' => \OmegaUp\Controllers\Run::SUPPORTED_LANGUAGES,
                'COUNTRIES' => \OmegaUp\DAO\Countries::getAll(
                    null,
                    100,
                    'name'
                ),
            ];
        } catch (\OmegaUp\Exceptions\ApiException $e) {
            \OmegaUp\ApiCaller::logException($e);
            $smartyProperties = [
                'STATUS_ERROR' => $e->getErrorMessage(),
            ];
        }
        $template = 'user.edit.tpl';
        if (is_null($r->identity) || is_null($r->identity->password)) {
            $template = 'user.basicedit.tpl';
        }
        return [
            'smartyProperties' => $smartyProperties,
            'template' => $template,
        ];
    }

    /**
     * @omegaup-request-param null|string $auth_token
     * @omegaup-request-param mixed $username
     *
     * @return array{smartyProperties: array{STATUS_ERROR?: string, payload?: array{email: null|string}, profile?: UserProfileInfo}, template: string}
     */
    public static function getEmailEditDetailsForSmarty(\OmegaUp\Request $r) {
        $currentSession = \OmegaUp\Controllers\Session::getCurrentSession();

        try {
            self::authenticateOrAllowUnauthenticatedRequest($r);

            $identity = self::resolveTargetIdentity($r);
            if (is_null($identity)) {
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'parameterNotFound',
                    'Identity'
                );
            }
            $smartyProperties = [
                'payload' => [
                    'email' => $currentSession['email'],
                ],
                'profile' => self::getProfileDetails(
                    $currentSession['identity'],
                    $identity
                ),
            ];
        } catch (\OmegaUp\Exceptions\ApiException $e) {
            \OmegaUp\ApiCaller::logException($e);
            $smartyProperties = [
                'STATUS_ERROR' => $e->getErrorMessage(),
            ];
        }
        return [
            'smartyProperties' => $smartyProperties,
            'template' => 'user.email.edit.tpl',
        ];
    }

    /**
     * @omegaup-request-param mixed $username
     *
     * @return array{smartyProperties: array{STATUS_ERROR?: string, admin?: true, practice?: false, profile?: UserProfileInfo}, template: string}
     */
    public static function getInterviewResultsDetailsForSmarty(\OmegaUp\Request $r) {
        try {
            self::authenticateOrAllowUnauthenticatedRequest($r);

            $identity = self::resolveTargetIdentity($r);
            if (is_null($identity)) {
                throw new \OmegaUp\Exceptions\InvalidParameterException(
                    'parameterNotFound',
                    'Identity'
                );
            }
            $smartyProperties = [
                'profile' => self::getProfileDetails(
                    $r->identity,
                    $identity
                ),
                'admin' => true,
                'practice' => false,
            ];
        } catch (\OmegaUp\Exceptions\ApiException $e) {
            \OmegaUp\ApiCaller::logException($e);
            $smartyProperties = [
                'STATUS_ERROR' => $e->getErrorMessage(),
            ];
        }
        return [
            'smartyProperties' => $smartyProperties,
            'template' => 'interviews.results.tpl',
        ];
    }

    /**
     * @return UserProfileInfo
     */
    private static function getProfileDetails(
        ?\OmegaUp\DAO\VO\Identities $loggedIdentity,
        \OmegaUp\DAO\VO\Identities $identity
    ) {
        $response = self::getUserProfile($loggedIdentity, $identity);
        if (
            !empty($response['graduation_date'])
            && is_object($response['graduation_date'])
        ) {
            $response['graduation_date'] = gmdate(
                'Y-m-d',
                $response['graduation_date']->time
            ) ?: null;
        }

        return $response;
    }

    /**
     * @param list<array{country_id: string, email: null|string, rank?: int, time: string, user_id?: int, username: string}> $coders
     *
     * @return CoderOfTheMonthList
     */
    private static function processCodersList(array $coders): array {
        $response = [];
        /** @var array{time: string, username: string, country_id: string, email: ?string} $coder */
        foreach ($coders as $coder) {
            $userInfo = \OmegaUp\DAO\Users::FindByUsername($coder['username']);
            if (is_null($userInfo)) {
                throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
            }
            $classname = \OmegaUp\DAO\Users::getRankingClassName(
                $userInfo->user_id
            );
            $hashEmail = md5($coder['email'] ?? '');
            $avatar = 'https://secure.gravatar.com/avatar/{$hashEmail}?s=32';
            $response[] = [
                'username' => $coder['username'],
                'country_id' => $coder['country_id'],
                'gravatar_32' => $avatar,
                'date' => $coder['time'],
                'classname' => $classname,
            ];
        }
        return $response;
    }

    /**
     * @throws \OmegaUp\Exceptions\NotFoundException
     */
    public static function isNonUserIdentity(
        \OmegaUp\DAO\VO\Identities $identity
    ): bool {
        if (is_null($identity->username)) {
            throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
        }
        return strpos($identity->username, ':') !== false;
    }
}

\OmegaUp\Controllers\User::$urlHelper = new \OmegaUp\UrlHelper();
