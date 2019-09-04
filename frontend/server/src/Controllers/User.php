<?php

 namespace OmegaUp\Controllers;

/**
 *  UserController
 *
 * @author joemmanuel
 */
class User extends \OmegaUp\Controllers\Controller {
    public static $sendEmailOnVerify = true;
    public static $redirectOnVerify = true;
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

    /**
     * Entry point for Create a User API
     *
     * @param \OmegaUp\Request $r
     * @return array
     * @throws \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException
     */
    public static function apiCreate(\OmegaUp\Request $r) {
        // Validate request
        \OmegaUp\Validators::validateValidUsername($r['username'], 'username');

        \OmegaUp\Validators::validateEmail($r['email'], 'email');

        if (empty($r['scholar_degree'])) {
            $r['scholar_degree'] = 'none';
        }

        \OmegaUp\Validators::validateInEnum(
            $r['scholar_degree'],
            'scholar_degree',
            \OmegaUp\Controllers\User::ALLOWED_SCHOLAR_DEGREES
        );

        // Check password
        $hashedPassword = null;
        if (!isset($r['ignore_password'])) {
            \OmegaUp\SecurityTools::testStrongPassword($r['password']);
            $hashedPassword = \OmegaUp\SecurityTools::hashString($r['password']);
        }

        // Does user or email already exists?
        $user = \OmegaUp\DAO\Users::FindByUsername($r['username']);
        $userByEmail = \OmegaUp\DAO\Users::FindByEmail($r['email']);

        if (!is_null($userByEmail)) {
            if (!is_null($userByEmail->password)) {
                // Check if the same user had already tried to create this account.
                if (!is_null($user) && $user->user_id == $userByEmail->user_id
                    && \OmegaUp\SecurityTools::compareHashedStrings(
                        $r['password'],
                        strval($user->password)
                    )) {
                    return [
                        'status' => 'ok',
                        'username' => $user->username,
                    ];
                }
                throw new \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException('mailInUse');
            }

            $user = new \OmegaUp\DAO\VO\Users([
                'user_id' => $userByEmail->user_id,
                'username' => $r['username'],
                'password' => $hashedPassword
            ]);
            try {
                \OmegaUp\DAO\Users::savePassword($user);
            } catch (\Exception $e) {
                if (\OmegaUp\DAO\DAO::isDuplicateEntryException($e)) {
                    throw new \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException('usernameInUse', $e);
                }
                throw $e;
            }

            return [
                'status' => 'ok',
                'username' => $user->username,
            ];
        }

        if (!is_null($user)) {
            throw new \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException('usernameInUse');
        }

        // Prepare DAOs
        $identityData = [
            'username' => $r['username'],
            'password' => $hashedPassword
        ];
        $userData = [
            'username' => $r['username'],
            'password' => $hashedPassword,
            'verified' => 0,
            'verification_id' => \OmegaUp\SecurityTools::randomString(50),
        ];
        if (isset($r['is_private'])) {
            $userData['is_private'] = $r['is_private'];
        }
        if (isset($r['name'])) {
            $identityData['name'] = $r['name'];
        }
        if (isset($r['gender'])) {
            $identityData['gender'] = $r['gender'];
        }
        if (isset($r['facebook_user_id'])) {
            $userData['facebook_user_id'] = $r['facebook_user_id'];
        }
        if (!is_null(self::$permissionKey) &&
            self::$permissionKey == $r['permission_key']) {
            $userData['verified'] = 1;
        } elseif (OMEGAUP_VALIDATE_CAPTCHA) {
            // Validate captcha
            if (!isset($r['recaptcha'])) {
                throw new \OmegaUp\Exceptions\InvalidParameterException('parameterNotFound', 'recaptcha');
            }

            $url = 'https://www.google.com/recaptcha/api/siteverify';
            $data = [
                'secret' => OMEGAUP_RECAPTCHA_SECRET,
                'response' => $r['recaptcha'],
                'remoteip' => $_SERVER['REMOTE_ADDR']];

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

            $resultAsJson = json_decode($result, true);
            if (is_null($resultAsJson)) {
                self::$log->error('Captcha response was not a json');
                self::$log->error('Here is the result:' . $result);
                throw new \OmegaUp\Exceptions\CaptchaVerificationFailedException();
            }

            if (!(array_key_exists('success', $resultAsJson) && $resultAsJson['success'])) {
                self::$log->error('Captcha response said no');
                throw new \OmegaUp\Exceptions\CaptchaVerificationFailedException();
            }
        }

        $user = new \OmegaUp\DAO\VO\Users($userData);
        $identity = new \OmegaUp\DAO\VO\Identities($identityData);

        $email = new \OmegaUp\DAO\VO\Emails([
            'email' => $r['email'],
        ]);

        // Save objects into DB
        try {
            \OmegaUp\DAO\DAO::transBegin();

            \OmegaUp\DAO\Users::create($user);

            $email->user_id = $user->user_id;
            \OmegaUp\DAO\Emails::create($email);

            $identity->user_id = $user->user_id;
            \OmegaUp\DAO\Identities::create($identity);

            $user->main_email_id = $email->email_id;
            $user->main_identity_id = $identity->identity_id;
            \OmegaUp\DAO\Users::update($user);

            $r['user'] = $user;
            if ($user->verified) {
                self::$log->info('User ' . $user->username . ' created, trusting e-mail');
            } else {
                self::$log->info('User ' . $user->username . ' created, sending verification mail');

                self::sendVerificationEmail($user);
            }

            \OmegaUp\DAO\DAO::transEnd();
        } catch (\Exception $e) {
            \OmegaUp\DAO\DAO::transRollback();
            throw $e;
        }

        return [
            'status' => 'ok',
            'username' => $identity->username,
        ];
    }

