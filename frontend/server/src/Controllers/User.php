<?php

namespace OmegaUp\Controllers;

/**
 *  UserController
 *
 * @psalm-type PageItem=array{class: string, label: string, page: int, url?: string}
 * @psalm-type AuthorsRank=array{ranking: list<array{author_ranking: int|null, author_score: float, classname: string, country_id: null|string, name: null|string, username: string}>, total: int}
 * @psalm-type AuthorRankTablePayload=array{length: int, page: int, ranking: AuthorsRank, pagerItems: list<PageItem>}
 * @psalm-type Badge=array{assignation_time: \OmegaUp\Timestamp|null, badge_alias: string, first_assignation: \OmegaUp\Timestamp|null, owners_count: int, total_users: int}
 * @psalm-type AssociatedIdentity=array{username: string, default: bool}
 * @psalm-type CommonPayload=array{associatedIdentities: list<AssociatedIdentity>, currentEmail: string, currentName: null|string, currentUsername: string, gravatarURL128: string, gravatarURL51: string, isAdmin: bool, inContest: bool, isLoggedIn: bool, isMainUserIdentity: bool, isReviewer: bool, lockDownImage: string, navbarSection: string, omegaUpLockDown: bool, profileProgress: float, userClassname: string, userCountry: string, userTypes: list<string>}
 * @psalm-type UserRankInfo=array{name: string, problems_solved: int, rank: int, author_ranking: int|null}
 * @psalm-type UserRank=array{rank: list<array{classname: string, country_id: null|string, name: null|string, problems_solved: int, ranking: null|int, score: float, user_id: int, username: string}>, total: int}
 * @psalm-type Problem=array{title: string, alias: string, submissions: int, accepted: int, difficulty: float, quality_seal: bool}
 * @psalm-type UserProfile=array{birth_date: \OmegaUp\Timestamp|null, classname: string, country: string, country_id: null|string, email: null|string, gender: null|string, graduation_date: \OmegaUp\Timestamp|null, gravatar_92: string, has_competitive_objective: bool|null, has_learning_objective: bool|null, has_scholar_objective: bool|null, has_teaching_objective: bool|null, hide_problem_tags: bool, is_own_profile: bool, is_private: bool, locale: string, name: null|string, preferred_language: null|string, scholar_degree: null|string, school: null|string, school_id: int|null, state: null|string, state_id: null|string, username: null|string, verified: bool}
 * @psalm-type ListItem=array{key: string, value: string}
 * @psalm-type UserRankTablePayload=array{availableFilters: array{country?: null|string, school?: null|string, state?: null|string}, filter: string, isIndex: false, isLogged: bool, length: int, page: int, ranking: UserRank, pagerItems: list<PageItem>}
 * @psalm-type CoderOfTheMonth=array{category: string, classname: string, coder_of_the_month_id: int, country_id: string, description: null|string, problems_solved: int, ranking: int, school_id: int|null, score: float, selected_by: int|null, time: string, user_id: int, username: string}
 * @psalm-type CoderOfTheMonthList=list<array{username: string, country_id: string, gravatar_32: string, date: string, classname: string}>
 * @psalm-type IndexPayload=array{coderOfTheMonthData: array{all: UserProfile|null, female: UserProfile|null}, currentUserInfo: array{username?: string}, userRank: list<CoderOfTheMonth>, schoolOfTheMonthData: array{country_id: null|string, country: null|string, name: string, school_id: int, state: null|string}|null, schoolRank: list<array{name: string, ranking: int, school_id: int, school_of_the_month_id: int, score: float}>}
 * @psalm-type CoderOfTheMonthPayload=array{codersOfCurrentMonth: CoderOfTheMonthList, codersOfPreviousMonth: CoderOfTheMonthList, candidatesToCoderOfTheMonth: list<array{category: string, classname: string, coder_of_the_month_id: int, country_id: string, description: null|string, problems_solved: int, ranking: int, school_id: int|null, score: float, selected_by: int|null, time: string, username: string}>, isMentor: bool, category: string, options?: array{canChooseCoder: bool, coderIsSelected: bool}}
 * @psalm-type UserProfileInfo=array{birth_date?: \OmegaUp\Timestamp|null, classname: string, country: null|string, country_id: null|string, email?: null|string, gender?: null|string, graduation_date: \OmegaUp\Timestamp|null, gravatar_92: null|string, has_competitive_objective?: bool|null, has_learning_objective?: bool|null, has_scholar_objective?: bool|null, has_teaching_objective?: bool|null, hide_problem_tags: bool, is_own_profile: bool, is_private: bool, locale: null|string, name: null|string, preferred_language: null|string, rankinfo: array{author_ranking: int|null, name: null|string, problems_solved: int|null, rank: int|null}, scholar_degree: null|string, school: null|string, school_id: int|null, state: null|string, state_id: null|string, username: null|string, verified: bool|null, programming_languages: array<string,string>}
 * @psalm-type ContestParticipated=array{alias: string, title: string, start_time: \OmegaUp\Timestamp, finish_time: \OmegaUp\Timestamp, last_updated: \OmegaUp\Timestamp}
 * @psalm-type UserProfileContests=array<string, array{data: ContestParticipated, place: int}>
 * @psalm-type UserProfileStats=array{date: null|string, runs: int, verdict: string}
 * @psalm-type RunMetadata=array{verdict: string, time: float, sys_time: int, wall_time: float, memory: int}
 * @psalm-type CaseResult=array{contest_score: float, max_score: float, meta: RunMetadata, name: string, out_diff?: string, score: float, verdict: string}
 * @psalm-type Contest=array{acl_id?: int, admission_mode: string, alias: string, contest_id: int, description: string, feedback?: string, finish_time: \OmegaUp\Timestamp, languages?: null|string, last_updated: \OmegaUp\Timestamp, original_finish_time?: \OmegaUp\Timestamp, score_mode: string, penalty?: int, penalty_calc_policy?: string, penalty_type?: string, points_decay_factor?: float, problemset_id: int, recommended: bool, rerun_id: int|null, scoreboard?: int, scoreboard_url: string, scoreboard_url_admin: string, show_scoreboard_after?: int, start_time: \OmegaUp\Timestamp, submissions_gap?: int, title: string, urgent?: int, window_length: int|null}
 * @psalm-type Course=array{acl_id?: int, admission_mode: string, alias: string, archived: bool, course_id: int, description: string, finish_time?: \OmegaUp\Timestamp|null, group_id?: int, languages?: null|string, level?: null|string, minimum_progress_for_certificate?: int|null, name: string, needs_basic_information: bool, objective?: null|string, requests_user_information: string, school_id?: int|null, show_scoreboard: bool, start_time: \OmegaUp\Timestamp}
 * @psalm-type ExtraProfileDetails=array{contests: UserProfileContests, solvedProblems: list<Problem>, unsolvedProblems: list<Problem>, createdProblems: list<Problem>, createdContests: list<Contest>, createdCourses: list<Course>, stats: list<UserProfileStats>, badges: list<string>, ownedBadges: list<Badge>, hasPassword: bool}
 * @psalm-type CachedExtraProfileDetails=array{contests: UserProfileContests, solvedProblems: list<Problem>, unsolvedProblems: list<Problem>, createdProblems: list<Problem>, createdContests: list<Contest>, createdCourses: list<Course>, stats: list<UserProfileStats>, badges: list<string>}
 * @psalm-type UserProfileDetailsPayload=array{countries: list<\OmegaUp\DAO\VO\Countries>, identities: list<AssociatedIdentity>, programmingLanguages: array<string, string>, profile: UserProfileInfo, extraProfileDetails: ExtraProfileDetails|null}
 * @psalm-type ScoreboardRankingProblemDetailsGroup=array{cases: list<array{meta: RunMetadata}>}
 * @psalm-type ScoreboardRankingProblem=array{alias: string, penalty: float, percent: float, pending?: int, place?: int, points: float, run_details?: array{cases?: list<CaseResult>, details: array{groups: list<ScoreboardRankingProblemDetailsGroup>}}, runs: int}
 * @psalm-type ScoreboardRankingEntry=array{classname: string, country: string, is_invited: bool, name: null|string, place?: int, problems: list<ScoreboardRankingProblem>, total: array{penalty: float, points: float}, username: string}
 * @psalm-type Scoreboard=array{finish_time: \OmegaUp\Timestamp|null, problems: list<array{alias: string, order: int}>, ranking: list<ScoreboardRankingEntry>, start_time: \OmegaUp\Timestamp, time: \OmegaUp\Timestamp, title: string}
 * @psalm-type LoginDetailsPayload=array{facebookUrl?: string, statusError?: string, validateRecaptcha: bool, verifyEmailSuccessfully?: string}
 * @psalm-type Experiment=array{config: bool, hash: string, name: string}
 * @psalm-type UserRole=array{name: string}
 * @psalm-type UserDetailsPayload=array{emails: list<string>, experiments: list<string>, roleNames: list<UserRole>, systemExperiments: list<Experiment>, systemRoles: list<string>, username: string, verified: bool}
 * @psalm-type PrivacyPolicyDetailsPayload=array{policy_markdown: string, has_accepted: bool, git_object_id: string, statement_type: string}
 * @psalm-type EmailEditDetailsPayload=array{email: null|string, profile?: UserProfileInfo}
 * @psalm-type UserRolesPayload=array{username: string, userSystemRoles: array<int, array{name: string, value: bool}>, userSystemGroups: array<int, array{name: string, value: bool}>}
 * @psalm-type VerificationParentalTokenDetailsPayload=array{hasParentalVerificationToken: bool}
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

    // User types
    const USER_TYPE_STUDENT = 'student';
    const USER_TYPE_CONTESTANT = 'contestant';
    const USER_TYPE_TEACHER = 'teacher';
    const USER_TYPE_COACH = 'coach';
    const USER_TYPE_SELF_TAUGHT = 'self-taught';
    const USER_TYPE_INDEPENDENT_TEACHER = 'independent-teacher';
    const USER_TYPE_CURIOUS = 'curious';

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
            ignorePassword: false,
            forceVerification: false
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
        if (!is_null($createUserParams->email)) {
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
            'birth_date' => \OmegaUp\DAO\DAO::toMySQLTimestamp(
                intval(
                    $createUserParams->birthDate
                )
            ),
        ];
        if (
            $createUserParams->birthDate >= strtotime(
                '-13 year',
                \OmegaUp\Time::get()
            )
            && !is_null($createUserParams->parentEmail)
        ) {
            // Fill all the columns refering to user's parent
            $userData['parental_verification_token'] = \OmegaUp\SecurityTools::randomHexString(
                25
            );
            $userData['creation_timestamp'] = \OmegaUp\Time::get();
            $userData['parent_email_verification_initial'] = \OmegaUp\Time::get();
            $userData['parent_email_verification_deadline'] = strtotime(
                '+7 days',
                \OmegaUp\Time::get()
            );

            $subject = \OmegaUp\Translations::getInstance()->get(
                'parentEmailSubject'
            );
            $body = \OmegaUp\ApiUtils::formatString(
                \OmegaUp\Translations::getInstance()->get('parentEmailBody'),
                [
                    'parental_verification_token' => $userData['parental_verification_token'],
                ]
            );

            \OmegaUp\Email::sendEmail(
                [$createUserParams->parentEmail],
                $subject,
                $body
            );
        }

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
            $resultAsJson = json_decode($result, associative: true);
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

        $email = null;
        if (!is_null($createUserParams->email)) {
            $email = new \OmegaUp\DAO\VO\Emails([
                'email' => $createUserParams->email,
            ]);
        }

        // Save objects into DB
        try {
            \OmegaUp\DAO\DAO::transBegin();

            \OmegaUp\DAO\Users::create($user);

            if (!is_null($email)) {
                $email->user_id = $user->user_id;
                \OmegaUp\DAO\Emails::create($email);
                if (empty($email->email_id)) {
                    throw new \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException(
                        'mailInUse'
                    );
                }
                $user->main_email_id = $email->email_id;
            }

            $identity->user_id = $user->user_id;
            \OmegaUp\DAO\Identities::create($identity);
            $user->main_identity_id = $identity->identity_id;

            \OmegaUp\DAO\Users::update($user);

            if ($user->verified) {
                self::$log->info(
                    "Identity {$identity->username} created, trusting e-mail"
                );
            } elseif (is_null($createUserParams->parentEmail)) {
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
            self::$log->warning('Email lookup failed', ['exception' => $e]);
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
            required: false,
            validator: fn (string $password) => \OmegaUp\Validators::stringNonEmpty(
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
     * @return array{entrypoint: string, templateProperties: array{payload: LoginDetailsPayload, title: \OmegaUp\TranslationString}}
     *
     * @omegaup-request-param string $id
     */
    public static function getLoginDetailsViaVerifyEmailForTypeScript(
        \OmegaUp\Request $r
    ): array {
        $response = [
            'templateProperties' => [
                'payload' => [
                    'validateRecaptcha' => boolval(OMEGAUP_VALIDATE_CAPTCHA),
                    'verifyEmailSuccessfully' => \OmegaUp\Translations::getInstance()->get(
                        'verificationEmailSuccesfully'
                    ),
                ],
                'title' => new \OmegaUp\TranslationString('omegaupTitleLogin'),
            ],
            'entrypoint' => 'login_signin',
        ];
        try {
            $id = $r->ensureString('id');

            $user = \OmegaUp\DAO\Users::getByVerification($id);

            self::verifyEmail($user);
        } catch (\OmegaUp\Exceptions\ApiException $e) {
            \OmegaUp\ApiCaller::logException($e);
            $response['templateProperties']['payload'] = [
                'validateRecaptcha' => boolval(OMEGAUP_VALIDATE_CAPTCHA),
                'statusError' => $e->getErrorMessage(),
            ];
        } finally {
            return $response;
        }
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
        $usernameOrEmail = $r->ensureOptionalString(
            'usernameOrEmail',
            required: false,
            validator: fn (string $username) => \OmegaUp\Validators::usernameOrEmail(
                $username
            )
        );

        if (!is_null($usernameOrEmail)) {
            // Admin can override verification by sending username
            $r->ensureIdentity();

            if (!\OmegaUp\Authorization::isSupportTeamMember($r->identity)) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException();
            }

            self::$log->info("Admin verifying user... {$usernameOrEmail}");
            $user = self::resolveUser($usernameOrEmail);
        } else {
            // Normal user verification path
            $id = $r->ensureString('id');
            $user = \OmegaUp\DAO\Users::getByVerification($id);
        }

        self::verifyEmail($user);

        return ['status' => 'ok'];
    }

    /**
     * @throws \OmegaUp\Exceptions\NotFoundException
     */
    private static function verifyEmail(
        ?\OmegaUp\DAO\VO\Users $user
    ): void {
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
            'has_competitive_objective' => $user->has_competitive_objective,
            'has_learning_objective' => $user->has_learning_objective,
            'has_scholar_objective' => $user->has_scholar_objective,
            'has_teaching_objective' => $user->has_teaching_objective,
            'hide_problem_tags' => is_null(
                $user->hide_problem_tags
            ) ? false : $user->hide_problem_tags,
            'is_own_profile' => false,
        ];

        $userDb = \OmegaUp\DAO\Users::getExtendedProfileDataByPk(
            intval($user->user_id)
        );
        if (is_null($userDb)) {
            throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
        }

        $response['graduation_date'] = $userDb['graduation_date'];
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
     * @omegaup-request-param null|string $username
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
     * When the user is not allowed to see the information of another user
     * we just need to send an array with almost empty profile
     *
     * @return UserProfileInfo
     */
    private static function getPrivateUserProfile(
        \OmegaUp\DAO\VO\Identities $identity
    ) {
        // We need the email to get the gravatar, even though it will be not
        // published
        $userData = \OmegaUp\DAO\Users::getBasicProfileDataByPk(
            $identity->user_id
        );
        $hashedEmail = md5($userData['email'] ?? '');

        return [
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
            'country_id' => $userData['country_id'],
            'classname' => $userData['classname'],
            'programming_languages' => \OmegaUp\Controllers\Run::SUPPORTED_LANGUAGES,
            'email' => null,
            'gender' => null,
            'graduation_date' => null,
            'gravatar_92' => "https://secure.gravatar.com/avatar/{$hashedEmail}?s=92",
            'has_competitive_objective' => null,
            'has_learning_objective' => null,
            'has_scholar_objective' => null,
            'has_teaching_objective' => null,
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
            'is_own_profile' => false,
        ];
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
        $user = null;
        if (!is_null($identity->user_id)) {
            $user = \OmegaUp\DAO\Users::getByPK($identity->user_id);
        }

        if (
            self::shouldUserInformationBeHidden(
                $loggedIdentity,
                $identity,
                $user
            )
        ) {
            return self::getPrivateUserProfile($identity);
        }
        $response = \OmegaUp\Controllers\Identity::getProfile(
            $loggedIdentity,
            $identity,
            $user,
            $omitRank,
            $category
        );
        return array_merge(
            $response,
            [
                'classname' => \OmegaUp\DAO\Users::getRankingClassName(
                    $identity->user_id
                ),
                'programming_languages' => \OmegaUp\Controllers\Run::SUPPORTED_LANGUAGES
            ]
        );
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
     * - birth date to verify the user identity
     *
     * @omegaup-request-param string $email
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     *
     * @return array{birth_date: \OmegaUp\Timestamp|null, last_login: \OmegaUp\Timestamp|null, username: string, verified: bool, within_last_day: bool}
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

        // But avoid divulging the email and user's objectives in the response.
        unset($response['coderinfo']['email']);
        unset($response['coderinfo']['has_learning_objective']);
        unset($response['coderinfo']['has_teaching_objective']);
        unset($response['coderinfo']['has_scholar_objective']);
        unset($response['coderinfo']['has_competitive_objective']);

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
            autoselected: false,
            category: $category,
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
            foreach ($users as $_index => $user) {
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
     * Get Contests which a certain user has participated in
     *
     * @return array{contests: UserProfileContests}
     *
     * @omegaup-request-param null|string $username
     */
    public static function apiContestStats(\OmegaUp\Request $r): array {
        self::authenticateOrAllowUnauthenticatedRequest($r);

        $identity = self::resolveTargetIdentity($r);
        if (
            is_null($identity) ||
            is_null($identity->identity_id) ||
            is_null($identity->username)
        ) {
            throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
        }

        return [
            'contests' => self::getContestStats($identity),
        ];
    }

    /**
     * @return UserProfileContests
     */
    private static function getContestStats(
        \OmegaUp\DAO\VO\Identities $identity
    ): array {
        // Get contests where identity had at least 1 run
        $contestsParticipated = \OmegaUp\DAO\Contests::getContestsParticipated(
            intval($identity->identity_id)
        );

        /** @var UserProfileContests */
        $contests = [];

        foreach ($contestsParticipated as $contestProblemset) {
            if (
                is_null($contestProblemset['contest']->alias)
                || is_null($contestProblemset['contest']->title)
            ) {
                throw new \OmegaUp\Exceptions\NotFoundException(
                    'contestNotFound'
                );
            }
            // Get identity ranking
            $scoreboardResponse = \OmegaUp\Controllers\Contest::getScoreboardForUserProfile(
                $contestProblemset['contest'],
                $contestProblemset['problemset'],
                $identity
            );
            if (is_null($scoreboardResponse)) {
                continue;
            }
            $contest = [
                'alias' => $contestProblemset['contest']->alias,
                'title' => $contestProblemset['contest']->title,
                'start_time' => $contestProblemset['contest']->start_time,
                'finish_time' => $contestProblemset['contest']->finish_time,
                'last_updated' => $contestProblemset['contest']->last_updated,
            ];

            $contests[$contest['alias']] = [
                'data' => $contest,
                'place' => 0,
            ];

            // Grab the place of the current identity in the given contest
            foreach ($scoreboardResponse['ranking'] as $identityData) {
                if (
                    $identityData['username'] == strval($identity->username) &&
                    isset($identityData['place'])
                ) {
                    $contests[$contest['alias']]['place'] = $identityData['place'];
                    break;
                }
            }
        }

        return $contests;
    }

    /**
     * Get Problems solved by user
     *
     * @omegaup-request-param null|string $username
     *
     * @return array{problems: list<Problem>}
     */
    public static function apiProblemsSolved(\OmegaUp\Request $r): array {
        self::authenticateOrAllowUnauthenticatedRequest($r);

        $identity = self::resolveTargetIdentity($r);
        if (is_null($identity) || is_null($identity->identity_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
        }

        return [
            'problems' => self::getSolvedProblems($identity->identity_id),
        ];
    }

    /**
     * @return list<Problem>
     */
    private static function getSolvedProblems(int $identityId): array {
        $problems = \OmegaUp\DAO\Problems::getProblemsSolved($identityId);

        /** @var list<Problem> */
        $responseProblems = [];
        $relevantColumns = ['title', 'alias', 'submissions', 'accepted', 'difficulty', 'quality_seal'];
        foreach ($problems as $problem) {
            if (!\OmegaUp\DAO\Problems::isVisible($problem)) {
                continue;
            }
            /** @var Problem */
            $responseProblems[] = $problem->asFilteredArray($relevantColumns);
        }
        return $responseProblems;
    }

    /**
     * Get Problems unsolved by user
     *
     * @omegaup-request-param null|string $username
     *
     * @return array{problems: list<Problem>}
     */
    public static function apiListUnsolvedProblems(\OmegaUp\Request $r): array {
        self::authenticateOrAllowUnauthenticatedRequest($r);

        $identity = self::resolveTargetIdentity($r);
        if (is_null($identity) || is_null($identity->identity_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
        }

        return [
            'problems' => self::getUnsolvedProblems($identity->identity_id),
        ];
    }

    /**
     * @return list<Problem>
     */
    private static function getUnsolvedProblems(int $identityId): array {
        $problems = \OmegaUp\DAO\Problems::getProblemsUnsolvedByIdentity(
            $identityId
        );

        $relevantColumns = ['title', 'alias', 'submissions', 'accepted', 'difficulty', 'quality_seal'];
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
        return $filteredProblems;
    }

    /**
     * Get Problems created by user
     *
     * @omegaup-request-param null|string $username
     *
     * @return array{problems: list<Problem>}
     */
    public static function apiProblemsCreated(\OmegaUp\Request $r): array {
        self::authenticateOrAllowUnauthenticatedRequest($r);

        $identity = self::resolveTargetIdentity($r);
        if (is_null($identity) || is_null($identity->identity_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
        }

        return [
            'problems' => self::getCreatedProblems($identity->identity_id),
        ];
    }

    /**
     * @return list<Problem>
     */
    private static function getCreatedProblems(int $identityId): array {
        $relevantColumns = ['title', 'alias', 'submissions', 'accepted', 'difficulty', 'quality_seal'];
        /** @var list<Problem> */
        $filteredProblems = [];
        foreach (
            \OmegaUp\DAO\Problems::getPublicProblemsCreatedByIdentity(
                $identityId
            ) as $problem
        ) {
            if (\OmegaUp\DAO\Problems::isVisible($problem)) {
                /** @var Problem */
                $filteredProblems[] = $problem->asFilteredArray(
                    $relevantColumns
                );
            }
        }
        return $filteredProblems;
    }

    /**
     * Gets a list of users.
     *
     * @omegaup-request-param null|string $query
     * @omegaup-request-param null|int $rowcount
     * @omegaup-request-param null|string $term
     *
     * @return array{results: list<ListItem>}
     */
    public static function apiList(\OmegaUp\Request $r): array {
        $term = $r->ensureOptionalString(
            'term',
            required: false,
            validator: fn (string $term) => \OmegaUp\Validators::stringNonEmpty(
                $term
            )
        );
        $query = $r->ensureOptionalString(
            'query',
            required: false,
            validator: fn (string $query) => \OmegaUp\Validators::stringNonEmpty(
                $query
            )
        );
        $rowcount = $r->ensureOptionalInt('rowcount') ?? 100;
        $param = $term ?? $query;
        if (is_null($param)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterEmpty',
                'query'
            );
        }

        return [
            'results' => \OmegaUp\DAO\Identities::findByUsernameOrName(
                $param,
                $rowcount
            ),
        ];
    }

    /**
     * Get stats
     *
     * @omegaup-request-param null|string $username
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return array{runs: list<UserProfileStats>}
     */
    public static function apiStats(\OmegaUp\Request $r): array {
        self::authenticateOrAllowUnauthenticatedRequest($r);
        $identity = self::resolveTargetIdentity($r);
        if (is_null($identity) || is_null($identity->identity_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
        }
        $user = null;
        if (!is_null($identity->user_id)) {
            $user = \OmegaUp\DAO\Users::getByPK($identity->user_id);
        }

        if (
            self::shouldUserInformationBeHidden($r->identity, $identity, $user)
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'userProfileIsPrivate'
            );
        }

        return [
            'runs' => \OmegaUp\DAO\Runs::countRunsOfIdentityPerDatePerVerdict(
                $identity->identity_id
            ),
        ];
    }

    /**
     * It will return a boolean value indicating whether user can see
     * information of the given identity. There are three scenarios where this
     * function returns false:
     *  - Logged user is a sysadmin.
     *  - Logged user is trying to see their own information.
     *  - The target identity has a user associated to it, and they have not
     *    marked their information as private.
     */
    private static function shouldUserInformationBeHidden(
        ?\OmegaUp\DAO\VO\Identities $loggedIdentity,
        \OmegaUp\DAO\VO\Identities $identity,
        ?\OmegaUp\DAO\VO\Users $user
    ): bool {
        return (
            is_null($loggedIdentity)
            || (
                $loggedIdentity->username !== $identity->username
                && !\OmegaUp\Authorization::isSystemAdmin($loggedIdentity)
            )
        )
        && !is_null($user)
        && boolval($user->is_private);
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
     * @omegaup-request-param bool|null $has_competitive_objective
     * @omegaup-request-param bool|null $has_learning_objective
     * @omegaup-request-param bool|null $has_scholar_objective
     * @omegaup-request-param bool|null $has_teaching_objective
     * @omegaup-request-param bool|null $hide_problem_tags
     * @omegaup-request-param bool|null $is_private
     * @omegaup-request-param string $locale
     * @omegaup-request-param null|string $name
     * @omegaup-request-param null|string $scholar_degree
     * @omegaup-request-param int|null $school_id
     * @omegaup-request-param null|string $school_name
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

        $schoolId = $r->ensureOptionalInt('school_id');
        if (!is_null($schoolId)) {
            $school = \OmegaUp\DAO\Schools::getByPK($schoolId);
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

        $schoolName = $r->ensureOptionalString('school_name');
        if (is_null($newSchoolId) && !is_null($schoolName)) {
            $response = \OmegaUp\Controllers\School::apiCreate(
                new \OmegaUp\Request([
                    'name' => $schoolName,
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
        $r->ensureOptionalBool('has_competitive_objective');
        $r->ensureOptionalBool('has_learning_objective');
        $r->ensureOptionalBool('has_scholar_objective');
        $r->ensureOptionalBool('has_teaching_objective');
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
            'has_competitive_objective',
            'has_learning_objective',
            'has_scholar_objective',
            'has_teaching_objective',
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
     * @return AuthorsRank
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
     * author rank table view via TypeScript.
     *
     * @return array{templateProperties: array{payload: AuthorRankTablePayload, title: \OmegaUp\TranslationString}, entrypoint: string}
     *
     * @omegaup-request-param int|null $length
     * @omegaup-request-param int|null $page
     */
    public static function getAuthorRankForTypeScript(\OmegaUp\Request $r) {
        $page = $r->ensureOptionalInt('page') ?? 1;
        $length = $r->ensureOptionalInt('length') ?? 100;

        $authorsRanking = self::getAuthorsRank(
            $page,
            $length
        );
        return [
            'templateProperties' => [
                'payload' => [
                    'page' => $page,
                    'length' => $length,
                    'ranking' => $authorsRanking,
                    'pagerItems' => \OmegaUp\Pager::paginateWithUrl(
                        $authorsRanking['total'],
                        $length,
                        $page,
                        '/rank/authors/',
                        adjacent: 5,
                        params: []
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
     * @omegaup-request-param null|string $originalEmail
     *
     * @param \OmegaUp\Request $r
     *
     * @return array{status: string}
     */
    public static function apiUpdateMainEmail(\OmegaUp\Request $r): array {
        $r->ensureMainUserIdentity();

        $user = $r->user;
        $originalEmail = $r->ensureOptionalString(
            'originalEmail',
            required: false,
            validator: fn (string $originalEmail) => \OmegaUp\Validators::email(
                $originalEmail
            )
        );

        if (!is_null($originalEmail)) {
            // Only users with privileges of support team can update the email
            // on behalf to another user
            if (!\OmegaUp\Authorization::isSupportTeamMember($r->identity)) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                    'userNotAllowed'
                );
            }

            $user = \OmegaUp\DAO\Users::findByEmail($originalEmail);
            if (is_null($user) || is_null($user->main_identity_id)) {
                throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
            }
            if (
                is_null(
                    \OmegaUp\DAO\Identities::getByPK(
                        $user->main_identity_id
                    )
                )
            ) {
                throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
            }
        }

        $emailParam = $r->ensureString(
            'email',
            fn (string $email) => \OmegaUp\Validators::email($email)
        );

        try {
            \OmegaUp\DAO\DAO::transBegin();

            // Update email
            if (!is_null($user->main_email_id)) {
                $email = \OmegaUp\DAO\Emails::getByPK($user->main_email_id);
                if (!is_null($email)) {
                    $email->email = $emailParam;
                    \OmegaUp\DAO\Emails::update($email);
                }
            } else {
                $email = new \OmegaUp\DAO\VO\Emails([
                    'user_id' => $user->user_id,
                    'email' => $emailParam,
                ]);
                \OmegaUp\DAO\Emails::create($email);
                $user->main_email_id = $email->email_id;
                \OmegaUp\DAO\Users::update($user);
            }

            // Add verification_id if not there
            if (!$user->verified) {
                self::$log->info('User not verified.');

                if (is_null($user->verification_id)) {
                    self::$log->info(
                        'User does not have verification id. Generating.'
                    );

                    try {
                        $user->verification_id = \OmegaUp\SecurityTools::randomString(
                            50
                        );
                        \OmegaUp\DAO\Users::update($user);
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

        if (!is_null($originalEmail)) {
            // Delete profile cache, no needed for support team members
            \OmegaUp\Cache::deleteFromCache(
                \OmegaUp\Cache::USER_PROFILE,
                $r->identity->username
            );
        }

        // Send verification email
        self::sendVerificationEmail($user);

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
     * @omegaup-request-param null|string $token
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
                    $token = null;
                    if (count($tokens) >= 4) {
                        $token = $tokens[3];
                        $r2['token'] = $token;
                    }
                    $contestResponse = \OmegaUp\Controllers\Contest::validateDetails(
                        $tokens[2],
                        $identity,
                        $token
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
                        required: false,
                        validator: fn (string $alias) => \OmegaUp\Validators::alias(
                            $alias
                        )
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

    /**
     * Gets the list of roles assigned to logged user
     *
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     *
     * @return array{entrypoint: string, templateProperties: array{payload: UserRolesPayload, title: \OmegaUp\TranslationString}}
     */
    public static function getUserRolesForTypeScript(\OmegaUp\Request $r) {
        if (!OMEGAUP_ALLOW_PRIVILEGE_SELF_ASSIGNMENT) {
            throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
        }

        $r->ensureMainUserIdentity();

        $systemRoles = \OmegaUp\DAO\UserRoles::getSystemRoles(
            $r->user->user_id
        );
        $roles = \OmegaUp\DAO\Roles::getAll();
        $systemGroups = \OmegaUp\DAO\UserRoles::getSystemGroups(
            $r->identity->identity_id
        );
        $groups = \OmegaUp\DAO\Groups::searchByName('omegaup:');
        $userSystemRoles = [];
        $userSystemGroups = [];
        foreach ($roles as $key => $role) {
            $userSystemRoles[$key] = [
                'name' => strval($role->name),
                'value' => in_array($role->name, $systemRoles),
            ];
        }
        foreach ($groups as $key => $group) {
            $userSystemGroups[$key] = [
                'name' => strval($group->name),
                'value' => in_array($group->name, $systemGroups),
            ];
        }

        return [
            'templateProperties' => [
                'payload' => [
                    'userSystemRoles' => $userSystemRoles,
                    'userSystemGroups' => $userSystemGroups,
                    'username' => $r->identity->username,
                ],
                'title' => new \OmegaUp\TranslationString(
                    'omegaupTitleUpdatePrivileges'
                )
            ],
            'entrypoint' => 'admin_roles',
        ];
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
     * @omegaup-request-param string $username
     */
    public static function apiAddRole(\OmegaUp\Request $r): array {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();
        $r->ensureMainUserIdentity();

        $role = $r->ensureString('role');
        $role = self::validateAddRemoveRole($r->identity, $role);
        $r->ensureString(
            'username',
            fn (string $username) => \OmegaUp\Validators::usernameOrEmail(
                $username
            )
        );
        $user = self::resolveTargetUser($r);
        if (is_null($user) || is_null($user->user_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
        }

        try {
            \OmegaUp\DAO\UserRoles::create(new \OmegaUp\DAO\VO\UserRoles([
                'user_id' => $user->user_id,
                'role_id' => $role->role_id,
                'acl_id' => \OmegaUp\Authorization::SYSTEM_ACL,
            ]));
        } catch (\Exception $e) {
            if (\OmegaUp\DAO\DAO::isDuplicateEntryException($e)) {
                throw new \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException(
                    'userAlreadyHasSelectedRole',
                    $e
                );
            }
            throw $e;
        }

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
     * @omegaup-request-param string $username
     */
    public static function apiRemoveRole(\OmegaUp\Request $r): array {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();
        $r->ensureMainUserIdentity();

        $role = $r->ensureString('role');
        $role = self::validateAddRemoveRole($r->identity, $role);
        $r->ensureString(
            'username',
            fn (string $username) => \OmegaUp\Validators::usernameOrEmail(
                $username
            )
        );
        $user = self::resolveTargetUser($r);
        if (is_null($user) || is_null($user->user_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
        }

        \OmegaUp\DAO\UserRoles::delete(new \OmegaUp\DAO\VO\UserRoles([
            'user_id' => $user->user_id,
            'role_id' => $role->role_id,
            'acl_id' => \OmegaUp\Authorization::SYSTEM_ACL,
        ]));

        return [
            'status' => 'ok',
        ];
    }

    /**
     * @return array{token: string}
     *
     * @omegaup-request-param null|string $username
     */
    public static function apiDeleteRequest(\OmegaUp\Request $r): array {
        $r->ensureMainUserIdentity();
        $username = $r->ensureOptionalString(
            'username',
            required: false,
            validator: fn (string $username) => \OmegaUp\Validators::usernameOrEmail(
                $username
            )
        );
        if (
            !\OmegaUp\Authorization::isSystemAdmin(
                $r->identity
            ) && !is_null(
                $username
            )
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }
        $identity = self::resolveTargetIdentity($r);
        $user = self::resolveTargetUser($r);
        if (is_null($user) || is_null($identity)) {
            throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
        }
        $token = \OmegaUp\SecurityTools::randomString(50);
        \OmegaUp\DAO\Users::generateDeletionToken($user, $token);
        self::$log->info(
            "User {$identity->username} is requesting to delete their account."
        );
        if (is_null($user->main_email_id)) {
            return [
                'token' => $token,
            ];
        }
        $email = \OmegaUp\DAO\Emails::getByPK($user->main_email_id);

        if (is_null($email) || is_null($email->email)) {
            return [
                'token' => $token,
            ];
        }
        $subject = \OmegaUp\Translations::getInstance()->get(
            'accountDeletionRequestEmailSubject'
        );
        $body = \OmegaUp\ApiUtils::formatString(
            \OmegaUp\Translations::getInstance()->get(
                'accountDeletionRequestEmailBody'
            ),
            [
                'username' => $identity->username,
            ]
        );

        \OmegaUp\Email::sendEmail([$email->email], $subject, $body);
        return [
            'token' => $token,
        ];
    }

    /**
     * @return array{status: string}
     *
     * @omegaup-request-param string $token
     * @omegaup-request-param null|string $username
     */
    public static function apiDeleteConfirm(\OmegaUp\Request $r): array {
        $r->ensureMainUserIdentity();
        $username = $r->ensureOptionalString(
            'username',
            required: false,
            validator: fn (string $username) => \OmegaUp\Validators::usernameOrEmail(
                $username
            )
        );
        $token = $r->ensureString('token');
        if (
            !\OmegaUp\Authorization::isSystemAdmin(
                $r->identity
            ) && !is_null(
                $username
            )
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }
        $user = self::resolveTargetUser($r);
        $identity = self::resolveTargetIdentity($r);
        if (is_null($user) || is_null($identity)) {
            throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
        }

        if (!\OmegaUp\DAO\Users::validateDeletionToken($user, $token)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterInvalid',
                'token'
            );
        }
        \OmegaUp\DAO\Users::deleteUserAndIndentityInformation($user, $identity);
        self::$log->info(
            "User {$identity->username} deleted their account successfully."
        );
        if (is_null($user->main_email_id)) {
            return [
                'status' => 'ok',
            ];
        }
        $email = \OmegaUp\DAO\Emails::getByPK($user->main_email_id);

        if (is_null($email) || is_null($email->email)) {
            return [
                'status' => 'ok',
            ];
        }
        $subject = \OmegaUp\Translations::getInstance()->get(
            'accountDeletionConfirmEmailSubject'
        );
        $body = \OmegaUp\ApiUtils::formatString(
            \OmegaUp\Translations::getInstance()->get(
                'accountDeletionConfirmEmailBody'
            ),
            [
                'username' => $identity->username,
            ]
        );

        \OmegaUp\Email::sendEmail([$email->email], $subject, $body);

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
     * @omegaup-request-param string $username
     *
     * @return array{status: string}
     */
    public static function apiAddExperiment(\OmegaUp\Request $r): array {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();
        $r->ensureMainUserIdentity();
        if (!\OmegaUp\Authorization::isSystemAdmin($r->identity)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        $r->ensureString(
            'username',
            fn (string $username) => \OmegaUp\Validators::usernameOrEmail(
                $username
            )
        );
        $user = self::resolveTargetUser($r);
        if (is_null($user) || is_null($user->user_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
        }

        \OmegaUp\DAO\UsersExperiments::create(new \OmegaUp\DAO\VO\UsersExperiments([
            'user_id' => $user->user_id,
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
     * @omegaup-request-param string $username
     *
     * @return array{status: string}
     */
    public static function apiRemoveExperiment(\OmegaUp\Request $r): array {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();
        $r->ensureMainUserIdentity();
        if (!\OmegaUp\Authorization::isSystemAdmin($r->identity)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        $r->ensureString(
            'username',
            fn (string $username) => \OmegaUp\Validators::usernameOrEmail(
                $username
            )
        );
        $user = self::resolveTargetUser($r);
        if (is_null($user) || is_null($user->user_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
        }

        \OmegaUp\DAO\UsersExperiments::delete(
            $user->user_id,
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
     * @return array{entrypoint: string, templateProperties: array{payload: PrivacyPolicyDetailsPayload, title: \OmegaUp\TranslationString}}
     *
     * @omegaup-request-param null|string $username
     */
    public static function getPrivacyPolicyDetailsForTypeScript(
        \OmegaUp\Request $r
    ): array {
        $r->ensureMainUserIdentity();
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
        self::$log->error(print_r($lang, true));
        $latestStatement = \OmegaUp\DAO\PrivacyStatements::getLatestPublishedStatement();
        if (is_null($latestStatement)) {
            throw new \OmegaUp\Exceptions\NotFoundException(
                'privacyStatementNotFound'
            );
        }
        /** @psalm-suppress MixedArgument OMEGAUP_ROOT is really a string... */
        $omegaupRoot = strval(OMEGAUP_ROOT);
        return [
            'templateProperties' => [
                'payload' => [
                    'policy_markdown' => file_get_contents(
                        sprintf(
                            "%s/privacy/privacy_policy/{$lang}.md",
                            $omegaupRoot,
                        )
                    ) ?: '',
                    'has_accepted' => \OmegaUp\DAO\PrivacyStatementConsentLog::hasAcceptedPrivacyStatement(
                        intval($identity->identity_id),
                        $latestStatement['privacystatement_id']
                    ),
                    'git_object_id' => $latestStatement['git_object_id'],
                    'statement_type' => 'privacy_policy',
                ],
                'title' => new \OmegaUp\TranslationString(
                    'omegaupTitlePrivacyPolicy'
                )
            ],
            'entrypoint' => 'user_privacy_policy',
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
     * @omegaup-request-param null|string $username
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
     * @omegaup-request-param null|string $username
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
        if (!is_null($identity->user_id)) {
            throw new \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException(
                'identityAlreadyInUse'
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
     * @return array{identities: list<AssociatedIdentity>}
     */
    public static function apiListAssociatedIdentities(\OmegaUp\Request $r): array {
        $r->ensureMainUserIdentity();

        return [
            'identities' => \OmegaUp\DAO\Identities::getAssociatedIdentities(
                $r->identity
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
     * Prepare all the properties to be sent to the rank table view via TypeScript
     *
     * @return array{templateProperties: array{payload: UserRankTablePayload, title: \OmegaUp\TranslationString}, entrypoint: string}
     *
     * @omegaup-request-param mixed $filter
     * @omegaup-request-param int $length
     * @omegaup-request-param int $page
     */
    public static function getRankForTypeScript(\OmegaUp\Request $r) {
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
            'templateProperties' => [
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
                        adjacent: 5,
                        params: $filter === '' ? [] : [ 'filter' => $filter ]
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

        $response['templateProperties']['payload']['isLogged'] = true;
        if (!is_null($r->identity->country_id)) {
            $availableFilters['country'] =
                \OmegaUp\Translations::getInstance($r->identity)->get(
                    'wordsFilterByCountry'
                );
        }
        if (!is_null($r->identity->state_id)) {
            $availableFilters['state'] =
                \OmegaUp\Translations::getInstance($r->identity)->get(
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
                \OmegaUp\Translations::getInstance($r->identity)->get(
                    'wordsFilterBySchool'
                );
        }
        $response['templateProperties']['payload']['availableFilters'] = $availableFilters;
        $response['templateProperties']['payload']['ranking'] = self::getRankByProblemsSolved(
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
     * @return array{entrypoint: string, templateProperties: array{fullWidth: bool, payload: IndexPayload, title: \OmegaUp\TranslationString}}
     */
    public static function getIndexDetailsForTypeScript(\OmegaUp\Request $r) {
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
            required: false,
            validator: fn (string $date): bool => \OmegaUp\Validators::stringNonEmpty(
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

        return [
            'templateProperties' => [
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
     * Prepare all the properties to be sent to the rank table view via TypeScript
     *
     * @omegaup-request-param mixed $category
     *
     * @return array{templateProperties: array{payload: CoderOfTheMonthPayload, title: \OmegaUp\TranslationString}, entrypoint: string}
     */
    public static function getCoderOfTheMonthDetailsForTypeScript(
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
                'templateProperties' => [
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
                        autoselected: false,
                        category: $category,
                    )
                ),
        ];
        return [
            'templateProperties' => [
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
     * @return array{entrypoint: string, templateProperties: array{payload: UserProfileDetailsPayload, title: \OmegaUp\TranslationString, fullWidth: bool}}
     *
     * @omegaup-request-param null|string $username
     */
    public static function getProfileDetailsForTypeScript(\OmegaUp\Request $r) {
        $username = $r->ensureOptionalString(
            'username',
            required: false,
            validator: fn (string $username) => \OmegaUp\Validators::normalUsername(
                $username
            )
        );
        self::authenticateOrAllowUnauthenticatedRequest($r);
        // When $username is not provided we need to validate that user is
        // logged in because we assume they want to see/edit their own profile
        if (is_null($username)) {
            $r->ensureIdentity();
        }
        $loggedIdentity = $r->identity;
        $targetIdentity = self::resolveTargetIdentity($r);
        if (
            is_null($targetIdentity) ||
            is_null($targetIdentity->identity_id) ||
            is_null($targetIdentity->username)
        ) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterNotFound',
                'username'
            );
        }
        $targetUser = null;
        if (!is_null($targetIdentity->user_id)) {
            $targetUser = \OmegaUp\DAO\Users::getByPK($targetIdentity->user_id);
        }
        $response = [
            'templateProperties' => [
                'payload' => [
                    'countries' => \OmegaUp\DAO\Countries::getAll(
                        null,
                        100,
                        'name'
                    ),
                    'programmingLanguages' => \OmegaUp\Controllers\Run::SUPPORTED_LANGUAGES,
                    'extraProfileDetails' => null,
                    'identities' => [],
                ],
                'title' => new \OmegaUp\TranslationString(
                    'omegaupTitleProfile'
                ),
                'fullWidth' => true,
            ],
            'entrypoint' => 'user_profile',
        ];

        if (
            self::shouldUserInformationBeHidden(
                $loggedIdentity,
                $targetIdentity,
                $targetUser
            )
        ) {
            // Only construct a private profile if it's actually needed.
            $response['templateProperties']['payload']['profile'] = self::getPrivateUserProfile(
                $targetIdentity
            );
            return $response;
        }

        $targetIdentityId = $targetIdentity->identity_id;
        /** @var CachedExtraProfileDetails */
        $cachedExtraProfileDetails = \OmegaUp\Cache::getFromCacheOrSet(
            \OmegaUp\Cache::USER_PROFILE,
            "{$targetIdentity->username}-extraProfileDetails",
            function () use (
                $targetIdentity,
                $targetIdentityId
            ): array {
                return
                [
                    'contests' => self::getContestStats($targetIdentity),
                    'solvedProblems' => self::getSolvedProblems(
                        $targetIdentityId
                    ),
                    'unsolvedProblems' => self::getUnsolvedProblems(
                        $targetIdentityId
                    ),
                    'createdProblems' => self::getCreatedProblems(
                        $targetIdentityId
                    ),
                    'createdContests' => \OmegaUp\DAO\Contests::getContestsCreatedByIdentity(
                        $targetIdentityId
                    ),
                    'createdCourses' => \OmegaUp\DAO\Courses::getCoursesCreatedByIdentity(
                        $targetIdentityId
                    ),
                    'stats' => \OmegaUp\DAO\Runs::countRunsOfIdentityPerDatePerVerdict(
                        $targetIdentityId
                    ),
                    'badges' => \OmegaUp\Controllers\Badge::getAllBadges()
                ];
            },
            APC_USER_CACHE_USER_RANK_TIMEOUT
        );

        $profile = self::getUserProfile($loggedIdentity, $targetIdentity);
        $associatedIdentities = is_null(
            $loggedIdentity
        ) ? [] : \OmegaUp\DAO\Identities::getAssociatedIdentities(
            $loggedIdentity
        );
        $ownedBadges = [];
        if (!is_null($targetUser)) {
            $ownedBadges = \OmegaUp\DAO\UsersBadges::getUserOwnedBadges(
                $targetUser
            );
        }
        $response['templateProperties']['payload'] = array_merge(
            $response['templateProperties']['payload'],
            [
                'profile' => $profile,
                'extraProfileDetails' => array_merge(
                    [
                    'ownedBadges' => $ownedBadges,
                    'hasPassword' => !is_null($targetIdentity->password),
                    ],
                    $cachedExtraProfileDetails
                ),
                'identities' => $associatedIdentities,
            ]
        );

        return $response;
    }

    /**
     * @return array{entrypoint: string, templateProperties: array{payload: UserDetailsPayload, title: \OmegaUp\TranslationString}}
     *
     * @omegaup-request-param string $username
     */
    public static function getUserDetailsForTypeScript(\OmegaUp\Request $r) {
        $r->ensureMainUserIdentity();
        $username = $r->ensureString(
            'username',
            fn (string $username) => \OmegaUp\Validators::usernameOrEmail(
                $username
            )
        );

        $user = self::resolveTargetUser($r);
        if (is_null($user) || is_null($user->user_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
        }

        $emails = \OmegaUp\DAO\Emails::getByUserId($user->user_id);
        $emailsList = [];
        foreach ($emails as $email) {
            if (is_null($email->email)) {
                continue;
            }
            $emailsList[] = $email->email;
        }

        $userExperiments = \OmegaUp\DAO\UsersExperiments::getByUserId(
            $user->user_id
        );
        $userExperimentsList = [];
        foreach ($userExperiments as $userExperiment) {
            if (is_null($userExperiment->experiment)) {
                continue;
            }
            $userExperimentsList[] = $userExperiment->experiment;
        }

        // TODO: Also support GroupRoles.
        $systemRoles = \OmegaUp\DAO\UserRoles::getSystemRoles($user->user_id);

        $roles = \OmegaUp\DAO\Roles::getAll();
        $rolesList = [];
        foreach ($roles as $role) {
            if (is_null($role->name)) {
                continue;
            }
            $rolesList[] = ['name' => $role->name];
        }

        $systemExperiments = [];
        /** @var array<string, mixed> */
        $defines = get_defined_constants(true)['user'];
        foreach (\OmegaUp\Experiments::getInstance()->getAllKnownExperiments() as $experiment) {
            $systemExperiments[] = [
                'name' => $experiment,
                'hash' => \OmegaUp\Experiments::getExperimentHash($experiment),
                'config' => \OmegaUp\Experiments::getInstance()->isEnabledByConfig(
                    $experiment,
                    $defines
                ),
            ];
        }

        return [
            'templateProperties' => [
                'payload' => [
                    'emails' => $emailsList,
                    'experiments' => $userExperimentsList,
                    'roleNames' => $rolesList,
                    'systemExperiments' => $systemExperiments,
                    'systemRoles' => $systemRoles,
                    'username' => $username,
                    'verified' => $user->verified != 0,
                ],
                'title' => new \OmegaUp\TranslationString(
                    'omegaupTitleAdminUsers'
                ),
            ],
            'entrypoint' => 'admin_user',
        ];
    }

    /**
     * @omegaup-request-param null|string $username
     *
     * @return array{entrypoint: string, templateProperties: array{payload: EmailEditDetailsPayload, title: \OmegaUp\TranslationString}}
     */
    public static function getEmailEditDetailsForTypeScript(\OmegaUp\Request $r) {
        $r->ensureMainUserIdentity();

        $targetIdentity = self::resolveTargetIdentity($r);

        // Only sysadmin can change email for another user
        if (
            !is_null($targetIdentity)
            && $targetIdentity->identity_id !== $r->identity->identity_id
            && !\OmegaUp\Authorization::isSystemAdmin($r->identity)
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException(
                'userNotAllowed'
            );
        }

        if (
            is_null(
                $targetIdentity
            ) || is_null(
                $targetIdentity->identity_id
            ) || is_null(
                $targetIdentity->user_id
            )
        ) {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterNotFound',
                'Identity'
            );
        }

        return [
            'templateProperties' => [
                'payload' => [
                    'email' => \OmegaUp\DAO\Emails::getMainMailByUserId(
                        $targetIdentity->user_id
                    ),
                    'profile' => self::getUserProfile(
                        $r->identity,
                        $targetIdentity
                    ),
                ],
                'title' => new \OmegaUp\TranslationString(
                    'omegaupTitleUsersEditEmail'
                ),
            ],
            'entrypoint' => 'user_edit_email_form',
        ];
    }

    /**
     * @return list<string>
     */
    public static function getUserTypes(
        \OmegaUp\DAO\VO\Users $user,
        \OmegaUp\DAO\VO\Identities $loggedIdentity
    ): array {
        if (
            !\OmegaUp\Authorization::isSystemAdmin($loggedIdentity)
                && $loggedIdentity->user_id !== $user->user_id
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        if (is_null($user->main_identity_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
        }

        $userTypes = [];

        if (
            is_null($user->has_competitive_objective)
            || is_null($user->has_learning_objective)
            || is_null($user->has_scholar_objective)
            || is_null($user->has_teaching_objective)
        ) {
            return $userTypes;
        }

        if ($user->has_learning_objective && $user->has_scholar_objective) {
            $userTypes[] = self::USER_TYPE_STUDENT;
        }
        if ($user->has_learning_objective && $user->has_competitive_objective) {
            $userTypes[] = self::USER_TYPE_CONTESTANT;
        }
        if ($user->has_teaching_objective && $user->has_scholar_objective) {
            $userTypes[] = self::USER_TYPE_TEACHER;
        }
        if ($user->has_teaching_objective && $user->has_competitive_objective) {
            $userTypes[] = self::USER_TYPE_COACH;
        }
        if ($user->has_learning_objective && !$user->has_scholar_objective && !$user->has_competitive_objective) {
            $userTypes[] = self::USER_TYPE_SELF_TAUGHT;
        }
        if ($user->has_teaching_objective && !$user->has_scholar_objective && !$user->has_competitive_objective) {
            $userTypes[] = self::USER_TYPE_INDEPENDENT_TEACHER;
        }
        if (!$user->has_learning_objective && !$user->has_teaching_objective) {
            $userTypes[] = self::USER_TYPE_CURIOUS;
        }
        return $userTypes;
    }

    /**
     * @param list<array{classname: string, country_id: string, email: null|string, rank?: int, time: string, user_id?: int, username: string}> $coders
     *
     * @return CoderOfTheMonthList
     */
    private static function processCodersList(array $coders): array {
        $response = [];
        foreach ($coders as $coder) {
            $hashEmail = md5($coder['email'] ?? '');
            $avatar = "https://secure.gravatar.com/avatar/{$hashEmail}?s=32";
            $response[] = [
                'username' => $coder['username'],
                'country_id' => $coder['country_id'],
                'gravatar_32' => $avatar,
                'date' => $coder['time'],
                'classname' => $coder['classname'],
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

    /**
     * @return array{entrypoint: string, templateProperties: array{payload: LoginDetailsPayload, title: \OmegaUp\TranslationString}}
     *
     * @omegaup-request-param null|string $credential
     * @omegaup-request-param null|string $g_csrf_token
     * @omegaup-request-param null|string $third_party_login
     */
    public static function getLoginDetailsForTypeScript(\OmegaUp\Request $r) {
        try {
            $r->ensureIdentity();
            // If the user has already logged in, redirect them to the home page.
            header('Location: /');
            throw new \OmegaUp\Exceptions\ExitException();
        } catch (\OmegaUp\Exceptions\UnauthorizedException $e) {
            // Do nothing.
        }
        $thirdPartyLogin = $r->ensureOptionalString('third_party_login');
        $gCsrfToken = $r->ensureOptionalString('g_csrf_token');
        $idToken = $r->ensureOptionalString('credential');
        if ($r->offsetExists('fb')) {
            $thirdPartyLogin = 'facebook';
        }

        $response = [
            'templateProperties' => [
                'payload' => [
                    'validateRecaptcha' => boolval(OMEGAUP_VALIDATE_CAPTCHA),
                    'facebookUrl' => \OmegaUp\Controllers\Session::getFacebookLoginUrl(),
                ],
                'title' => new \OmegaUp\TranslationString('omegaupTitleLogin'),
            ],
            'entrypoint' => 'login_signin',
        ];
        try {
            if ($thirdPartyLogin === 'facebook') {
                \OmegaUp\Controllers\Session::loginViaFacebook();
            } elseif (!is_null($gCsrfToken) && !is_null($idToken)) {
                \OmegaUp\Controllers\Session::loginViaGoogle(
                    $idToken,
                    $gCsrfToken
                );
            }
        } catch (\OmegaUp\Exceptions\ExitException $e) {
            // The controller has explicitly requested to exit.
            exit;
        } catch (\OmegaUp\Exceptions\ApiException $e) {
            \OmegaUp\ApiCaller::logException($e);
            $response['templateProperties']['payload']['statusError'] = strval(
                $e->getErrorMessage()
            );
            return $response;
        }
        return $response;
    }

    /**
     * Creates a new API token associated with the user.
     *
     * This token can be used to authenticate against the API in other calls
     * through the [HTTP `Authorization`
     * header](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Authorization)
     * in the request:
     *
     * ```
     * Authorization: token 92d8c5a0eceef3c05f4149fc04b62bb2cd50d9c6
     * ```
     *
     * The following alternative syntax allows to specify an associated
     * identity:
     *
     * ```
     * Authorization: token Credential=92d8c5a0eceef3c05f4149fc04b62bb2cd50d9c6,Username=groupname:username
     * ```
     *
     * There is a limit of 1000 requests that can be done every hour, after
     * which point all requests will fail with [HTTP 429 Too Many
     * Requests](https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/429).
     * The `X-RateLimit-Limit`, `X-RateLimit-Remaining`, and
     * `X-RateLimit-Reset` response headers will be set whenever an API token
     * is used and will contain useful information about the limit to the
     * caller.
     *
     * There is a limit of 5 API tokens that each user can have.
     *
     * @return array{token: string}
     *
     * @omegaup-request-param string $name A non-empty alphanumeric string. May contain underscores and dashes.
     */
    public static function apiCreateAPIToken(\OmegaUp\Request $r) {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();
        $r->ensureMainUserIdentity();

        $name = $r->ensureString(
            'name',
            fn (string $name) => preg_match('/^[a-zA-Z0-9_-]+$/', $name) === 1,
        );
        $token = \OmegaUp\SecurityTools::randomHexString(40);
        $apiToken = new \OmegaUp\DAO\VO\APITokens([
            'user_id' => $r->user->user_id,
            'token' => $token,
            'name' => $name,
        ]);

        try {
            \OmegaUp\DAO\DAO::transBegin();

            \OmegaUp\DAO\APITokens::create($apiToken);
            if (\OmegaUp\DAO\APITokens::getCountByUser($r->user->user_id) > 5) {
                throw new \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException(
                    'apiTokenLimitExceeded'
                );
            }

            \OmegaUp\DAO\DAO::transEnd();
            return [
                'token' => $token,
            ];
        } catch (\Exception $e) {
            \OmegaUp\DAO\DAO::transRollback();
            if (\OmegaUp\DAO\DAO::isDuplicateEntryException($e)) {
                throw new \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException(
                    'apiTokenNameAlreadyInUse',
                    $e
                );
            }
            throw $e;
        }
    }

    /**
     * Returns a list of all the API tokens associated with the user.
     *
     * @return array{tokens: list<array{name: string, timestamp: \OmegaUp\Timestamp, last_used: \OmegaUp\Timestamp, rate_limit: array{reset: \OmegaUp\Timestamp, limit: int, remaining: int}}>}
     */
    public static function apiListAPITokens(\OmegaUp\Request $r) {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();
        $r->ensureMainUserIdentity();

        return [
            'tokens' => \OmegaUp\DAO\APITokens::getAllByUser($r->user->user_id),
        ];
    }

    /**
     * Revokes an API token associated with the user.
     *
     * @return array{status: string}
     *
     * @omegaup-request-param string $name A non-empty alphanumeric string. May contain underscores and dashes.
     */
    public static function apiRevokeAPIToken(\OmegaUp\Request $r) {
        \OmegaUp\Controllers\Controller::ensureNotInLockdown();
        $r->ensureMainUserIdentity();

        $name = $r->ensureString(
            'name',
            fn (string $name) => preg_match('/^[a-zA-Z0-9_-]+$/', $name) === 1,
        );

        \OmegaUp\DAO\APITokens::deleteByName($r->user->user_id, $name);
        return ['status' => 'ok'];
    }

    /**
     * @return array{entrypoint: string, templateProperties: array{payload: VerificationParentalTokenDetailsPayload, title: \OmegaUp\TranslationString}}
     *
     * @omegaup-request-param string $parental_verification_token
     */
    public static function getVerificationParentalTokenDetailsForTypeScript(
        \OmegaUp\Request $r
    ): array {
        $r->ensureIdentity();

        $token = $r->ensureString(
            'parental_verification_token',
            validator: fn (string $token) => preg_match(
                '/^[a-zA-Z0-9]{24}$/',
                $token
            ) === 1
        );
        $hasParentalVerificationToken = false;
        try {
            \OmegaUp\DAO\DAO::transBegin();
            $user = \OmegaUp\DAO\Users::findByParentalToken($token);

            if (is_null($user)) {
                throw new \OmegaUp\Exceptions\NotFoundException(
                    'parentalTokenNotFound'
                );
            }

            if (is_null($r->user) || is_null($r->user->main_email_id)) {
                throw new \OmegaUp\Exceptions\UnauthorizedException();
            }

            $user->parent_email_id = $r->user->main_email_id;
            $user->parent_verified = true;
            $user->parental_verification_token = null;
            \OmegaUp\DAO\Users::update($user);
            $hasParentalVerificationToken = true;

            \OmegaUp\DAO\DAO::transEnd();
            return [
                'templateProperties' => [
                    'payload' => [
                        'hasParentalVerificationToken' => $hasParentalVerificationToken,
                    ],
                    'title' => new \OmegaUp\TranslationString(
                        'omegaupTitleParentalVerificationToken'
                    ),
                ],
                'entrypoint' => 'user_verification_parental_token',
            ];
        } catch (\Exception $e) {
            \OmegaUp\DAO\DAO::transRollback();
            throw $e;
        }
    }
}

\OmegaUp\Controllers\User::$urlHelper = new \OmegaUp\UrlHelper();