    /**
     * Registers the created user to Sendy
     *
     * @param \OmegaUp\Request $r
     */
    private static function registerToSendy(\OmegaUp\DAO\VO\Users $user) {
        if (!OMEGAUP_EMAIL_SENDY_ENABLE) {
            return false;
        }

        self::$log->info('Adding user to Sendy.');

        // Get email
        try {
            $email = \OmegaUp\DAO\Emails::getByPK($user->main_email_id);
        } catch (\Exception $e) {
            self::$log->warn('Email lookup failed: ' . $e->getMessage());
            return false;
        }

        //Subscribe
        $postdata = http_build_query(
            [
                'name' => $user->username,
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
        $result = self::$urlHelper->fetchUrl(OMEGAUP_EMAIL_SENDY_SUBSCRIBE_URL, $context);

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
     * @param \OmegaUp\Request $r
     * @throws \OmegaUp\Exceptions\EmailVerificationSendException
     */
    private static function sendVerificationEmail(\OmegaUp\DAO\VO\Users $user) {
        $email = \OmegaUp\DAO\Emails::getByPK($user->main_email_id);
        if (is_null($email) || is_null($email->email)) {
            throw new \OmegaUp\Exceptions\NotFoundException('userOrMailNotfound');
        }

        if (!self::$sendEmailOnVerify) {
            self::$log->info('Not sending email beacause sendEmailOnVerify = FALSE');
            return;
        }

        $subject = \OmegaUp\Translations::getInstance()->get('verificationEmailSubject')
            ?: 'verificationEmailSubject';
        $body = sprintf(
            \OmegaUp\Translations::getInstance()->get('verificationEmailBody')
                ?: 'verificationEmailBody',
            OMEGAUP_URL,
            strval($user->verification_id)
        );

        \OmegaUp\Email::sendEmail([$email->email], $subject, $body);
    }

    /**
     * Check if email of user in request has been verified
     *
     * @param \OmegaUp\Request $r
     * @throws \OmegaUp\Exceptions\EmailNotVerifiedException
     */
    public static function checkEmailVerification(\OmegaUp\DAO\VO\Users $user) {
        if ($user->verified != '0') {
            // Already verified, nothing to do.
            return;
        }
        if (!OMEGAUP_FORCE_EMAIL_VERIFICATION) {
            return;
        }
        self::$log->info("User {$user->username} not verified.");

        if (is_null($user->verification_id)) {
            self::$log->info('User does not have verification id. Generating.');

            try {
                $user->verification_id = \OmegaUp\SecurityTools::randomString(50);
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
     *
     * @param \OmegaUp\Request $r
     */
    public static function apiLogin(\OmegaUp\Request $r) {
        $sessionController = new \OmegaUp\Controllers\Session();

        return [
            'status' => 'ok',
            'auth_token' => $sessionController->nativeLogin($r),
        ];
    }

    /**
     * Changes the password of a user
     *
     * @param \OmegaUp\Request $rﬁ
     * @return array
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     */
    public static function apiChangePassword(\OmegaUp\Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        $r->ensureMainUserIdentity();

        $hashedPassword = null;
        /** @var \OmegaUp\DAO\VO\Users */
        $user = $r->user;
        if (isset($r['username']) && $r['username'] != $user->username) {
            // This is usable only in tests.
            if (is_null(self::$permissionKey) || self::$permissionKey != $r['permission_key']) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException();
            }
            \OmegaUp\Validators::validateStringNonEmpty($r['username'], 'username');

            $user = \OmegaUp\DAO\Users::FindByUsername($r['username']);
            if (is_null($user)) {
                throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
            }
            $identity = \OmegaUp\DAO\Identities::getByPK($user->main_identity_id);

            if (isset($r['password']) && $r['password'] != '') {
                \OmegaUp\SecurityTools::testStrongPassword($r['password']);
                $hashedPassword = \OmegaUp\SecurityTools::hashString($r['password']);
            }
        } else {
            /** @var int $user->main_identity_id */
            $identity = \OmegaUp\DAO\Identities::getByPK($user->main_identity_id);

            if ($user->password != null) {
                // Check the old password
                \OmegaUp\Validators::validateStringNonEmpty($r['old_password'], 'old_password');

                $old_password_valid = \OmegaUp\SecurityTools::compareHashedStrings(
                    $r['old_password'],
                    $user->password
                );

                if ($old_password_valid === false) {
                    throw new \OmegaUp\Exceptions\InvalidParameterException('parameterInvalid', 'old_password');
                }
            }

            \OmegaUp\SecurityTools::testStrongPassword($r['password']);
            $hashedPassword = \OmegaUp\SecurityTools::hashString($r['password']);
        }

        $user->password = $hashedPassword;
        $identity->password = $hashedPassword;

        try {
            \OmegaUp\DAO\DAO::transBegin();

            \OmegaUp\DAO\Users::update($user);

            \OmegaUp\DAO\Identities::update($identity);

            \OmegaUp\DAO\DAO::transEnd();
        } catch (\Exception $e) {
            \OmegaUp\DAO\DAO::transRollback();
            throw $e;
        }

        return ['status' => 'ok'];
    }

    /**
     * Verifies the user given its verification id
     *
     * @param \OmegaUp\Request $r
     * @return type
     * @throws \OmegaUp\Exceptions\ApiException
     * @throws \OmegaUp\Exceptions\NotFoundException
     */
    public static function apiVerifyEmail(\OmegaUp\Request $r) {
        $user = null;

        // Admin can override verification by sending username
        if (isset($r['usernameOrEmail'])) {
            $r->ensureIdentity();

            if (!\OmegaUp\Authorization::isSupportTeamMember($r->identity)) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException();
            }

            self::$log->info('Admin verifiying user...' . $r['usernameOrEmail']);

            \OmegaUp\Validators::validateStringNonEmpty($r['usernameOrEmail'], 'usernameOrEmail');

            $user = self::resolveUser($r['usernameOrEmail']);

            self::$redirectOnVerify = false;
        } else {
            // Normal user verification path
            \OmegaUp\Validators::validateStringNonEmpty($r['id'], 'id');

            $users = \OmegaUp\DAO\Users::getByVerification($r['id']);
            $user = !empty($users) ? $users[0] : null;
        }

        if (is_null($user)) {
            throw new \OmegaUp\Exceptions\NotFoundException('verificationIdInvalid');
        }

        $user->verified = 1;
        \OmegaUp\DAO\Users::update($user);

        self::$log->info('User verification complete.');

        if (self::$redirectOnVerify) {
            if (!is_null($r['redirecttointerview'])) {
                die(header('Location: /login/?redirect=/interview/' . urlencode($r['redirecttointerview']) . '/arena'));
            } else {
                die(header('Location: /login/'));
            }
        }

        // Expire profile cache
        \OmegaUp\Cache::deleteFromCache(\OmegaUp\Cache::USER_PROFILE, $user->username);

        return ['status' => 'ok'];
    }

    /**
     * Registers to the mailing list all users that have not been added before. Admin only
     *
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     */
    public static function apiMailingListBackfill(\OmegaUp\Request $r) {
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
            $registered = self::registerToSendy($user);

            if ($registered) {
                $user->in_mailing_list = 1;
                \OmegaUp\DAO\Users::update($user);
            }

            $usersAdded[$user->username] = $registered;
        }

        return [
            'status' => 'ok',
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
    public static function resolveUser(?string $userOrEmail) : \OmegaUp\DAO\VO\Users {
        \OmegaUp\Validators::validateStringNonEmpty($userOrEmail, 'usernameOrEmail');
        $user = \OmegaUp\DAO\Users::FindByUsername($userOrEmail);
        if (!is_null($user)) {
            return $user;
        }
        $user = \OmegaUp\DAO\Users::FindByEmail($userOrEmail);
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
     */
    private static function omiPrepareUser(\OmegaUp\Request $r, $username, $password) {
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
        } elseif (is_null($r['change_password']) || $r['change_password'] !== 'false') {
            if (!$user->verified) {
                self::apiVerifyEmail(new \OmegaUp\Request([
                    'auth_token' => $r['auth_token'],
                    'usernameOrEmail' => $username
                ]));
            }

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
     *
     * @param \OmegaUp\Request $r
     * @return array
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     */
    public static function apiGenerateOmiUsers(\OmegaUp\Request $r) {
        $r->ensureIdentity();

        $response = [];

        $is_system_admin = \OmegaUp\Authorization::isSystemAdmin($r->identity);
        if ($r['contest_type'] == 'OMI') {
            if ($r->user->username != 'andreasantillana'
                && !$is_system_admin
            ) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException();
            }

            // Arreglo de estados de MX
            $keys = [
                'OMI2019-AGU' => 4,
                'OMI2019-BCN' => 4,
                'OMI2019-BCS' => 4,
                'OMI2019-CAM' => 4,
                'OMI2019-CHH' => 4,
                'OMI2019-CHP' => 4,
                'OMI2019-CMX' => 4,
                'OMI2019-COA' => 4,
                'OMI2019-COL' => 4,
                'OMI2019-DUR' => 4,
                'OMI2019-GRO' => 4,
                'OMI2019-GUA' => 4,
                'OMI2019-HID' => 4,
                'OMI2019-JAL' => 4,
                'OMI2019-MEX' => 4,
                'OMI2019-MIC' => 4,
                'OMI2019-MOR' => 4,
                'OMI2019-NAY' => 4,
                'OMI2019-NLE' => 4,
                'OMI2019-OAX' => 4,
                'OMI2019-PUE' => 4,
                'OMI2019-QTO' => 4,
                'OMI2019-ROO' => 4,
                'OMI2019-SIN' => 8,
                'OMI2019-SLP' => 4,
                'OMI2019-SON' => 4,
                'OMI2019-TAB' => 4,
                'OMI2019-TAM' => 4,
                'OMI2019-TLA' => 4,
                'OMI2019-VER' => 4,
                'OMI2019-YUC' => 4,
                'OMI2019-ZAC' => 4,
                'OMI2019-INV' => 4,
            ];
        } elseif ($r['contest_type'] == 'OMIP') {
            if ($r->user->username != 'andreasantillana'
                && !$is_system_admin
            ) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException();
            }

            $keys = [
                'OMIP2019-AGU' => 25,
                'OMIP2019-BCN' => 25,
                'OMIP2019-BCS' => 25,
                'OMIP2019-CAM' => 25,
                'OMIP2019-CHH' => 25,
                'OMIP2019-CHP' => 25,
                'OMIP2019-CMX' => 25,
                'OMIP2019-COA' => 25,
                'OMIP2019-COL' => 25,
                'OMIP2019-DUR' => 25,
                'OMIP2019-GRO' => 25,
                'OMIP2019-GUA' => 25,
                'OMIP2019-HID' => 25,
                'OMIP2019-JAL' => 25,
                'OMIP2019-MEX' => 25,
                'OMIP2019-MIC' => 25,
                'OMIP2019-MOR' => 25,
                'OMIP2019-NAY' => 25,
                'OMIP2019-NLE' => 25,
                'OMIP2019-OAX' => 25,
                'OMIP2019-PUE' => 25,
                'OMIP2019-QTO' => 25,
                'OMIP2019-ROO' => 25,
                'OMIP2019-SIN' => 25,
                'OMIP2019-SLP' => 25,
                'OMIP2019-SON' => 25,
                'OMIP2019-TAB' => 25,
                'OMIP2019-TAM' => 25,
                'OMIP2019-TLA' => 25,
                'OMIP2019-VER' => 25,
                'OMIP2019-YUC' => 25,
                'OMIP2019-ZAC' => 25,
            ];
        } elseif ($r['contest_type'] == 'OMIS') {
            if ($r->user->username != 'andreasantillana'
                && !$is_system_admin
            ) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException();
            }

            $keys = [
                'OMIS2019-AGU' => 25,
                'OMIS2019-BCN' => 25,
                'OMIS2019-BCS' => 25,
                'OMIS2019-CAM' => 25,
                'OMIS2019-CHH' => 25,
                'OMIS2019-CHP' => 25,
                'OMIS2019-CMX' => 25,
                'OMIS2019-COA' => 25,
                'OMIS2019-COL' => 25,
                'OMIS2019-DUR' => 25,
                'OMIS2019-GRO' => 25,
                'OMIS2019-GUA' => 25,
                'OMIS2019-HID' => 25,
                'OMIS2019-JAL' => 25,
                'OMIS2019-MEX' => 25,
                'OMIS2019-MIC' => 25,
                'OMIS2019-MOR' => 25,
                'OMIS2019-NAY' => 25,
                'OMIS2019-NLE' => 25,
                'OMIS2019-OAX' => 25,
                'OMIS2019-PUE' => 25,
                'OMIS2019-QTO' => 25,
                'OMIS2019-ROO' => 25,
                'OMIS2019-SIN' => 25,
                'OMIS2019-SLP' => 25,
                'OMIS2019-SON' => 25,
                'OMIS2019-TAB' => 25,
                'OMIS2019-TAM' => 25,
                'OMIS2019-TLA' => 25,
                'OMIS2019-VER' => 25,
                'OMIS2019-YUC' => 25,
                'OMIS2019-ZAC' => 25,
            ];
        } elseif ($r['contest_type'] == 'OMIPN') {
            if ($r->user->username != 'andreasantillana'
                && !$is_system_admin
            ) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException();
            }

            $keys = [
                'OMIP2019-AGU' => 4,
                'OMIP2019-BCN' => 4,
                'OMIP2019-BCS' => 4,
                'OMIP2019-CAM' => 4,
                'OMIP2019-CHH' => 4,
                'OMIP2019-CHP' => 4,
                'OMIP2019-CMX' => 4,
                'OMIP2019-COA' => 4,
                'OMIP2019-COL' => 4,
                'OMIP2019-DUR' => 4,
                'OMIP2019-GRO' => 4,
                'OMIP2019-GUA' => 4,
                'OMIP2019-HID' => 4,
                'OMIP2019-JAL' => 4,
                'OMIP2019-MEX' => 4,
                'OMIP2019-MIC' => 4,
                'OMIP2019-MOR' => 4,
                'OMIP2019-NAY' => 4,
                'OMIP2019-NLE' => 4,
                'OMIP2019-OAX' => 4,
                'OMIP2019-PUE' => 4,
                'OMIP2019-QTO' => 4,
                'OMIP2019-ROO' => 4,
                'OMIP2019-SIN' => 4,
                'OMIP2019-SLP' => 4,
                'OMIP2019-SON' => 4,
                'OMIP2019-TAB' => 4,
                'OMIP2019-TAM' => 4,
                'OMIP2019-TLA' => 4,
                'OMIP2019-VER' => 4,
                'OMIP2019-YUC' => 4,
                'OMIP2019-ZAC' => 4,
                'OMIP2019-INV' => 4,
            ];
        } elseif ($r['contest_type'] == 'OMISN') {
            if ($r->user->username != 'andreasantillana'
                && !$is_system_admin
            ) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException();
            }

            $keys = [
                'OMIS2019-AGU' => 4,
                'OMIS2019-BCN' => 4,
                'OMIS2019-BCS' => 4,
                'OMIS2019-CAM' => 4,
                'OMIS2019-CHH' => 4,
                'OMIS2019-CHP' => 4,
                'OMIS2019-CMX' => 4,
                'OMIS2019-COA' => 4,
                'OMIS2019-COL' => 4,
                'OMIS2019-DUR' => 4,
                'OMIS2019-GRO' => 4,
                'OMIS2019-GUA' => 4,
                'OMIS2019-HID' => 4,
                'OMIS2019-JAL' => 4,
                'OMIS2019-MEX' => 4,
                'OMIS2019-MIC' => 4,
                'OMIS2019-MOR' => 4,
                'OMIS2019-NAY' => 4,
                'OMIS2019-NLE' => 4,
                'OMIS2019-OAX' => 4,
                'OMIS2019-PUE' => 4,
                'OMIS2019-QTO' => 4,
                'OMIS2019-ROO' => 4,
                'OMIS2019-SIN' => 4,
                'OMIS2019-SLP' => 4,
                'OMIS2019-SON' => 4,
                'OMIS2019-TAB' => 4,
                'OMIS2019-TAM' => 4,
                'OMIS2019-TLA' => 4,
                'OMIS2019-VER' => 4,
                'OMIS2019-YUC' => 4,
                'OMIS2019-ZAC' => 4,
                'OMIS2019-INV' => 4,
            ];
        } elseif ($r['contest_type'] == 'ORIG') {
            if ($r->user->username != 'kuko.coder'
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
            if ($r->user->username != 'rsolis'
                && !$is_system_admin
            ) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException();
            }

            $keys =  [
                'OMIZAC-2018' => 20
            ];
        } elseif ($r['contest_type'] == 'Pr8oUAIE') {
            if ($r->user->username != 'rsolis'
                && !$is_system_admin
            ) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException();
            }

            $keys =  [
                'Pr8oUAIE' => 20
            ];
        } elseif ($r['contest_type'] == 'OMIZAC') {
            if ($r->user->username != 'rsolis'
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
            if ($r->user->username != 'rsolis'
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
            if ($r->user->username != 'EfrenGonzalez'
                && !$is_system_admin
            ) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException();
            }

            $keys =  [
                'OMIAGS-2018' => 30
            ];
        } elseif ($r['contest_type'] == 'OMIAGS-2017') {
            if ($r->user->username != 'EfrenGonzalez'
                && !$is_system_admin
            ) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException();
            }

            $keys =  [
                'OMIAGS-2017' => 30
            ];
        } elseif ($r['contest_type'] == 'OMIP-AGS') {
            if ($r->user->username != 'EfrenGonzalez'
                && !$is_system_admin
            ) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException();
            }

            $keys =  [
                'OMIP-AGS' => 30
            ];
        } elseif ($r['contest_type'] == 'OMIS-AGS') {
            if ($r->user->username != 'EfrenGonzalez'
                && !$is_system_admin
            ) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException();
            }

            $keys =  [
                'OMIS-AGS' => 30
            ];
        } elseif ($r['contest_type'] == 'OSI') {
            if ($r->user->username != 'cope_quintana'
                && !$is_system_admin
            ) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException();
            }

            $keys =  [
                'OSI16' => 120
            ];
        } elseif ($r['contest_type'] == 'UNAMFC') {
            if ($r->user->username != 'manuelalcantara52'
                && $r->user->username != 'manuel52'
                && !$is_system_admin
            ) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException();
            }
            $keys =  [
                'UNAMFC16' => 65
            ];
        } elseif ($r['contest_type'] == 'OVI') {
            if ($r->user->username != 'covi.academico'
                && !$is_system_admin
            ) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException();
            }
            $keys =  [
                'OVI19' => 200
            ];
        } elseif ($r['contest_type'] == 'UDCCUP') {
            if ($r->user->username != 'Diego_Briaares'
                && !$is_system_admin
            ) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException();
            }
            $keys =  [
                'UDCCUP-2017' => 40
            ];
        } elseif ($r['contest_type'] == 'CCUPITSUR') {
            if ($r->user->username != 'licgerman-yahoo'
                && !$is_system_admin
            ) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException();
            }
            // Arreglo de concurso
            $keys = [
                'CCUPITSUR-16' => 50,
            ];
        } elseif ($r['contest_type'] == 'CONALEP') {
            if ($r->user->username != 'reyes811'
                && !$is_system_admin
            ) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException();
            }
            $keys =  [
                'OIC-16' => 225
            ];
        } elseif ($r['contest_type'] == 'OMIQROO') {
            if ($r->user->username != 'pablobatun'
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
            if ($r->user->username != 'lacj20'
                && !$is_system_admin
            ) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException();
            }
            $keys = [
                'TEBAEV' => 250,
            ];
        } elseif ($r['contest_type'] == 'PYE-AGS') {
            if ($r->user->username != 'joemmanuel'
                && !$is_system_admin
            ) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException();
            }
            $keys = [
                'PYE-AGS18' => 40,
            ];
        } elseif ($r['contest_type'] == 'CAPKnuth') {
            if ($r->user->username != 'galloska'
                && !$is_system_admin
            ) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException();
            }
            $keys = [
                'ESCOM2018' => 50,
            ];
        } elseif ($r['contest_type'] == 'CAPVirtualKnuth') {
            if ($r->user->username != 'galloska'
                && !$is_system_admin
            ) {
                throw new \OmegaUp\Exceptions\ForbiddenAccessException();
            }
            $keys = [
                'Virtual-ESCOM2018' => 50,
            ];
        } else {
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterNotInExpectedSet',
                'contest_type',
                [
                    'bad_elements' => $r['contest_type'],
                    'expected_set' => 'OMI, OMIAGS, OMIP-AGS, OMIS-AGS, ORIG, OSI, OVI, UDCCUP, CCUPITSUR, CONALEP, OMIQROO, OMIAGS-2017, OMIAGS-2018, PYE-AGS, OMIZAC-2018, Pr8oUAIE, CAPKnuth, CAPVirtualKnuth, OMIZAC, ProgUAIE',
                ]
            );
        }

        self::$permissionKey = $r['permission_key'] = \OmegaUp\SecurityTools::randomString(32);

        foreach ($keys as $k => $n) {
            $digits = floor(log10($n) + 1);
            for ($i = 1; $i <= $n; $i++) {
                $username = $k . '-' . str_pad($i, $digits, '0', STR_PAD_LEFT);
                $password = \OmegaUp\SecurityTools::randomString(8);

                if (self::omiPrepareUser($r, $username, $password)) {
                    $response[$username] = $password;
                }

                // Add user to contest if needed
                if (!is_null($r['contest_alias'])) {
                    $addUserRequest = new \OmegaUp\Request();
                    $addUserRequest['auth_token'] = $r['auth_token'];
                    $addUserRequest['usernameOrEmail'] = $username;
                    $addUserRequest['contest_alias'] = $r['contest_alias'];
                    \OmegaUp\Controllers\Contest::apiAddUser($addUserRequest);
                }
            }
        }

        return $response;
    }

    /**
     * Returns the profile of the user given
     *
     * @param \OmegaUp\DAO\VO\Users $user
     * @return array
     */
    public static function getProfileImpl(
        \OmegaUp\DAO\VO\Users $user,
        \OmegaUp\DAO\VO\Identities $identity
    ) {
        $response = [];
        $response['userinfo'] = [];

        $response['userinfo'] = [
            'username' => $user->username,
            'name' => $identity->name,
            'birth_date' => is_null($user->birth_date) ? null : \OmegaUp\DAO\DAO::fromMySQLTimestamp($user->birth_date),
            'gender' => $identity->gender,
            'graduation_date' => is_null($user->graduation_date) ? null : \OmegaUp\DAO\DAO::fromMySQLTimestamp($user->graduation_date),
            'scholar_degree' => $user->scholar_degree,
            'preferred_language' => $user->preferred_language,
            'is_private' => $user->is_private,
            'verified' => $user->verified == '1',
            'hide_problem_tags' => is_null($user->hide_problem_tags) ? null : $user->hide_problem_tags,
        ];

        $userDb = \OmegaUp\DAO\Users::getExtendedProfileDataByPk($user->user_id);

        $response['userinfo']['email'] = $userDb['email'];
        $response['userinfo']['country'] = $userDb['country'];
        $response['userinfo']['country_id'] = $userDb['country_id'];
        $response['userinfo']['state'] = $userDb['state'];
        $response['userinfo']['state_id'] = $userDb['state_id'];
        $response['userinfo']['school'] = $userDb['school'];
        $response['userinfo']['school_id'] = $userDb['school_id'];
        $response['userinfo']['locale'] =
          \OmegaUp\Controllers\Identity::convertToSupportedLanguage($userDb['locale']);

        $response['userinfo']['gravatar_92'] = 'https://secure.gravatar.com/avatar/' . md5($response['userinfo']['email']) . '?s=92';

        return $response;
    }

    /**
     * Get general user info
     *
     * @param \OmegaUp\Request $r
     * @return array{status: 'ok'} with user info
     */
    public static function apiProfile(\OmegaUp\Request $r) {
        self::authenticateOrAllowUnauthenticatedRequest($r);

        $identity = self::resolveTargetIdentity($r);
        if (is_null($identity)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException('parameterNotFound', 'Identity');
        }
        $user = is_null($identity->user_id) ? null : \OmegaUp\DAO\Users::getByPK($identity->user_id);
        $r['user'] = $user;
        $r['identity'] = $identity;

        $response = \OmegaUp\Controllers\Identity::getProfile($r, $identity, $user, boolval($r['omit_rank']));
        if ((is_null($r->identity) || $r->identity->username != $identity->username)
            && (!is_null($user) && $user->is_private == 1)
            && (is_null($r->identity) || !\OmegaUp\Authorization::isSystemAdmin($r->identity))
        ) {
            $response['problems'] = [];
            foreach ($response['userinfo'] as $k => $v) {
                $response['userinfo'][$k] = null;
            }
            $response['userinfo']['username'] = $identity->username;
            $response['userinfo']['rankinfo'] = [
                'name' => null,
                'problems_solved' => null,
                'rank' => null,
                'status' => 'ok',
            ];
            $response['userinfo']['is_private'] = true;
        }
        $response['userinfo']['classname'] = \OmegaUp\DAO\Users::getRankingClassName($identity->user_id);
        $response['status'] = 'ok';
        return $response;
    }

    /**
     * Gets verify status of a user
     *
     * @param \OmegaUp\Request $r
     * @return response array
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     */
    public static function apiStatusVerified(\OmegaUp\Request $r) {
        $r->ensureIdentity();

        if (!\OmegaUp\Authorization::isSupportTeamMember($r->identity)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        $response = \OmegaUp\DAO\Identities::getStatusVerified($r['email']);

        if (is_null($response)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException('invalidUser');
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
     * @param \OmegaUp\Request $r
     * @return response array
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     */
    public static function apiExtraInformation(\OmegaUp\Request $r) {
        $r->ensureIdentity();

        if (!\OmegaUp\Authorization::isSupportTeamMember($r->identity)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        $response = \OmegaUp\DAO\Identities::getExtraInformation($r['email']);

        if (is_null($response)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException('invalidUser');
        }

        $response['status'] = 'ok';
        return $response;
    }

    /**
     * Get coder of the month by trying to find it in the table using the first
     * day of the current month. If there's no coder of the month for the given
     * date, calculate it and save it.
     *
     * @param \OmegaUp\Request $r
     * @return array
     */
    public static function apiCoderOfTheMonth(\OmegaUp\Request $r) {
        $currentTimestamp = \OmegaUp\Time::get();
        if (!empty($r['date'])) {
            \OmegaUp\Validators::validateDate($r['date'], 'date');
            $firstDay = date('Y-m-01', strtotime($r['date']));
        } else {
            // Get first day of the current month
            $firstDay = date('Y-m-01', $currentTimestamp);
        }

        $codersOfTheMonth = \OmegaUp\DAO\CoderOfTheMonth::getByTime($firstDay);

        if (empty($codersOfTheMonth)) {
            // Generate the coder
            $users = \OmegaUp\DAO\CoderOfTheMonth::calculateCoderOfMonthByGivenDate($firstDay);
            if (is_null($users)) {
                return [
                    'status' => 'ok',
                    'userinfo' => null,
                    'problems' => null,
                ];
            }

            // Only first place coder is saved
            \OmegaUp\DAO\CoderOfTheMonth::create(new \OmegaUp\DAO\VO\CoderOfTheMonth([
                'user_id' => $users[0]['user_id'],
                'time' => $firstDay,
                'rank' => 1,
            ]));
            $coderOfTheMonthUserId = $users[0]['user_id'];
        } else {
            $coderOfTheMonthUserId = $codersOfTheMonth[0]->user_id;
        }
        $user = \OmegaUp\DAO\Users::getByPK($coderOfTheMonthUserId);
        $identity = \OmegaUp\DAO\Identities::findByUserId($coderOfTheMonthUserId);

        // Get the profile of the coder of the month
        $response = \OmegaUp\Controllers\User::getProfileImpl($user, $identity);

        // But avoid divulging the email in the response.
        unset($response['userinfo']['email']);

        $response['status'] = 'ok';
        return $response;
    }

    /**
     * Returns the list of coders of the month
     *
     * @param \OmegaUp\Request $r
     */
    public static function apiCoderOfTheMonthList(\OmegaUp\Request $r) {
        \OmegaUp\Validators::validateOptionalDate($r['date'], 'date');
        if (!is_null($r['date'])) {
            $coders = \OmegaUp\DAO\CoderOfTheMonth::getMonthlyList($r['date']);
        } else {
            $coders = \OmegaUp\DAO\CoderOfTheMonth::getCodersOfTheMonth();
        }
        return [
            'status' => 'ok',
            'coders' => self::processCodersList($coders),
        ];
    }

    /**
     * Selects coder of the month for next month.
     *
     * @param \OmegaUp\Request $r
     * @return Array
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     * @throws \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException
     * @throws \OmegaUp\Exceptions\NotFoundException
     */
    public static function apiSelectCoderOfTheMonth(\OmegaUp\Request $r) {
        $r->ensureIdentity();
        $currentTimestamp = \OmegaUp\Time::get();

        if (!\OmegaUp\Authorization::isMentor($r->identity)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('userNotAllowed');
        }
        if (!\OmegaUp\Authorization::canChooseCoder($currentTimestamp)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('coderOfTheMonthIsNotInPeriodToBeChosen');
        }
        \OmegaUp\Validators::validateStringNonEmpty($r['username'], 'username');

        $currentDate = date('Y-m-d', $currentTimestamp);
        $firstDayOfNextMonth = new \DateTime($currentDate);
        $firstDayOfNextMonth->modify('first day of next month');
        $dateToSelect = $firstDayOfNextMonth->format('Y-m-d');

        $codersOfTheMonth = \OmegaUp\DAO\CoderOfTheMonth::getByTime($dateToSelect);

        if (!empty($codersOfTheMonth)) {
            throw new \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException('coderOfTheMonthAlreadySelected');
        }
        // Generate the coder
        $users = \OmegaUp\DAO\CoderOfTheMonth::calculateCoderOfMonthByGivenDate($dateToSelect);

        if (empty($users)) {
            throw new \OmegaUp\Exceptions\NotFoundException('noCoders');
        }

        foreach ($users as $index => $user) {
            if ($user['username'] != $r['username']) {
                continue;
            }

            // Save it
            \OmegaUp\DAO\CoderOfTheMonth::create(new \OmegaUp\DAO\VO\CoderOfTheMonth([
                'user_id' => $user['user_id'],
                'time' => $dateToSelect,
                'rank' => $index + 1,
                'selected_by' => $r->identity->identity_id,
            ]));

            return ['status' => 'ok'];
        }
    }

    public static function userOpenedProblemset($problemset_id, $user_id) {
        // User already started the problemset.
        $problemsetOpened = \OmegaUp\DAO\ProblemsetIdentities::getByPK($user_id, $problemset_id);

        if (!is_null($problemsetOpened) && !is_null($problemsetOpened->access_time)) {
            return true;
        }

        return false;
    }

    /**
     * Get the results for this user in a given interview
     *
     * @param \OmegaUp\Request $r
     */
    public static function apiInterviewStats(\OmegaUp\Request $r) {
        self::authenticateOrAllowUnauthenticatedRequest($r);

        \OmegaUp\Validators::validateStringNonEmpty($r['interview'], 'interview');
        \OmegaUp\Validators::validateStringNonEmpty($r['username'], 'username');

        $contest = \OmegaUp\DAO\Contests::getByAlias($r['interview']);
        if (is_null($contest)) {
            throw new \OmegaUp\Exceptions\NotFoundException('interviewNotFound');
        }

        // Only admins can view interview details
        if (!\OmegaUp\Authorization::isContestAdmin($r->identity->identity_id, $contest)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        $user = self::resolveTargetUser($r);
        if (is_null($user)) {
            throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
        }
        /** @var \OmegaUp\DAO\VO\Identities */
        $identity = \OmegaUp\DAO\Identities::getByPK($user->main_identity_id);

        return [
            'status' => 'ok',
            'user_verified' => $user->verified,
            'interview_url' => "https://omegaup.com/interview/{$contest->alias}/arena/",
            'name_or_username' => is_null($identity->name) ?
                                              $identity->username : $identity->name,
            'opened_interview' => self::userOpenedProblemset($contest->problemset_id, $user->user_id),
            'finished' => !\OmegaUp\DAO\Problemsets::insideSubmissionWindow($contest, $user->user_id),
        ];
    }

    /**
     * Get Contests which a certain user has participated in
     *
     * @param \OmegaUp\Request $r
     * @return \OmegaUp\DAO\VO\Contests array
     */
    public static function apiContestStats(\OmegaUp\Request $r) {
        self::authenticateOrAllowUnauthenticatedRequest($r);

        $response = [];
        $response['contests'] = [];

        $identity = self::resolveTargetIdentity($r);
        if (is_null($identity)) {
            throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
        }

        // Get contests where identity had at least 1 run
        $contestsParticipated = \OmegaUp\DAO\Contests::getContestsParticipated($identity->identity_id);

        $contests = [];

        foreach ($contestsParticipated as $contest) {
            // Get identity ranking
            $scoreboardResponse = \OmegaUp\Controllers\Contest::apiScoreboard(
                new \OmegaUp\Request([
                    'auth_token' => $r['auth_token'],
                    'contest_alias' => $contest['alias'],
                    'token' => $contest['scoreboard_url_admin'],
                ])
            );

            // Grab the place of the current identity in the given contest
            $contests[$contest['alias']]['place'] = null;
            foreach ($scoreboardResponse['ranking'] as $identityData) {
                if ($identityData['username'] == $identity->username) {
                    $contests[$contest['alias']]['place'] = $identityData['place'];
                    break;
                }
            }
            $contests[$contest['alias']]['data'] = $contest;
            foreach ($contest as $key => $item) {
                if ($key == 'start_time' || $key == 'finish_time' || $key == 'last_updated') {
                    $contests[$contest['alias']][$key] = \OmegaUp\DAO\DAO::fromMySQLTimestamp($item);
                }
            }
        }

        $response['contests'] = $contests;
        $response['status'] = 'ok';
        return $response;
    }

    /**
     * Get Problems solved by user
     *
     * @param \OmegaUp\Request $r
     * @return \OmegaUp\DAO\VO\Problems array
     */
    public static function apiProblemsSolved(\OmegaUp\Request $r) {
        self::authenticateOrAllowUnauthenticatedRequest($r);

        $identity = self::resolveTargetIdentity($r);
        if (is_null($identity)) {
            throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
        }
        $problems = \OmegaUp\DAO\Problems::getProblemsSolved($identity->identity_id);

        $response = [
            'status' => 'ok',
            'problems' => [],
        ];
        if (!is_null($problems)) {
            $relevant_columns = ['title', 'alias', 'submissions', 'accepted'];
            foreach ($problems as $problem) {
                if (\OmegaUp\DAO\Problems::isVisible($problem)) {
                    array_push($response['problems'], $problem->asFilteredArray($relevant_columns));
                }
            }
        }

        return $response;
    }

    /**
     * Get Problems unsolved by user
     *
     * @param \OmegaUp\Request $r
     * @return \OmegaUp\DAO\VO\Problems array
     */
    public static function apiListUnsolvedProblems(\OmegaUp\Request $r) {
        self::authenticateOrAllowUnauthenticatedRequest($r);
        $response = [
            'problems' => [],
            'status' => 'ok',
        ];

        $identity = self::resolveTargetIdentity($r);
        if (is_null($identity)) {
            throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
        }

        $problems = \OmegaUp\DAO\Problems::getProblemsUnsolvedByIdentity($identity->identity_id);

        $relevant_columns = ['title', 'alias', 'submissions', 'accepted', 'difficulty'];
        foreach ($problems as $problem) {
            if (\OmegaUp\DAO\Problems::isVisible($problem)) {
                array_push($response['problems'], $problem->asFilteredArray($relevant_columns));
            }
        }

        $response['status'] = 'ok';
        return $response;
    }

    /**
     * Gets a list of users. This returns an array instead of an object since
     * it is used by typeahead.
     *
     * @param \OmegaUp\Request $r
     */
    public static function apiList(\OmegaUp\Request $r) {
        $r->ensureIdentity();

        $param = '';
        if (!is_null($r['term'])) {
            $param = 'term';
        } elseif (!is_null($r['query'])) {
            $param = 'query';
        } else {
            throw new \OmegaUp\Exceptions\InvalidParameterException('parameterEmpty', 'query');
        }

        $identities = \OmegaUp\DAO\Identities::findByUsernameOrName($r[$param]);

        $response = [];
        foreach ($identities as $identity) {
            array_push($response, [
                'label' => $identity->username,
                'value' => $identity->username
            ]);
        }

        return $response;
    }

    /**
     * Get stats
     *
     * @param \OmegaUp\Request $r
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     */
    public static function apiStats(\OmegaUp\Request $r) {
        self::authenticateOrAllowUnauthenticatedRequest($r);
        $identity = self::resolveTargetIdentity($r);
        if (is_null($identity)) {
            throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
        }
        $user = null;
        if (!is_null($identity->user_id)) {
            $user = \OmegaUp\DAO\Users::getByPK($identity->user_id);
        }

        if ((is_null($r->identity) || $r->identity->username != $identity->username)
            && (is_null($r->identity) || (!is_null($r->identity) &&
                !\OmegaUp\Authorization::isSystemAdmin($r->identity)))
            && (!is_null($user) && $user->is_private == 1)
        ) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('userProfileIsPrivate');
        }

        return [
            'runs' => \OmegaUp\DAO\Runs::countRunsOfIdentityPerDatePerVerdict((int)$identity->identity_id),
            'status' => 'ok'
        ];
    }

    /**
     * Update basic user profile info when logged with fb/gool
     *
     * @param \OmegaUp\Request $r
     * @return array
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     */
    public static function apiUpdateBasicInfo(\OmegaUp\Request $r) {
        $r->ensureIdentity();

        //Buscar que el nuevo username no este ocupado si es que selecciono uno nuevo
        if ($r['username'] != $r->user->username) {
            $testu = \OmegaUp\DAO\Users::FindByUsername($r['username']);

            if (!is_null($testu)) {
                throw new \OmegaUp\Exceptions\InvalidParameterException('parameterUsernameInUse', 'username');
            }

            \OmegaUp\Validators::validateValidUsername($r['username'], 'username');
            $r->user->username = $r['username'];
            $r->identity->username = $r['username'];
        }

        \OmegaUp\SecurityTools::testStrongPassword($r['password']);
        $hashedPassword = \OmegaUp\SecurityTools::hashString($r['password']);
        $r->user->password = $hashedPassword;
        $r->identity->password = $hashedPassword;

        try {
            \OmegaUp\DAO\DAO::transBegin();

            // Update username and password for user object
            \OmegaUp\DAO\Users::update($r->user);

            // Update username and password for identity object
            \OmegaUp\DAO\Identities::update($r->identity);

            \OmegaUp\DAO\DAO::transEnd();
        } catch (\Exception $e) {
            \OmegaUp\DAO\DAO::transRollback();
            throw $e;
        }

        // Expire profile cache
        \OmegaUp\Cache::deleteFromCache(\OmegaUp\Cache::USER_PROFILE, $r->user->username);
        \OmegaUp\Controllers\Session::invalidateCache();

        return ['status' => 'ok'];
    }

    /**
     * Update user profile
     *
     * @param \OmegaUp\Request $r
     * @return array
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     */
    public static function apiUpdate(\OmegaUp\Request $r) {
        $r->ensureIdentity();

        if (isset($r['username'])) {
            \OmegaUp\Validators::validateValidUsername($r['username'], 'username');
            $user = \OmegaUp\DAO\Users::FindByUsername($r['username']);
            if ($r['username'] != $r->user->username && !is_null($user)) {
                throw new \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException('usernameInUse');
            }
        }

        if (!is_null($r['name'])) {
            \OmegaUp\Validators::validateStringOfLengthInRange($r['name'], 'name', 1, 50);
            $r->identity->name = $r['name'];
        }

        $state = null;
        if (!is_null($r['country_id']) || !is_null($r['state_id'])) {
            // Both state and country must be specified together.
            \OmegaUp\Validators::validateStringNonEmpty($r['country_id'], 'country_id');
            \OmegaUp\Validators::validateStringNonEmpty($r['state_id'], 'state_id');

            $state = \OmegaUp\DAO\States::getByPK($r['country_id'], $r['state_id']);
            if (is_null($state)) {
                throw new \OmegaUp\Exceptions\InvalidParameterException('parameterInvalid', 'state_id');
            }
            $r->identity->state_id = $state->state_id;
            $r->identity->country_id = $state->country_id;
        }

        if (!is_null($r['school_id'])) {
            if (is_numeric($r['school_id'])) {
                $r['school'] = \OmegaUp\DAO\Schools::getByPK($r['school_id']);
                if (is_null($r['school'])) {
                    throw new \OmegaUp\Exceptions\InvalidParameterException('parameterInvalid', 'school');
                }
                $r->identity->school_id = $r['school']->school_id;
            } elseif (empty($r['school_name'])) {
                $r['school_id'] = null;
            } else {
                $response = \OmegaUp\Controllers\School::apiCreate(new \OmegaUp\Request([
                    'name' => $r['school_name'],
                    'country_id' => $state != null ? $state->country_id : null,
                    'state_id' => $state != null ? $state->state_id : null,
                    'auth_token' => $r['auth_token'],
                ]));
                $r['school_id'] = $response['school_id'];
                $r->identity->school_id = $response['school_id'];
            }
        }

        \OmegaUp\Validators::validateOptionalStringNonEmpty($r['scholar_degree'], 'scholar_degree');

        if (!is_null($r['graduation_date'])) {
            if (is_numeric($r['graduation_date'])) {
                $graduationDate = intval($r['graduation_date']);
            } else {
                \OmegaUp\Validators::validateDate($r['graduation_date'], 'graduation_date');
                $graduationDate = strtotime($r['graduation_date']);
            }
            $r['graduation_date'] = $graduationDate;
        }
        if (!is_null($r['birth_date'])) {
            if (is_numeric($r['birth_date'])) {
                $birthDate = intval($r['birth_date']);
            } else {
                \OmegaUp\Validators::validateDate($r['birth_date'], 'birth_date');
                $birthDate = strtotime($r['birth_date']);
            }

            if ($birthDate >= strtotime('-5 year', \OmegaUp\Time::get())) {
                throw new \OmegaUp\Exceptions\InvalidParameterException('birthdayInTheFuture', 'birth_date');
            }
            $r['birth_date'] = $birthDate;
        }

        if (!is_null($r['locale'])) {
            // find language in Language
            $language = \OmegaUp\DAO\Languages::getByName($r['locale']);
            if (is_null($language)) {
                throw new \OmegaUp\Exceptions\InvalidParameterException('invalidLanguage', 'locale');
            }
            $r->identity->language_id = $language->language_id;
        }

        $r->ensureBool('is_private', false);
        $r->ensureBool('hide_problem_tags', false);

        if (!is_null($r['gender'])) {
            \OmegaUp\Validators::validateInEnum(
                $r['gender'],
                'gender',
                \OmegaUp\Controllers\User::ALLOWED_GENDER_OPTIONS,
                true
            );
            $r->identity->gender = $r['gender'];
        }

        $userValueProperties = [
            'username',
            'scholar_degree',
            'school_id',
            'graduation_date' => ['transform' => function ($value) {
                return gmdate('Y-m-d', $value);
            }],
            'birth_date' => ['transform' => function ($value) {
                return gmdate('Y-m-d', $value);
            }],
            'is_private',
            'hide_problem_tags',
        ];

        $identityValueProperties = [
            'username',
            'name',
            'country_id',
            'state_id',
            'school_id',
            'gender',
        ];

        self::updateValueProperties($r, $r->user, $userValueProperties);
        self::updateValueProperties($r, $r->identity, $identityValueProperties);

        try {
            \OmegaUp\DAO\DAO::transBegin();

            // Update user object
            \OmegaUp\DAO\Users::update($r->user);

            // Update identity object
            \OmegaUp\DAO\Identities::update($r->identity);

            \OmegaUp\DAO\DAO::transEnd();
        } catch (\Exception $e) {
            \OmegaUp\DAO\DAO::transRollback();

            throw $e;
        }

        // Expire profile cache
        \OmegaUp\Cache::deleteFromCache(\OmegaUp\Cache::USER_PROFILE, $r->user->username);
        \OmegaUp\Controllers\Session::invalidateCache();

        return ['status' => 'ok'];
    }

    /**
     * If no username provided: Gets the top N users who have solved more problems
     * If username provided: Gets rank for username provided
     *
     * @param \OmegaUp\Request $r
     * @return string
     */

    public static function apiRankByProblemsSolved(\OmegaUp\Request $r) {
        $r->ensureInt('offset', null, null, false);
        $r->ensureInt('rowcount', null, null, false);

        $identity = null;
        if (!is_null($r['username'])) {
            \OmegaUp\Validators::validateStringNonEmpty($r['username'], 'username');
            $identity = \OmegaUp\DAO\Identities::findByUsername($r['username']);
            if (is_null($identity)) {
                throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
            }
        }
        \OmegaUp\Validators::validateInEnum($r['filter'], 'filter', ['', 'country', 'state', 'school'], false);

        // Defaults for offset and rowcount
        if (null == $r['offset']) {
            $r['offset'] = 1;
        }
        if (null == $r['rowcount']) {
            $r['rowcount'] = 100;
        }

        return self::getRankByProblemsSolved($r, $identity);
    }

    /**
     * Get rank by problems solved logic. It has its own func so
     * it can be accesed internally without authentication
     *
     * @param \OmegaUp\Request $r
     */
    public static function getRankByProblemsSolved(
        \OmegaUp\Request $r,
        ?\OmegaUp\DAO\VO\Identities $identity
    ) : array {
        if (is_null($identity)) {
            $selectedFilter = self::getSelectedFilter($r);
            $rankCacheName = "{$r['offset']}-{$r['rowcount']}-{$r['filter']}-{$selectedFilter['value']}";
            $response = \OmegaUp\Cache::getFromCacheOrSet(
                \OmegaUp\Cache::PROBLEMS_SOLVED_RANK,
                $rankCacheName,
                function () use ($r) {
                    $response = [];
                    $response['rank'] = [];
                    $response['total'] = 0;
                    $selectedFilter = self::getSelectedFilter($r);
                    $userRankEntries = \OmegaUp\DAO\UserRank::getFilteredRank(
                        $r['offset'],
                        $r['rowcount'],
                        'rank',
                        'ASC',
                        $selectedFilter['filteredBy'],
                        $selectedFilter['value']
                    );

                    if (!is_null($userRankEntries)) {
                        $response['rank'] = $userRankEntries['rows'];
                        $response['total'] = $userRankEntries['total'];
                    }
                    return $response;
                },
                APC_USER_CACHE_USER_RANK_TIMEOUT
            );
        } else {
            $response = [];

            if (is_null($identity->user_id)) {
                $userRank = null;
            } else {
                $userRank = \OmegaUp\DAO\UserRank::getByPK($identity->user_id);
            }

            if (!is_null($userRank)) {
                $response['rank'] = $userRank->rank;
                $response['name'] = $identity->name;
                $response['problems_solved'] = $userRank->problems_solved_count;
            } else {
                $response['rank'] = 0;
                $response['name'] = $identity->name;
                $response['problems_solved'] = 0;
            }
        }

        $response['status'] = 'ok';
        return $response;
    }

    /**
     * Expires the known ranks
     * @TODO: This should be called only in the grader->frontend callback and only IFF
     * verdict = AC (and not test run)
     */
    public static function deleteProblemsSolvedRankCacheList() {
        \OmegaUp\Cache::invalidateAllKeys(\OmegaUp\Cache::PROBLEMS_SOLVED_RANK);
        \OmegaUp\Cache::invalidateAllKeys(\OmegaUp\Cache::CONTESTANT_SCOREBOARD_PREFIX);
        \OmegaUp\Cache::invalidateAllKeys(\OmegaUp\Cache::ADMIN_SCOREBOARD_PREFIX);
    }

    /**
     * Updates the main email of the current user
     *
     * @param \OmegaUp\Request $r
     */
    public static function apiUpdateMainEmail(\OmegaUp\Request $r) {
        $r->ensureIdentity();

        \OmegaUp\Validators::validateEmail($r['email'], 'email');

        try {
            \OmegaUp\DAO\DAO::transBegin();

            // Update email
            $email = \OmegaUp\DAO\Emails::getByPK($r->user->main_email_id);
            $email->email = $r['email'];
            \OmegaUp\DAO\Emails::update($email);

            // Add verification_id if not there
            if ($r->user->verified == '0') {
                self::$log->info('User not verified.');

                if ($r->user->verification_id == null) {
                    self::$log->info('User does not have verification id. Generating.');

                    try {
                        $r->user->verification_id = \OmegaUp\SecurityTools::randomString(50);
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
                throw new \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException('mailInUse', $e);
            }
            throw $e;
        }

        // Delete profile cache
        \OmegaUp\Cache::deleteFromCache(\OmegaUp\Cache::USER_PROFILE, $r->user->username);

        // Send verification email
        $r['user'] = $r->user;
        self::sendVerificationEmail($r['user']);

        return ['status' => 'ok'];
    }

    public static function makeUsernameFromEmail($email) {
        $newUsername = substr($email, 0, strpos($email, '@'));
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
     * @param \OmegaUp\Request $r
     */
    public static function apiValidateFilter(\OmegaUp\Request $r) {
        \OmegaUp\Validators::validateStringNonEmpty($r['filter'], 'filter');

        $response = [
            'status' => 'ok',
            'user' => null,
            'admin' => false,
            'problem_admin' => [],
            'contest_admin' => [],
            'problemset' => [],
        ];

        $session = \OmegaUp\Controllers\Session::apiCurrentSession($r)['session'];
        $identity = $session['identity'];
        if (!is_null($identity)) {
            $response['user'] = $identity->username;
            $response['admin'] = $session['is_admin'];
        }

        $filters = explode(',', $r['filter']);
        foreach ($filters as $filter) {
            $tokens = explode('/', $filter);
            if (count($tokens) < 2 || $tokens[0] != '') {
                throw new \OmegaUp\Exceptions\InvalidParameterException('parameterInvalid', 'filter');
            }
            switch ($tokens[1]) {
                case 'all-events':
                    if (count($tokens) != 2) {
                        throw new \OmegaUp\Exceptions\InvalidParameterException('parameterInvalid', 'filter');
                    }
                    if (!$session['is_admin']) {
                        throw new \OmegaUp\Exceptions\ForbiddenAccessException('userNotAllowed');
                    }
                    break;
                case 'user':
                    if (count($tokens) != 3) {
                        throw new \OmegaUp\Exceptions\InvalidParameterException('parameterInvalid', 'filter');
                    }
                    if (is_null($identity)) {
                        throw new \OmegaUp\Exceptions\ForbiddenAccessException('userNotAllowed');
                    }
                    if ($tokens[2] != $identity->username && !$session['is_admin']) {
                        throw new \OmegaUp\Exceptions\ForbiddenAccessException('userNotAllowed');
                    }
                    break;
                case 'contest':
                    if (count($tokens) < 3) {
                        throw new \OmegaUp\Exceptions\InvalidParameterException('parameterInvalid', 'filter');
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
                    $contestResponse = \OmegaUp\Controllers\Contest::validateDetails($r2);
                    if ($contestResponse['contest_admin']) {
                        $response['contest_admin'][] = $contestResponse['contest_alias'];
                    }
                    break;
                case 'problemset':
                    if (count($tokens) < 3) {
                        throw new \OmegaUp\Exceptions\InvalidParameterException('parameterInvalid', 'filter');
                    }
                    $r2 = \OmegaUp\Controllers\Problemset::wrapRequest(new \OmegaUp\Request([
                        'problemset_id' => $tokens[2],
                        'auth_token' => $r['auth_token'],
                        'tokens' => $tokens
                    ]));
                    if ($r2['contest_admin']) {
                        $response['contest_admin'][] = $r2['contest_alias'];
                    }
                    break;
                case 'problem':
                    if (count($tokens) != 3) {
                        throw new \OmegaUp\Exceptions\InvalidParameterException('parameterInvalid', 'filter');
                    }
                    $problem = \OmegaUp\DAO\Problems::getByAlias($tokens[2]);
                    if (is_null($problem)) {
                        throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
                    }
                    if (!is_null($identity) && \OmegaUp\Authorization::isProblemAdmin(
                        $identity,
                        $problem
                    )) {
                        $response['problem_admin'][] = $tokens[2];
                    } elseif (!\OmegaUp\DAO\Problems::isVisible($problem)) {
                        throw new \OmegaUp\Exceptions\ForbiddenAccessException('problemIsPrivate');
                    }

                    break;
            }
        }

        return $response;
    }

    private static function validateUser(\OmegaUp\Request $r) {
        // Validate request
        \OmegaUp\Validators::validateValidUsername($r['username'], 'username');
        $r['user'] = \OmegaUp\DAO\Users::FindByUsername($r['username']);
        if (is_null($r['user'])) {
            throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
        }
    }

    private static function validateAddRemoveRole(\OmegaUp\Request $r) {
        if (!\OmegaUp\Authorization::isSystemAdmin($r->identity) &&
            !OMEGAUP_ALLOW_PRIVILEGE_SELF_ASSIGNMENT) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        self::validateUser($r);

        \OmegaUp\Validators::validateStringNonEmpty($r['role'], 'role');
        $role = \OmegaUp\DAO\Roles::getByName($r['role']);
        /** @var int $role->role_id */
        if ($role->role_id == \OmegaUp\Authorization::ADMIN_ROLE
            && !OMEGAUP_ALLOW_PRIVILEGE_SELF_ASSIGNMENT
        ) {
            // System-admin role cannot be added/removed from the UI, only when OMEGAUP_ALLOW_PRIVILEGE_SELF_ASSIGNMENT flag is on.
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('userNotAllowed');
        }
        $r['role'] = $role;
    }

    private static function validateAddRemoveGroup(\OmegaUp\Request $r) : void {
        if (!OMEGAUP_ALLOW_PRIVILEGE_SELF_ASSIGNMENT) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('userNotAllowed');
        }

        self::validateUser($r);

        \OmegaUp\Validators::validateStringNonEmpty($r['group'], 'group');
        $group = \OmegaUp\DAO\Groups::getByName($r['group']);
        if (is_null($group)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException('parameterNotFound', 'group');
        }
        $r['group'] = $group;
    }

    /**
     * Adds the role to the user.
     *
     * @param \OmegaUp\Request $r
     */
    public static function apiAddRole(\OmegaUp\Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        $r->ensureIdentity();
        self::validateAddRemoveRole($r);

        \OmegaUp\DAO\UserRoles::create(new \OmegaUp\DAO\VO\UserRoles([
            'user_id' => $r['user']->user_id,
            'role_id' => $r['role']->role_id,
            'acl_id' => \OmegaUp\Authorization::SYSTEM_ACL,
        ]));

        return [
            'status' => 'ok',
        ];
    }

    /**
     * Removes the role from the user.
     *
     * @param \OmegaUp\Request $r
     */
    public static function apiRemoveRole(\OmegaUp\Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        $r->ensureIdentity();
        self::validateAddRemoveRole($r);

        \OmegaUp\DAO\UserRoles::delete(new \OmegaUp\DAO\VO\UserRoles([
            'user_id' => $r['user']->user_id,
            'role_id' => $r['role']->role_id,
            'acl_id' => \OmegaUp\Authorization::SYSTEM_ACL,
        ]));

        return [
            'status' => 'ok',
        ];
    }

    /**
     * Adds the identity to the group.
     *
     * @param \OmegaUp\Request $r
     */
    public static function apiAddGroup(\OmegaUp\Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        $r->ensureIdentity();
        self::validateAddRemoveGroup($r);
        \OmegaUp\DAO\GroupsIdentities::create(new \OmegaUp\DAO\VO\GroupsIdentities([
            'identity_id' => $r->identity->identity_id,
            'group_id' => $r['group']->group_id,
        ]));

        return [
            'status' => 'ok',
        ];
    }

    /**
     * Removes the user to the group.
     *
     * @param \OmegaUp\Request $r
     */
    public static function apiRemoveGroup(\OmegaUp\Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        $r->ensureIdentity();
        self::validateAddRemoveGroup($r);

        \OmegaUp\DAO\GroupsIdentities::delete(new \OmegaUp\DAO\VO\GroupsIdentities([
            'identity_id' => $r->identity->identity_id,
            'group_id' => $r['group']->group_id
        ]));

        return [
            'status' => 'ok',
        ];
    }

    private static function validateAddRemoveExperiment(\OmegaUp\Request $r) {
        global $experiments;

        /** @var \OmegaUp\DAO\VO\Identities $r->identity */
        if (!\OmegaUp\Authorization::isSystemAdmin($r->identity)) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException();
        }

        self::validateUser($r);

        \OmegaUp\Validators::validateStringNonEmpty($r['experiment'], 'experiment');
        if (!in_array($r['experiment'], $experiments->getAllKnownExperiments())) {
            throw new \OmegaUp\Exceptions\InvalidParameterException('parameterNotFound', 'experiment');
        }
    }

    /**
     * Adds the experiment to the user.
     *
     * @param \OmegaUp\Request $r
     */
    public static function apiAddExperiment(\OmegaUp\Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        $r->ensureIdentity();
        self::validateAddRemoveExperiment($r);

        \OmegaUp\DAO\UsersExperiments::create(new \OmegaUp\DAO\VO\UsersExperiments([
            'user_id' => $r['user']->user_id,
            'experiment' => $r['experiment'],
        ]));

        return [
            'status' => 'ok',
        ];
    }

    /**
     * Removes the experiment from the user.
     *
     * @param \OmegaUp\Request $r
     */
    public static function apiRemoveExperiment(\OmegaUp\Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new \OmegaUp\Exceptions\ForbiddenAccessException('lockdown');
        }

        $r->ensureIdentity();
        self::validateAddRemoveExperiment($r);

        \OmegaUp\DAO\UsersExperiments::delete($r['user']->user_id, $r['experiment']);

        return [
            'status' => 'ok',
        ];
    }

    /**
     * Gets the last privacy policy saved in the data base
     * @throws \OmegaUp\Exceptions\ForbiddenAccessException
     */
    public static function getPrivacyPolicy(\OmegaUp\Request $r) {
        $r->ensureIdentity();

        /** @var \OmegaUp\DAO\VO\Identities */
        $identity = self::resolveTargetIdentity($r);

        $lang = 'es';
        if ($identity->language_id == \OmegaUp\Controllers\User::LANGUAGE_EN ||
            $identity->language_id == \OmegaUp\Controllers\User::LANGUAGE_PSEUDO) {
            $lang = 'en';
        } elseif ($identity->language_id == \OmegaUp\Controllers\User::LANGUAGE_PT) {
            $lang = 'pt';
        }
        $latest_statement = \OmegaUp\DAO\PrivacyStatements::getLatestPublishedStatement();
        return [
            'status' => 'ok',
            'policy_markdown' => file_get_contents(
                OMEGAUP_ROOT . "/privacy/privacy_policy/{$lang}.md"
            ),
            'has_accepted' => \OmegaUp\DAO\PrivacyStatementConsentLog::hasAcceptedPrivacyStatement(
                $identity->identity_id,
                $latest_statement['privacystatement_id']
            ),
            'git_object_id' => $latest_statement['git_object_id'],
            'statement_type' => 'privacy_policy',
        ];
    }

    private static function getSelectedFilter($r) {
        $session = \OmegaUp\Controllers\Session::apiCurrentSession($r)['session'];
        if (!$session['valid']) {
            return ['filteredBy' => null, 'value' => null];
        }
        $identity = $session['identity'];
        $filteredBy = $r['filter'];
        if ($filteredBy == 'country') {
            return [
                'filteredBy' => $filteredBy,
                'value' => $identity->country_id
            ];
        }
        if ($filteredBy == 'state') {
            return [
                'filteredBy' => $filteredBy,
                'value' => "{$identity->country_id}-{$identity->state_id}"
            ];
        }
        if ($filteredBy == 'school') {
            return [
                'filteredBy' => $filteredBy,
                'value' => $identity->school_id
            ];
        }
        return ['filteredBy' => null, 'value' => null];
    }

    /**
     * Gets the last privacy policy accepted by user
     *
     * @param \OmegaUp\Request $r
     */
    public static function apiLastPrivacyPolicyAccepted(\OmegaUp\Request $r) {
        $r->ensureIdentity();

        /** @var \OmegaUp\DAO\VO\Identities */
        $identity = self::resolveTargetIdentity($r);
        return [
            'status' => 'ok',
            'hasAccepted' => \OmegaUp\DAO\PrivacyStatementConsentLog::hasAcceptedPrivacyStatement(
                $identity->identity_id,
                \OmegaUp\DAO\PrivacyStatements::getLatestPublishedStatement()['privacystatement_id']
            ),
        ];
    }

    /**
     * Keeps a record of a user who accepts the privacy policy
     *
     * @param \OmegaUp\Request $r
     * @throws \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException
     */
    public static function apiAcceptPrivacyPolicy(\OmegaUp\Request $r) {
        $r->ensureIdentity();
        $privacystatement_id = \OmegaUp\DAO\PrivacyStatements::getId($r['privacy_git_object_id'], $r['statement_type']);
        if (is_null($privacystatement_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('privacyStatementNotFound');
        }
        /** @var \OmegaUp\DAO\VO\Identities */
        $identity = self::resolveTargetIdentity($r);

        try {
            \OmegaUp\DAO\PrivacyStatementConsentLog::saveLog(
                intval($identity->identity_id),
                $privacystatement_id
            );
        } catch (\Exception $e) {
            if (\OmegaUp\DAO\DAO::isDuplicateEntryException($e)) {
                throw new \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException('userAlreadyAcceptedPrivacyPolicy', $e);
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
     * @throws \OmegaUp\Exceptions\InvalidParameterException
     * @throws \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException
     */
    public static function apiAssociateIdentity(\OmegaUp\Request $r) {
        global $experiments;
        $experiments->ensureEnabled(\OmegaUp\Experiments::IDENTITIES);
        $r->ensureIdentity();

        \OmegaUp\Validators::validateStringNonEmpty($r['username'], 'username');
        \OmegaUp\Validators::validateStringNonEmpty($r['password'], 'password');

        $identity = \OmegaUp\DAO\Identities::getUnassociatedIdentity($r['username']);
        if (is_null($identity)) {
            throw new \OmegaUp\Exceptions\InvalidParameterException('parameterInvalid', 'username');
        }

        if (\OmegaUp\DAO\Identities::isUserAssociatedWithIdentityOfGroup((int)$r->user->user_id, (int)$identity->identity_id)) {
            throw new \OmegaUp\Exceptions\DuplicatedEntryInDatabaseException('identityAlreadyAssociated');
        }

        /** @var string $identity->password */
        $passwordCheck = \OmegaUp\SecurityTools::compareHashedStrings(
            $r['password'],
            $identity->password
        );

        if ($passwordCheck === false) {
            throw new \OmegaUp\Exceptions\InvalidParameterException('parameterInvalid', 'password');
        }

        /** @var int $r->user->user_id */
        \OmegaUp\DAO\Identities::associateIdentityWithUser($r->user->user_id, $identity->identity_id);

        return ['status' => 'ok'];
    }

    /**
     * Get the identities that have been associated to the logged user
     *
     * @param \OmegaUp\Request $r
     */
    public static function apiListAssociatedIdentities(\OmegaUp\Request $r) {
        global $experiments;
        $experiments->ensureEnabled(\OmegaUp\Experiments::IDENTITIES);
        $r->ensureIdentity();

        return [
            'status' => 'ok',
            'identities' => \OmegaUp\DAO\Identities::getAssociatedIdentities($r->user->user_id)
        ];
    }

    /**
     * Generate a new gitserver token. This token can be used to authenticate
     * against the gitserver.
     */
    public static function apiGenerateGitToken(\OmegaUp\Request $r) {
        $r->ensureMainUserIdentity();

        $token = \OmegaUp\SecurityTools::randomHexString(40);
        /** @var \OmegaUp\DAO\VO\Users $r->user */
        $r->user->git_token = \OmegaUp\SecurityTools::hashString($token);
        \OmegaUp\DAO\Users::update($r->user);

        return [
            'status' => 'ok',
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
    ) : bool {
        return $identity->identity_id == $user->main_identity_id;
    }

    /**
     * Prepare all the properties to be sent to the rank table view via smarty
     * @param \OmegaUp\Request $r
     * @param \OmegaUp\DAO\VO\Identities $identity
     * @param \Smarty $smarty
     * @return array
     */
    public static function getRankDetailsForSmarty(
        \OmegaUp\Request $r,
        ?\OmegaUp\DAO\VO\Identities $identity,
        \Smarty $smarty
    ) : array {
        $r->ensureInt('page', null, null, false);
        $r->ensureInt('length', null, null, false);
        \OmegaUp\Validators::validateInEnum(
            $r['filter'],
            'filter',
            ['', 'country', 'state', 'school'],
            /*$required=*/false
        );

        $page = $r['page'] ?? 1;
        $length = $r['length'] ?? 100;
        $filter = $r['filter'] ?? '';

        $availableFilters = [];
        if (!is_null($identity)) {
            if (!is_null($identity->country_id)) {
                $availableFilters['country'] =
                    $smarty->getConfigVars('wordsFilterByCountry');
            }
            if (!is_null($identity->state_id)) {
                $availableFilters['state'] =
                    $smarty->getConfigVars('wordsFilterByState');
            }
            if (!is_null($identity->school_id)) {
                $availableFilters['school'] =
                    $smarty->getConfigVars('wordsFilterBySchool');
            }
        }

        return [
            'rankTablePayload' => [
                'isLogged' => !is_null($identity),
                'page' => $page,
                'length' => $length,
                'filter' => $filter,
                'availableFilters' => $availableFilters,
                'isIndex' => false,
            ],
        ];
    }

    /**
     * Prepare all the properties to be sent to the rank table view via smarty
     * @param \OmegaUp\Request $r
     * @param \OmegaUp\DAO\VO\Identities $identity
     * @return array
     */
    public static function getCoderOfTheMonthDetailsForSmarty(
        \OmegaUp\Request $r,
        ?\OmegaUp\DAO\VO\Identities $identity
    ) : array {
        $currentTimeStamp = \OmegaUp\Time::get();
        $currentDate = date('Y-m-d', $currentTimeStamp);
        $firstDayOfNextMonth = new \DateTime($currentDate);
        $firstDayOfNextMonth->modify('first day of next month');
        $dateToSelect = $firstDayOfNextMonth->format('Y-m-d');

        $isMentor = !is_null($identity) && \OmegaUp\Authorization::isMentor($identity);

        $response = [
            'codersOfCurrentMonth' => self::processCodersList(
                \OmegaUp\DAO\CoderOfTheMonth::getCodersOfTheMonth()
            ),
            'codersOfPreviousMonth' => self::processCodersList(
                \OmegaUp\DAO\CoderOfTheMonth::getMonthlyList($currentDate)
            ),
            'isMentor' => $isMentor,
        ];

        if (!$isMentor) {
            return ['payload' => $response];
        }
        $candidates = \OmegaUp\DAO\CoderOfTheMonth::calculateCoderOfMonthByGivenDate(
            $dateToSelect
        );
        $bestCoders = [];

        if (!is_null($candidates)) {
            foreach ($candidates as $candidate) {
                unset($candidate['user_id']);
                array_push($bestCoders, $candidate);
            }
        }
        $response['options'] = [
            'bestCoders' => $bestCoders,
            'canChooseCoder' =>
                \OmegaUp\Authorization::canChooseCoder($currentTimeStamp),
            'coderIsSelected' =>
                !empty(\OmegaUp\DAO\CoderOfTheMonth::getByTime($dateToSelect)),
        ];
        return ['payload' => $response];
    }

    private static function processCodersList(array $coders) : array {
        $response = [];
        foreach ($coders as $coder) {
            $userInfo = \OmegaUp\DAO\Users::FindByUsername($coder['username']);
            if (is_null($userInfo)) {
                throw new \OmegaUp\Exceptions\NotFoundException('userNotFound');
            }
            $classname = \OmegaUp\DAO\Users::getRankingClassName($userInfo->user_id);
            $hashEmail = md5($coder['email']);
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
}

\OmegaUp\Controllers\User::$urlHelper = new \OmegaUp\UrlHelper();
