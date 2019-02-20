<?php

require_once 'libs/Translations.php';
require_once 'libs/UrlHelper.php';

/**
 *  UserController
 *
 * @author joemmanuel
 */
class UserController extends Controller {
    public static $sendEmailOnVerify = true;
    public static $redirectOnVerify = true;
    public static $permissionKey = null;
    public static $urlHelper = null;
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
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     * @throws DuplicatedEntryInDatabaseException
     */
    public static function apiCreate(Request $r) {
        // Validate request
        Validators::isValidUsername($r['username'], 'username');

        Validators::isEmail($r['email'], 'email');

        if (empty($r['scholar_degree'])) {
            $r['scholar_degree'] = 'none';
        }

        Validators::isInEnum(
            $r['scholar_degree'],
            'scholar_degree',
            UserController::ALLOWED_SCHOLAR_DEGREES
        );

        // Check password
        $hashedPassword = null;
        if (!isset($r['ignore_password'])) {
            SecurityTools::testStrongPassword($r['password']);
            $hashedPassword = SecurityTools::hashString($r['password']);
        }

        // Does user or email already exists?
        try {
            $user = UsersDAO::FindByUsername($r['username']);
            $userByEmail = UsersDAO::FindByEmail($r['email']);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        if (!is_null($userByEmail)) {
            if (!is_null($userByEmail->password)) {
                // Check if the same user had already tried to create this account.
                if (!is_null($user) && $user->user_id == $userByEmail->user_id
                    && SecurityTools::compareHashedStrings(
                        $r['password'],
                        $user->password
                    )) {
                    return [
                        'status' => 'ok',
                        'username' => $user->username,
                    ];
                }
                throw new DuplicatedEntryInDatabaseException('mailInUse');
            }

            $user = new Users([
                'user_id' => $userByEmail->user_id,
                'username' => $r['username'],
                'password' => $hashedPassword
            ]);
            UsersDAO::savePassword($user);

            return [
                'status' => 'ok',
                'username' => $user->username,
            ];
        }

        if (!is_null($user)) {
            throw new DuplicatedEntryInDatabaseException('usernameInUse');
        }

        // Prepare DAOs
        $user_data = [
            'username' => $r['username'],
            'password' => $hashedPassword,
            'verified' => 0,
            'verification_id' => SecurityTools::randomString(50),
        ];
        if (isset($r['is_private'])) {
            $user_data['is_private'] = $r['is_private'];
        }
        if (isset($r['name'])) {
            $user_data['name'] = $r['name'];
        }
        if (isset($r['facebook_user_id'])) {
            $user_data['facebook_user_id'] = $r['facebook_user_id'];
        }
        if (!is_null(self::$permissionKey) &&
            self::$permissionKey == $r['permission_key']) {
            $user_data['verified'] = 1;
        } elseif (OMEGAUP_VALIDATE_CAPTCHA) {
            // Validate captcha
            if (!isset($r['recaptcha'])) {
                throw new InvalidParameterException('parameterNotFound', 'recaptcha');
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
                throw new CaptchaVerificationFailedException();
            }

            $resultAsJson = json_decode($result, true);
            if (is_null($resultAsJson)) {
                self::$log->error('Captcha response was not a json');
                self::$log->error('Here is the result:' . $result);
                throw new CaptchaVerificationFailedException();
            }

            if (!(array_key_exists('success', $resultAsJson) && $resultAsJson['success'])) {
                self::$log->error('Captcha response said no');
                throw new CaptchaVerificationFailedException();
            }
        }

        $user = new Users($user_data);
        $identity = new Identities($user_data);

        $email = new Emails([
            'email' => $r['email'],
        ]);

        // Save objects into DB
        try {
            DAO::transBegin();

            UsersDAO::save($user);

            $email->user_id = $user->user_id;
            EmailsDAO::save($email);

            $identity->user_id = $user->user_id;
            IdentitiesDAO::save($identity);

            $user->main_email_id = $email->email_id;
            $user->main_identity_id = $identity->identity_id;
            UsersDAO::save($user);

            $r['user'] = $user;
            if ($user->verified) {
                self::$log->info('User ' . $user->username . ' created, trusting e-mail');
            } else {
                self::$log->info('User ' . $user->username . ' created, sending verification mail');

                self::sendVerificationEmail($user);
            }

            DAO::transEnd();
        } catch (Exception $e) {
            DAO::transRollback();
            throw new InvalidDatabaseOperationException($e);
        }

        return [
            'status' => 'ok',
            'username' => $identity->username,
        ];
    }

    /**
     * Registers the created user to Sendy
     *
     * @param Request $r
     */
    private static function registerToSendy(Users $user) {
        if (!OMEGAUP_EMAIL_SENDY_ENABLE) {
            return false;
        }

        self::$log->info('Adding user to Sendy.');

        // Get email
        try {
            $email = EmailsDAO::getByPK($user->main_email_id);
        } catch (Exception $e) {
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
        if ($result === UserController::SENDY_SUCCESS) {
            self::$log->info('Success adding user to Sendy.');
        } else {
            self::$log->info('Failure adding user to Sendy.');
            return false;
        }

        return true;
    }

    /**
     *
     * Description:
     *     Tests a if a password is valid for a given user.
     *
     * @param user_id
     * @param email
     * @param username
     * @param password
     *
     * */
    public function TestPassword(Request $r) {
        $vo_UserToTest = null;

        //find this user
        if (!is_null($r['user_id'])) {
            $vo_UserToTest = UsersDAO::getByPK($r['user_id']);
        } elseif (!is_null($r['email'])) {
            $vo_UserToTest = $this->FindByEmail();
        } elseif (!is_null($r['username'])) {
            $vo_UserToTest = $this->FindByUserName();
        } elseif (!is_null($r['identity_id'])) {
            $vo_UserToTest = IdentitiesDAO::getByPK($r['identity_id']);
        } else {
            throw new ApiException('mustProvideUserIdEmailOrUsername');
        }

        if (is_null($vo_UserToTest)) {
            //user does not even exist
            return false;
        }

        if (strlen($vo_UserToTest->password) === 0) {
            throw new LoginDisabledException();
        }

        $newPasswordCheck = SecurityTools::compareHashedStrings(
            $r['password'],
            $vo_UserToTest->password
        );

        // We are OK
        if ($newPasswordCheck === true) {
            return true;
        }
    }

    /**
     * Send the mail with verification link to the user in the Request
     *
     * @param Request $r
     * @throws InvalidDatabaseOperationException
     * @throws EmailVerificationSendException
     */
    private static function sendVerificationEmail(Users $user) {
        try {
            $email = EmailsDAO::getByPK($user->main_email_id);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        if (!self::$sendEmailOnVerify) {
            self::$log->info('Not sending email beacause sendEmailOnVerify = FALSE');
            return;
        }

        $subject = Translations::getInstance()->get('verificationEmailSubject');
        $body = sprintf(
            Translations::getInstance()->get('verificationEmailBody'),
            OMEGAUP_URL,
            $user->verification_id
        );

        include_once 'libs/Email.php';
        Email::sendEmail($email->email, $subject, $body);
    }

    /**
     * Check if email of user in request has been verified
     *
     * @param Request $r
     * @throws EmailNotVerifiedException
     */
    public static function checkEmailVerification(Request $r) {
        if (!OMEGAUP_FORCE_EMAIL_VERIFICATION) {
            return;
        }
        // Check if they have been verified.
        if ($r['user']->verified != '0') {
            return;
        }
        self::$log->info('User not verified.');

        if ($r['user']->verification_id == null) {
            self::$log->info('User does not have verification id. Generating.');

            try {
                $r['user']->verification_id = SecurityTools::randomString(50);
                UsersDAO::save($r['user']);
            } catch (Exception $e) {
                // best effort, eat exception
            }

            self::sendVerificationEmail($r['user']);
        }

        throw new EmailNotVerifiedException();
    }

    /**
     * Exposes API /user/login
     * Expects in request:
     * user
     * password
     *
     *
     * @param Request $r
     */
    public static function apiLogin(Request $r) {
        // Create a SessionController to perform login
        $sessionController = new SessionController();

        // Require the auth_token back
        $r['returnAuthToken'] = true;

        // Get auth_token
        $auth_token = $sessionController->NativeLogin($r);

        // If user was correctly logged in
        if ($auth_token !== false) {
            return [
                'status' => 'ok',
                'auth_token' => $auth_token];
        } else {
            throw new InvalidCredentialsException();
        }
    }

    /**
     * Changes the password of a user
     *
     * @param Request $rﬁ
     * @return array
     * @throws ForbiddenAccessException
     */
    public static function apiChangePassword(Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        self::authenticateRequest($r);

        $hashedPassword = null;
        $user = $r['current_user'];
        if (isset($r['username']) && $r['username'] != $user->username) {
            // This is usable only in tests.
            if (is_null(self::$permissionKey) || self::$permissionKey != $r['permission_key']) {
                throw new ForbiddenAccessException();
            }
            Validators::isStringNonEmpty($r['username'], 'username');

            try {
                $user = UsersDAO::FindByUsername($r['username']);
                $identity = IdentitiesDAO::getByPK($user->main_identity_id);

                if (is_null($user)) {
                    throw new NotFoundException('userNotExist');
                }
            } catch (Exception $e) {
                throw new InvalidDatabaseOperationException($e);
            }

            if (isset($r['password']) && $r['password'] != '') {
                SecurityTools::testStrongPassword($r['password']);
                $hashedPassword = SecurityTools::hashString($r['password']);
            }
        } else {
            $identity = IdentitiesDAO::getByPK($user->main_identity_id);

            if ($user->password != null) {
                // Check the old password
                Validators::isStringNonEmpty($r['old_password'], 'old_password');

                $old_password_valid = SecurityTools::compareHashedStrings(
                    $r['old_password'],
                    $user->password
                );

                if ($old_password_valid === false) {
                    throw new InvalidParameterException('parameterInvalid', 'old_password');
                }
            }

            SecurityTools::testStrongPassword($r['password']);
            $hashedPassword = SecurityTools::hashString($r['password']);
        }

        $user->password = $hashedPassword;
        $identity->password = $hashedPassword;

        try {
            DAO::transBegin();

            UsersDAO::save($user);

            IdentitiesDAO::save($identity);

            DAO::transEnd();
        } catch (Exception $e) {
            DAO::transRollback();
            throw new InvalidDatabaseOperationException($e);
        }

        return ['status' => 'ok'];
    }

    /**
     * Verifies the user given its verification id
     *
     * @param Request $r
     * @return type
     * @throws ApiException
     * @throws InvalidDatabaseOperationException
     * @throws NotFoundException
     */
    public static function apiVerifyEmail(Request $r) {
        $user = null;

        // Admin can override verification by sending username
        if (isset($r['usernameOrEmail'])) {
            self::authenticateRequest($r);

            if (!Authorization::isSupportTeamMember($r['current_identity_id'])) {
                throw new ForbiddenAccessException();
            }

            self::$log->info('Admin verifiying user...' . $r['usernameOrEmail']);

            Validators::isStringNonEmpty($r['usernameOrEmail'], 'usernameOrEmail');

            $user = self::resolveUser($r['usernameOrEmail']);

            self::$redirectOnVerify = false;
        } else {
            // Normal user verification path
            Validators::isStringNonEmpty($r['id'], 'id');

            try {
                $users = UsersDAO::getByVerification($r['id']);

                $user = !empty($users) ? $users[0] : null;
            } catch (Exception $e) {
                throw new InvalidDatabaseOperationException($e);
            }
        }

        if (is_null($user)) {
            throw new NotFoundException('verificationIdInvalid');
        }

        try {
            $user->verified = 1;
            UsersDAO::save($user);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        self::$log->info('User verification complete.');

        if (self::$redirectOnVerify) {
            if (!is_null($r['redirecttointerview'])) {
                die(header('Location: /login/?redirect=/interview/' . urlencode($r['redirecttointerview']) . '/arena'));
            } else {
                die(header('Location: /login/'));
            }
        }

        // Expire profile cache
        Cache::deleteFromCache(Cache::USER_PROFILE, $user->username);

        return ['status' => 'ok'];
    }

    /**
     * Registers to the mailing list all users that have not been added before. Admin only
     *
     * @throws InvalidDatabaseOperationException
     * @throws InvalidParameterException
     * @throws ForbiddenAccessException
     */
    public static function apiMailingListBackfill(Request $r) {
        self::authenticateRequest($r);

        if (!Authorization::isSystemAdmin($r['current_identity_id'])) {
            throw new ForbiddenAccessException();
        }

        $usersAdded = [];

        try {
            $usersMissing = UsersDAO::getVerified(
                true, // verified
                false // in_mailing_list
            );

            foreach ($usersMissing as $user) {
                $registered = self::registerToSendy($user);

                if ($registered) {
                    $user->in_mailing_list = 1;
                    UsersDAO::save($user);
                }

                $usersAdded[$user->username] = $registered;
            }
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        return [
            'status' => 'ok',
            'users' => $usersAdded
        ];
    }

    /**
     * Given a username or a email, returns the user object
     *
     * @param type $userOrEmail
     * @return User
     * @throws ApiException
     * @throws InvalidDatabaseOperationException
     * @throws InvalidParameterException
     */
    public static function resolveUser($userOrEmail) {
        Validators::isStringNonEmpty($userOrEmail, 'usernameOrEmail');

        $user = null;

        try {
            if (!is_null($user = UsersDAO::FindByEmail($userOrEmail))
                    || !is_null($user = UsersDAO::FindByUsername($userOrEmail))) {
                return $user;
            } else {
                throw new NotFoundException('userOrMailNotFound');
            }
        } catch (ApiException $apiException) {
            throw $apiException;
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        return $user;
    }

    /**
     * Resets the password of the OMI user and adds the user to the private
     * contest.
     * If the user does not exists, we create him.
     *
     * @param Request $r
     * @param string $username
     * @param string $password
     */
    private static function omiPrepareUser(Request $r, $username, $password) {
        try {
            $user = UsersDAO::FindByUsername($username);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        if (is_null($user)) {
            self::$log->info('Creating user: ' . $username);
            $createRequest = new Request([
                'username' => $username,
                'password' => $password,
                'email' => $username . '@omi.com',
                'permission_key' => $r['permission_key']
            ]);

            UserController::$sendEmailOnVerify = false;
            self::apiCreate($createRequest);
            return true;
        } elseif (is_null($r['change_password']) || $r['change_password'] !== 'false') {
            if (!$user->verified) {
                self::apiVerifyEmail(new Request([
                    'auth_token' => $r['auth_token'],
                    'usernameOrEmail' => $username
                ]));
            }

            // Pwd changes are by default unless explictly disabled
            $resetRequest = new Request();
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
     * @param Request $r
     * @return array
     * @throws ForbiddenAccessException
     */
    public static function apiGenerateOmiUsers(Request $r) {
        self::authenticateRequest($r);

        $response = [];

        $is_system_admin = Authorization::isSystemAdmin($r['current_identity_id']);
        if ($r['contest_type'] == 'OMI') {
            if ($r['current_user']->username != 'andreasantillana'
                && !$is_system_admin
            ) {
                throw new ForbiddenAccessException();
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
            if ($r['current_user']->username != 'andreasantillana'
                && !$is_system_admin
            ) {
                throw new ForbiddenAccessException();
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
            if ($r['current_user']->username != 'andreasantillana'
                && !$is_system_admin
            ) {
                throw new ForbiddenAccessException();
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
            if ($r['current_user']->username != 'andreasantillana'
                && !$is_system_admin
            ) {
                throw new ForbiddenAccessException();
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
            if ($r['current_user']->username != 'andreasantillana'
                && !$is_system_admin
            ) {
                throw new ForbiddenAccessException();
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
            if ($r['current_user']->username != 'kuko.coder'
                && !$is_system_admin
            ) {
                throw new ForbiddenAccessException();
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
            if ($r['current_user']->username != 'rsolis'
                && !$is_system_admin
            ) {
                throw new ForbiddenAccessException();
            }

            $keys =  [
                'OMIZAC-2018' => 20
            ];
        } elseif ($r['contest_type'] == 'Pr8oUAIE') {
            if ($r['current_user']->username != 'rsolis'
                && !$is_system_admin
            ) {
                throw new ForbiddenAccessException();
            }

            $keys =  [
                'Pr8oUAIE' => 20
            ];
        } elseif ($r['contest_type'] == 'OMIZAC') {
            if ($r['current_user']->username != 'rsolis'
                && !$is_system_admin
            ) {
                throw new ForbiddenAccessException();
            }

            $keys =  [
                'OMIZAC-Prim' => 60,
                'OMIZAC-Sec' => 60,
                'OMIZAC-Prepa' => 60
            ];
        } elseif ($r['contest_type'] == 'ProgUAIE') {
            if ($r['current_user']->username != 'rsolis'
                && !$is_system_admin
            ) {
                throw new ForbiddenAccessException();
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
            if ($r['current_user']->username != 'EfrenGonzalez'
                && !$is_system_admin
            ) {
                throw new ForbiddenAccessException();
            }

            $keys =  [
                'OMIAGS-2018' => 30
            ];
        } elseif ($r['contest_type'] == 'OMIAGS-2017') {
            if ($r['current_user']->username != 'EfrenGonzalez'
                && !$is_system_admin
            ) {
                throw new ForbiddenAccessException();
            }

            $keys =  [
                'OMIAGS-2017' => 30
            ];
        } elseif ($r['contest_type'] == 'OMIP-AGS') {
            if ($r['current_user']->username != 'EfrenGonzalez'
                && !$is_system_admin
            ) {
                throw new ForbiddenAccessException();
            }

            $keys =  [
                'OMIP-AGS' => 30
            ];
        } elseif ($r['contest_type'] == 'OMIS-AGS') {
            if ($r['current_user']->username != 'EfrenGonzalez'
                && !$is_system_admin
            ) {
                throw new ForbiddenAccessException();
            }

            $keys =  [
                'OMIS-AGS' => 30
            ];
        } elseif ($r['contest_type'] == 'OSI') {
            if ($r['current_user']->username != 'cope_quintana'
                && !$is_system_admin
            ) {
                throw new ForbiddenAccessException();
            }

            $keys =  [
                'OSI16' => 120
            ];
        } elseif ($r['contest_type'] == 'UNAMFC') {
            if ($r['current_user']->username != 'manuelalcantara52'
                && $r['current_user']->username != 'manuel52'
                && !$is_system_admin
            ) {
                throw new ForbiddenAccessException();
            }
            $keys =  [
                'UNAMFC16' => 65
            ];
        } elseif ($r['contest_type'] == 'OVI') {
            if ($r['current_user']->username != 'covi.academico'
                && !$is_system_admin
            ) {
                throw new ForbiddenAccessException();
            }
            $keys =  [
                'OVI19' => 200
            ];
        } elseif ($r['contest_type'] == 'UDCCUP') {
            if ($r['current_user']->username != 'Diego_Briaares'
                && !$is_system_admin
            ) {
                throw new ForbiddenAccessException();
            }
            $keys =  [
                'UDCCUP-2017' => 40
            ];
        } elseif ($r['contest_type'] == 'CCUPITSUR') {
            if ($r['current_user']->username != 'licgerman-yahoo'
                && !$is_system_admin
            ) {
                throw new ForbiddenAccessException();
            }
            // Arreglo de concurso
            $keys = [
                'CCUPITSUR-16' => 50,
            ];
        } elseif ($r['contest_type'] == 'CONALEP') {
            if ($r['current_user']->username != 'reyes811'
                && !$is_system_admin
            ) {
                throw new ForbiddenAccessException();
            }
            $keys =  [
                'OIC-16' => 225
            ];
        } elseif ($r['contest_type'] == 'OMIQROO') {
            if ($r['current_user']->username != 'pablobatun'
                && !$is_system_admin
            ) {
                throw new ForbiddenAccessException();
            }
            $keys = [
                'OMIROOD1-19' => 60,
                'OMIROOD2-19' => 60,
            ];
        } elseif ($r['contest_type'] == 'TEBAEV') {
            if ($r['current_user']->username != 'lacj20'
                && !$is_system_admin
            ) {
                throw new ForbiddenAccessException();
            }
            $keys = [
                'TEBAEV' => 250,
            ];
        } elseif ($r['contest_type'] == 'PYE-AGS') {
            if ($r['current_user']->username != 'joemmanuel'
                && !$is_system_admin
            ) {
                throw new ForbiddenAccessException();
            }
            $keys = [
                'PYE-AGS18' => 40,
            ];
        } elseif ($r['contest_type'] == 'CAPKnuth') {
            if ($r['current_user']->username != 'galloska'
                && !$is_system_admin
            ) {
                throw new ForbiddenAccessException();
            }
            $keys = [
                'ESCOM2018' => 50,
            ];
        } elseif ($r['contest_type'] == 'CAPVirtualKnuth') {
            if ($r['current_user']->username != 'galloska'
                && !$is_system_admin
            ) {
                throw new ForbiddenAccessException();
            }
            $keys = [
                'Virtual-ESCOM2018' => 50,
            ];
        } else {
            throw new InvalidParameterException(
                'parameterNotInExpectedSet',
                'contest_type',
                [
                    'bad_elements' => $r['contest_type'],
                    'expected_set' => 'OMI, OMIAGS, OMIP-AGS, OMIS-AGS, ORIG, OSI, OVI, UDCCUP, CCUPITSUR, CONALEP, OMIQROO, OMIAGS-2017, OMIAGS-2018, PYE-AGS, OMIZAC-2018, Pr8oUAIE, CAPKnuth, CAPVirtualKnuth, OMIZAC, ProgUAIE',
                ]
            );
        }

        self::$permissionKey = $r['permission_key'] = SecurityTools::randomString(32);

        foreach ($keys as $k => $n) {
            $digits = floor(log10($n) + 1);
            for ($i = 1; $i <= $n; $i++) {
                $username = $k . '-' . str_pad($i, $digits, '0', STR_PAD_LEFT);
                $password = SecurityTools::randomString(8);

                if (self::omiPrepareUser($r, $username, $password)) {
                    $response[$username] = $password;
                }

                // Add user to contest if needed
                if (!is_null($r['contest_alias'])) {
                    $addUserRequest = new Request();
                    $addUserRequest['auth_token'] = $r['auth_token'];
                    $addUserRequest['usernameOrEmail'] = $username;
                    $addUserRequest['contest_alias'] = $r['contest_alias'];
                    ContestController::apiAddUser($addUserRequest);
                }
            }
        }

        return $response;
    }

    /**
     * Returns the prefered language as a string (en,es,fra) of the user given
     * If no user is give, language is retrived from the browser.
     *
     * @param Users $user
     * @return String
     */
    public static function getPreferredLanguage(Request $r = null) {
        // for quick debugging
        if (isset($_GET['lang'])) {
            return UserController::convertToSupportedLanguage($_GET['lang']);
        }

        try {
            $user = self::resolveTargetUser($r);
            if (!is_null($user) && !is_null($user->language_id)) {
                $result = LanguagesDAO::getByPK($user->language_id);
                if (is_null($result)) {
                    self::$log->warn('Invalid language id for user');
                } else {
                    return UserController::convertToSupportedLanguage($result->name);
                }
            }
        } catch (NotFoundException $ex) {
            self::$log->debug($ex);
        } catch (InvalidParameterException $ex) {
            self::$log->debug($ex);
        }

        $langs = [];

        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            // break up string into pieces (languages and q factors)
            preg_match_all('/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $lang_parse);

            if (count($lang_parse[1])) {
                // create a list like "en" => 0.8
                $langs = array_combine($lang_parse[1], $lang_parse[4]);

                // set default to 1 for any without q factor
                foreach ($langs as $lang => $val) {
                    if ($val === '') {
                        $langs[$lang] = 1;
                    }
                }

                // sort list based on value
                arsort($langs, SORT_NUMERIC);
            }
        }

        foreach ($langs as $langCode => $langWeight) {
            switch (substr($langCode, 0, 2)) {
                case 'en':
                    return 'en';

                case 'es':
                    return 'es';

                case 'pt':
                    return 'pt';
            }
        }

        // Fallback to spanish.
        return 'es';
    }

    private static function convertToSupportedLanguage($lang) {
        switch ($lang) {
            case 'en':
            case 'en-us':
                return 'en';

            case 'es':
            case 'es-mx':
                return 'es';

            case 'pt':
            case 'pt-pt':
            case 'pt-br':
                return 'pt';

            case 'pseudo':
                return 'pseudo';
        }

        // Fallback to spanish.
        return 'es';
    }

    /**
     * Returns the profile of the user given
     *
     * @param Users $user
     * @return array
     * @throws InvalidDatabaseOperationException
     */
    public static function getProfileImpl(Users $user) {
        $response = [];
        $response['userinfo'] = [];

        $response['userinfo'] = [
            'username' => $user->username,
            'name' => $user->name,
            'birth_date' => is_null($user->birth_date) ? null : strtotime($user->birth_date),
            'gender' => $user->gender,
            'graduation_date' => is_null($user->graduation_date) ? null : strtotime($user->graduation_date),
            'scholar_degree' => $user->scholar_degree,
            'preferred_language' => $user->preferred_language,
            'is_private' => $user->is_private,
            'verified' => $user->verified == '1',
            'hide_problem_tags' => is_null($user->hide_problem_tags) ? null : $user->hide_problem_tags,
        ];

        if (!is_null($user->language_id)) {
            $query = LanguagesDAO::getByPK($user->language_id);
            if (!is_null($query)) {
                $response['userinfo']['locale'] =
                    UserController::convertToSupportedLanguage($query->name);
            }
        }

        try {
            $user_db = UsersDAO::getExtendedProfileDataByPk($user->user_id);

            $response['userinfo']['email'] = $user_db['email'];
            $response['userinfo']['country'] = $user_db['country'];
            $response['userinfo']['country_id'] = $user->country_id ?? 'xx';
            $response['userinfo']['state'] = $user_db['state'];
            $response['userinfo']['state_id'] = $user->state_id;
            $response['userinfo']['school'] = $user_db['school'];
            $response['userinfo']['school_id'] = $user->school_id;

            if (!is_null($user->language_id)) {
                $response['userinfo']['locale'] = UserController::convertToSupportedLanguage($user_db['locale']);
            }
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        $response['userinfo']['gravatar_92'] = 'https://secure.gravatar.com/avatar/' . md5($response['userinfo']['email']) . '?s=92';

        return $response;
    }

    /**
     * Get general user info
     *
     * @param Request $r
     * @return response array with user info
     * @throws InvalidDatabaseOperationException
     */
    public static function apiProfile(Request $r) {
        self::authenticateOrAllowUnauthenticatedRequest($r);

        $r['identity'] = self::resolveTargetIdentity($r);
        $r['user'] = self::resolveTargetUser($r);

        $response = IdentityController::getProfile($r);
        if ((is_null($r['current_identity']) || $r['current_identity']->username != $r['identity']->username)
            && (isset($r['user']) && $r['user']->is_private == 1) && !Authorization::isSystemAdmin($r['current_identity_id'])) {
            $response['problems'] = [];
            foreach ($response['userinfo'] as $k => $v) {
                $response['userinfo'][$k] = null;
            }
            $response['userinfo']['username'] = $r['identity']->username;
            $response['userinfo']['rankinfo'] = [
                'name' => null,
                'problems_solved' => null,
                'rank' => null,
                'status' => 'ok',
            ];
            $response['userinfo']['is_private'] = true;
        }
        $response['userinfo']['classname'] = UsersDAO::getRankingClassName($r['identity']->user_id);
        $response['status'] = 'ok';
        return $response;
    }

    /**
     * Gets verify status of a user
     *
     * @param Request $r
     * @return response array
     * @throws ForbiddenAccessException
     * @throws InvalidParameterException
     */
    public static function apiStatusVerified(Request $r) {
        self::authenticateRequest($r);

        if (!Authorization::isSupportTeamMember($r['current_identity_id'])) {
            throw new ForbiddenAccessException();
        }

        $response = IdentitiesDAO::getStatusVerified($r['email']);

        if (is_null($response)) {
            throw new InvalidParameterException('invalidUser');
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
     * @param Request $r
     * @return response array
     * @throws ForbiddenAccessException
     * @throws InvalidParameterException
     */
    public static function apiExtraInformation(Request $r) {
        self::authenticateRequest($r);

        if (!Authorization::isSupportTeamMember($r['current_identity_id'])) {
            throw new ForbiddenAccessException();
        }

        $response = IdentitiesDAO::getExtraInformation($r['email']);

        if (is_null($response)) {
            throw new InvalidParameterException('invalidUser');
        }

        return [
            'status' => 'ok',
            'username' => $response['username'],
            'within_last_day' => $response['within_last_day'],
            'verified' => $response['verified'],
        ];
    }

    /**
     * Get coder of the month by trying to find it in the table using the first
     * day of the current month. If there's no coder of the month for the given
     * date, calculate it and save it.
     *
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     */
    public static function apiCoderOfTheMonth(Request $r) {
        $currentTimestamp = Time::get();
        if (!empty($r['date'])) {
            Validators::isDate($r['date'], 'date', false);
            $firstDay = date('Y-m-01', strtotime($r['date']));
        } else {
            // Get first day of the current month
            $firstDay = date('Y-m-01', $currentTimestamp);
        }

        try {
            $codersOfTheMonth = CoderOfTheMonthDAO::getByTime($firstDay);

            if (empty($codersOfTheMonth)) {
                // Generate the coder
                $users = CoderOfTheMonthDAO::calculateCoderOfMonthByGivenDate($firstDay);
                if (is_null($users)) {
                    return [
                        'status' => 'ok',
                        'userinfo' => null,
                        'problems' => null,
                    ];
                }

                // Only first place coder is saved
                CoderOfTheMonthDAO::save(new CoderOfTheMonth([
                    'user_id' => $users[0]['user_id'],
                    'time' => $firstDay,
                    'rank' => 1,
                ]));
                $coderOfTheMonthUserId = $users[0]['user_id'];
            } else {
                $coderOfTheMonthUserId = $codersOfTheMonth[0]->user_id;
            }
            $user = UsersDAO::getByPK($coderOfTheMonthUserId);
        } catch (Exception $e) {
            self::$log->error('Unable to get coder of the month: ' . $e);
            throw new InvalidDatabaseOperationException($e);
        }

        // Get the profile of the coder of the month
        $response = UserController::getProfileImpl($user);

        // But avoid divulging the email in the response.
        unset($response['userinfo']['email']);

        $response['status'] = 'ok';
        return $response;
    }

    /**
     * Returns the list of coders of the month
     *
     * @param Request $r
     */
    public static function apiCoderOfTheMonthList(Request $r) {
        $response = [];
        $response['coders'] = [];
        try {
            $coders = [];
            if (!empty($r['date'])) {
                $coders = CoderOfTheMonthDAO::getMonthlyList($r['date']);
            } else {
                $coders = CoderOfTheMonthDAO::getCodersOfTheMonth();
            }
            foreach ($coders as $c) {
                $userInfo = UsersDAO::FindByUsername($c['username']);
                $classname = UsersDAO::getRankingClassName($userInfo->user_id);
                $response['coders'][] = [
                    'username' => $c['username'],
                    'country_id' => $c['country_id'],
                    'gravatar_32' => 'https://secure.gravatar.com/avatar/' . md5($c['email']) . '?s=32',
                    'date' => $c['time'],
                    'classname' => $classname,
                ];
            }
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        $response['status'] = 'ok';
        return $response;
    }

    /**
     * Selects coder of the month for next month.
     *
     * @param Request $r
     * @return Array
     * @throws ForbiddenAccessException
     * @throws DuplicatedEntryInDatabaseException
     * @throws NotFoundException
     * @throws InvalidDatabaseOperationException
     */
    public static function apiSelectCoderOfTheMonth(Request $r) {
        self::authenticateRequest($r);
        $currentTimestamp = Time::get();

        if (!Authorization::isMentor($r['current_identity_id'])) {
            throw new ForbiddenAccessException('userNotAllowed');
        }
        if (!Authorization::canChooseCoder($currentTimestamp)) {
            throw new ForbiddenAccessException('coderOfTheMonthIsNotInPeriodToBeChosen');
        }
        Validators::isStringNonEmpty($r['username'], 'username');

        $currentDate = date('Y-m-d', $currentTimestamp);
        $firstDayOfNextMonth = new DateTime($currentDate);
        $firstDayOfNextMonth->modify('first day of next month');
        $dateToSelect = $firstDayOfNextMonth->format('Y-m-d');

        try {
            $codersOfTheMonth = CoderOfTheMonthDAO::getByTime($dateToSelect);

            if (!empty($codersOfTheMonth)) {
                throw new DuplicatedEntryInDatabaseException('coderOfTheMonthAlreadySelected');
            }
            // Generate the coder
            $users = CoderOfTheMonthDAO::calculateCoderOfMonthByGivenDate($dateToSelect);

            if (empty($users)) {
                throw new NotFoundException('noCoders');
            }

            foreach ($users as $index => $user) {
                if ($user['username'] != $r['username']) {
                    continue;
                }

                // Save it
                CoderOfTheMonthDAO::save(new CoderOfTheMonth([
                    'user_id' => $user['user_id'],
                    'time' => $dateToSelect,
                    'rank' => $index + 1,
                    'selected_by' => $r['current_identity_id'],
                ]));

                return ['status' => 'ok'];
            }
        } catch (Exception $e) {
            self::$log->error('Unable to select coder of the month: ' . $e);
            throw new InvalidDatabaseOperationException($e);
        }

        throw new InvalidDatabaseOperationException();
    }

    public static function userOpenedProblemset($problemset_id, $user_id) {
        // User already started the problemset.
        $problemsetOpened = ProblemsetIdentitiesDAO::getByPK($user_id, $problemset_id);

        if (!is_null($problemsetOpened) && !is_null($problemsetOpened->access_time)) {
            return true;
        }

        return false;
    }

    /**
     * Get the results for this user in a given interview
     *
     * @param Request $r
     * @throws InvalidDatabaseOperationException
     */
    public static function apiInterviewStats(Request $r) {
        self::authenticateOrAllowUnauthenticatedRequest($r);

        Validators::isStringNonEmpty($r['interview'], 'interview');
        Validators::isStringNonEmpty($r['username'], 'username');

        $contest = ContestsDAO::getByAlias($r['interview']);
        if (is_null($contest)) {
            throw new NotFoundException('interviewNotFound');
        }

        // Only admins can view interview details
        if (!Authorization::isContestAdmin($r['current_identity_id'], $contest)) {
            throw new ForbiddenAccessException();
        }

        $response = [];
        $user = self::resolveTargetUser($r);

        $openedProblemset = self::userOpenedProblemset($contest->problemset_id, $user->user_id);

        $response['user_verified'] = $user->verified === '1';
        $response['interview_url'] = 'https://omegaup.com/interview/' . $contest->alias . '/arena';
        $response['name_or_username'] = is_null($user->name) ? $user->username : $user->name;
        $response['opened_interview'] = $openedProblemset;
        $response['finished'] = !ProblemsetsDAO::insideSubmissionWindow($contest, $user->user_id);
        $response['status'] = 'ok';
        return $response;
    }

    /**
     * Get Contests which a certain user has participated in
     *
     * @param Request $r
     * @return Contests array
     * @throws InvalidDatabaseOperationException
     */
    public static function apiContestStats(Request $r) {
        self::authenticateOrAllowUnauthenticatedRequest($r);

        $response = [];
        $response['contests'] = [];

        $identity = self::resolveTargetIdentity($r);

        // Get contests where identity had at least 1 run
        try {
            $contestsParticipated = ContestsDAO::getContestsParticipated($identity->identity_id);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        $contests = [];

        foreach ($contestsParticipated as $contest) {
            // Get identity ranking
            $scoreboardResponse = ContestController::apiScoreboard(
                new Request([
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
                    $contests[$contest['alias']][$key] = strtotime($item);
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
     * @param Request $r
     * @return Problems array
     * @throws InvalidDatabaseOperationException
     */
    public static function apiProblemsSolved(Request $r) {
        self::authenticateOrAllowUnauthenticatedRequest($r);

        $response = [];
        $response['problems'] = [];

        $identity = self::resolveTargetIdentity($r);

        try {
            $problems = ProblemsDAO::getProblemsSolved($identity->identity_id);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        if (!is_null($problems)) {
            $relevant_columns = ['title', 'alias', 'submissions', 'accepted'];
            foreach ($problems as $problem) {
                if (ProblemsDAO::isVisible($problem)) {
                    array_push($response['problems'], $problem->asFilteredArray($relevant_columns));
                }
            }
        }

        $response['status'] = 'ok';
        return $response;
    }

    /**
     * Get Problems unsolved by user
     *
     * @param Request $r
     * @return Problems array
     * @throws InvalidDatabaseOperationException
     */
    public static function apiListUnsolvedProblems(Request $r) {
        self::authenticateOrAllowUnauthenticatedRequest($r);
        $response = [
            'problems' => [],
            'status' => 'ok',
        ];

        $identity = self::resolveTargetIdentity($r);

        try {
            $problems = ProblemsDAO::getProblemsUnsolvedByIdentity($identity->identity_id);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        $relevant_columns = ['title', 'alias', 'submissions', 'accepted', 'difficulty'];
        foreach ($problems as $problem) {
            if (ProblemsDAO::isVisible($problem)) {
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
     * @param Request $r
     */
    public static function apiList(Request $r) {
        self::authenticateRequest($r);

        $param = '';
        if (!is_null($r['term'])) {
            $param = 'term';
        } elseif (!is_null($r['query'])) {
            $param = 'query';
        } else {
            throw new InvalidParameterException('parameterEmpty', 'query');
        }

        try {
            $users = UsersDAO::FindByUsernameOrName($r[$param]);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        $response = [];
        foreach ($users as $user) {
            $entry = ['label' => $user->username, 'value' => $user->username];
            array_push($response, $entry);
        }

        return $response;
    }

    /**
     * Get stats
     *
     * @param Request $r
     * @throws ForbiddenAccessException
     */
    public static function apiStats(Request $r) {
        self::authenticateOrAllowUnauthenticatedRequest($r);
        $identity = self::resolveTargetIdentity($r);
        $user = null;
        if (!is_null($identity->user_id)) {
            $user = self::resolveTargetUser($r);
        }

        if ((is_null($r['current_identity']) || $r['current_identity']->username != $identity->username)
            && (!is_null($user) && $user->is_private == 1) && !Authorization::isSystemAdmin($r['current_identity_id'])) {
            throw new ForbiddenAccessException('userProfileIsPrivate');
        }

        try {
            $runsPerDatePerVerdict = RunsDAO::CountRunsOfIdentityPerDatePerVerdict($identity->identity_id);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        return [
            'runs' => $runsPerDatePerVerdict,
            'status' => 'ok'
        ];
    }

    /**
     * Update basic user profile info when logged with fb/gool
     *
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     * @throws InvalidParameterException
     */
    public static function apiUpdateBasicInfo(Request $r) {
        self::authenticateRequest($r);

        //Buscar que el nuevo username no este ocupado si es que selecciono uno nuevo
        if ($r['username'] != $r['current_user']->username) {
            $testu = UsersDAO::FindByUsername($r['username']);

            if (!is_null($testu)) {
                throw new InvalidParameterException('parameterUsernameInUse', 'username');
            }

            Validators::isValidUsername($r['username'], 'username');
            $r['current_user']->username = $r['username'];
        }

        SecurityTools::testStrongPassword($r['password']);
        $hashedPassword = SecurityTools::hashString($r['password']);
        $r['current_user']->password = $hashedPassword;

        try {
            DAO::transBegin();

            UsersDAO::save($r['current_user']);
            IdentityController::convertFromUser($r['current_user']);

            DAO::transEnd();
        } catch (Exception $e) {
            DAO::transRollback();
            throw new InvalidDatabaseOperationException($e);
        }

        // Expire profile cache
        Cache::deleteFromCache(Cache::USER_PROFILE, $r['current_user']->username);
        $sessionController = new SessionController();
        $sessionController->InvalidateCache();

        return ['status' => 'ok'];
    }

    /**
     * Update user profile
     *
     * @param Request $r
     * @return array
     * @throws InvalidDatabaseOperationException
     * @throws InvalidParameterException
     */
    public static function apiUpdate(Request $r) {
        self::authenticateRequest($r);

        if (!is_null($r['username'])) {
            Validators::isValidUsername($r['username'], 'username');
            $user = null;
            try {
                $user = UsersDAO::FindByUsername($r['username']);
            } catch (Exception $e) {
                throw new InvalidDatabaseOperationException($e);
            }

            if ($r['username'] != $r['current_user']->username && !is_null($user)) {
                throw new DuplicatedEntryInDatabaseException('usernameInUse');
            }
        }

        if (!is_null($r['name'])) {
            Validators::isStringNonEmpty($r['name'], 'name', true);
            Validators::isStringOfMaxLength($r['name'], 'name', 50);
        }

        $state = null;
        if (!is_null($r['country_id']) || !is_null($r['state_id'])) {
            // Both state and country must be specified together.
            Validators::isStringNonEmpty($r['country_id'], 'country_id', true);
            Validators::isStringNonEmpty($r['state_id'], 'state_id', true);

            try {
                $state = StatesDAO::getByPK($r['country_id'], $r['state_id']);
            } catch (Exception $e) {
                throw new InvalidDatabaseOperationException($e);
            }

            if (is_null($state)) {
                throw new InvalidParameterException('parameterInvalid', 'state_id');
            }
        }

        if (!is_null($r['school_id'])) {
            if (is_numeric($r['school_id'])) {
                try {
                    $r['school'] = SchoolsDAO::getByPK($r['school_id']);
                } catch (Exception $e) {
                    throw new InvalidDatabaseOperationException($e);
                }

                if (is_null($r['school'])) {
                    throw new InvalidParameterException('parameterInvalid', 'school');
                }
            } elseif (empty($r['school_name'])) {
                $r['school_id'] = null;
            } else {
                try {
                    $response = SchoolController::apiCreate(new Request([
                        'name' => $r['school_name'],
                        'country_id' => $state != null ? $state->country_id : null,
                        'state_id' => $state != null ? $state->state_id : null,
                        'auth_token' => $r['auth_token'],
                    ]));
                    $r['school_id'] = $response['school_id'];
                } catch (Exception $e) {
                    throw new InvalidDatabaseOperationException($e);
                }
            }
        }

        Validators::isStringNonEmpty($r['scholar_degree'], 'scholar_degree', false);

        if (!is_null($r['graduation_date'])) {
            if (is_numeric($r['graduation_date'])) {
                $r['graduation_date'] = (int)$r['graduation_date'];
            } else {
                Validators::isDate($r['graduation_date'], 'graduation_date', false);
                $r['graduation_date'] = strtotime($r['graduation_date']);
            }
        }
        if (!is_null($r['birth_date'])) {
            if (is_numeric($r['birth_date'])) {
                $r['birth_date'] = (int)$r['birth_date'];
            } else {
                Validators::isDate($r['birth_date'], 'birth_date', false);
                $r['birth_date'] = strtotime($r['birth_date']);
            }

            if ($r['birth_date'] >= strtotime('-5 year', Time::get())) {
                throw new InvalidParameterException('birthdayInTheFuture', 'birth_date');
            }
        }

        if (!is_null($r['locale'])) {
            // find language in Language
            $language = LanguagesDAO::getByName($r['locale']);
            if (is_null($language)) {
                throw new InvalidParameterException('invalidLanguage', 'locale');
            }

            $r['current_user']->language_id = $language->language_id;
        }

        if (!is_null($r['is_private'])) {
            Validators::isNumber($r['is_private'], 'is_private', true);
        }

        if (!is_null($r['hide_problem_tags'])) {
            Validators::isNumber($r['hide_problem_tags'], 'hide_problem_tags', true);
        }

        if (!is_null($r['gender'])) {
            Validators::isInEnum($r['gender'], 'gender', UserController::ALLOWED_GENDER_OPTIONS, true);
        }

        $valueProperties = [
            'username',
            'name',
            'country_id',
            'state_id',
            'scholar_degree',
            'school_id',
            'preferred_language',
            'graduation_date' => ['transform' => function ($value) {
                return gmdate('Y-m-d', $value);
            }],
            'birth_date' => ['transform' => function ($value) {
                return gmdate('Y-m-d', $value);
            }],
            'gender',
            'is_private',
            'hide_problem_tags',
        ];

        self::updateValueProperties($r, $r['current_user'], $valueProperties);

        try {
            DAO::transBegin();

            UsersDAO::save($r['current_user']);

            IdentityController::convertFromUser($r['current_user']);

            DAO::transEnd();
        } catch (Exception $e) {
            DAO::transRollback();

            throw new InvalidDatabaseOperationException($e);
        }

        // Expire profile cache
        Cache::deleteFromCache(Cache::USER_PROFILE, $r['current_user']->username);
        $sessionController = new SessionController();
        $sessionController->InvalidateCache();

        return ['status' => 'ok'];
    }

    /**
     * If no username provided: Gets the top N users who have solved more problems
     * If username provided: Gets rank for username provided
     *
     * @param Request $r
     * @return string
     * @throws InvalidDatabaseOperationException
     */

    public static function apiRankByProblemsSolved(Request $r) {
        Validators::isNumber($r['offset'], 'offset', false);
        Validators::isNumber($r['rowcount'], 'rowcount', false);

        $r['user'] = null;
        if (!is_null($r['username'])) {
            Validators::isStringNonEmpty($r['username'], 'username');
            try {
                $r['user'] = UsersDAO::FindByUsername($r['username']);
                if (is_null($r['user'])) {
                    throw new NotFoundException('userNotExist');
                }
            } catch (ApiException $e) {
                throw $e;
            } catch (Exception $e) {
                throw new InvalidDatabaseOperationException($e);
            }
        }
        Validators::isInEnum($r['filter'], 'filter', ['', 'country', 'state', 'school'], false);

        // Defaults for offset and rowcount
        if (null == $r['offset']) {
            $r['offset'] = 1;
        }
        if (null == $r['rowcount']) {
            $r['rowcount'] = 100;
        }

        return self::getRankByProblemsSolved($r);
    }

    /**
     * Get rank by problems solved logic. It has its own func so
     * it can be accesed internally without authentication
     *
     * @param Request $r
     * @throws InvalidDatabaseOperationException
     */
    public static function getRankByProblemsSolved(Request $r) {
        if (is_null($r['user'])) {
            $selectedFilter = self::getSelectedFilter($r);
            $rankCacheName =  "{$r['offset']}-{$r['rowcount']}-{$r['filter']}-{$selectedFilter['value']}";
            $cacheUsed = Cache::getFromCacheOrSet(Cache::PROBLEMS_SOLVED_RANK, $rankCacheName, $r, function (Request $r) {
                $response = [];
                $response['rank'] = [];
                $response['total'] = 0;
                $selectedFilter = self::getSelectedFilter($r);
                try {
                    $userRankEntries = UserRankDAO::getFilteredRank(
                        $r['offset'],
                        $r['rowcount'],
                        'rank',
                        'ASC',
                        $selectedFilter['filteredBy'],
                        $selectedFilter['value']
                    );
                } catch (Exception $e) {
                    throw new InvalidDatabaseOperationException($e);
                }

                if (!is_null($userRankEntries)) {
                    foreach ($userRankEntries['rows'] as $userRank) {
                        array_push($response['rank'], [
                            'username' => $userRank->username,
                            'name' => $userRank->name,
                            'problems_solved' => $userRank->problems_solved_count,
                            'rank' => $userRank->rank,
                            'score' => $userRank->score,
                            'country_id' => $userRank->country_id]);
                    }
                    $response['total'] = $userRankEntries['total'];
                }
                return $response;
            }, $response, APC_USER_CACHE_USER_RANK_TIMEOUT);
        } else {
            $response = [];

            try {
                $userRank = UserRankDAO::getByPK($r['user']->user_id);
            } catch (Exception $e) {
                throw new InvalidDatabaseOperationException($e);
            }

            if (!is_null($userRank)) {
                $response['rank'] = $userRank->rank;
                $response['name'] = $r['user']->name;
                $response['problems_solved'] = $userRank->problems_solved_count;
            } else {
                $response['rank'] = 0;
                $response['name'] = $r['user']->name;
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
        Cache::invalidateAllKeys(Cache::PROBLEMS_SOLVED_RANK);
        Cache::invalidateAllKeys(Cache::CONTESTANT_SCOREBOARD_PREFIX);
        Cache::invalidateAllKeys(Cache::ADMIN_SCOREBOARD_PREFIX);
    }

    /**
     * Updates the main email of the current user
     *
     * @param Request $r
     */
    public static function apiUpdateMainEmail(Request $r) {
        self::authenticateRequest($r);

        Validators::isEmail($r['email'], 'email');

        try {
            // Update email
            $email = EmailsDAO::getByPK($r['current_user']->main_email_id);
            $email->email = $r['email'];
            EmailsDAO::save($email);

            // Add verification_id if not there
            if ($r['current_user']->verified == '0') {
                self::$log->info('User not verified.');

                if ($r['current_user']->verification_id == null) {
                    self::$log->info('User does not have verification id. Generating.');

                    try {
                        $r['current_user']->verification_id = SecurityTools::randomString(50);
                        UsersDAO::save($r['current_user']);
                    } catch (Exception $e) {
                        // best effort, eat exception
                    }
                }
            }
        } catch (Exception $e) {
            // If duplicate in DB
            if (strpos($e->getMessage(), '1062') !== false) {
                throw new DuplicatedEntryInDatabaseException('mailInUse');
            } else {
                throw new InvalidDatabaseOperationException($e);
            }
        }

        // Delete profile cache
        Cache::deleteFromCache(Cache::USER_PROFILE, $r['current_user']->username);

        // Send verification email
        $r['user'] = $r['current_user'];
        self::sendVerificationEmail($r['user']);

        return ['status' => 'ok'];
    }

    public static function makeUsernameFromEmail($email) {
        $newUsername = substr($email, 0, strpos($email, '@'));
        $newUsername = str_replace('-', '_', $newUsername);
        $newUsername = str_replace('.', '_', $newUsername);
        return $newUsername . Time::get();
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
     * @param Request $r
     */
    public static function apiValidateFilter(Request $r) {
        Validators::isStringNonEmpty($r['filter'], 'filter');

        $response = [
            'status' => 'ok',
            'user' => null,
            'admin' => false,
            'problem_admin' => [],
            'contest_admin' => [],
            'problemset' => [],
        ];

        $session = SessionController::apiCurrentSession($r)['session'];
        $identity = $session['identity'];
        if (!is_null($identity)) {
            $response['user'] = $identity->username;
            $response['admin'] = $session['is_admin'];
        }

        $filters = explode(',', $r['filter']);
        foreach ($filters as $filter) {
            $tokens = explode('/', $filter);
            if (count($tokens) < 2 || $tokens[0] != '') {
                throw new InvalidParameterException('parameterInvalid', 'filter');
            }
            switch ($tokens[1]) {
                case 'all-events':
                    if (count($tokens) != 2) {
                        throw new InvalidParameterException('parameterInvalid', 'filter');
                    }
                    if (!$session['is_admin']) {
                        throw new ForbiddenAccessException('userNotAllowed');
                    }
                    break;
                case 'user':
                    if (count($tokens) != 3) {
                        throw new InvalidParameterException('parameterInvalid', 'filter');
                    }
                    if ($tokens[2] != $identity->username && !$session['is_admin']) {
                        throw new ForbiddenAccessException('userNotAllowed');
                    }
                    break;
                case 'contest':
                    if (count($tokens) < 3) {
                        throw new InvalidParameterException('parameterInvalid', 'filter');
                    }
                    $r2 = new Request([
                        'contest_alias' => $tokens[2],
                    ]);
                    if (isset($r['auth_token'])) {
                        $r2['auth_token'] = $r['auth_token'];
                    }
                    if (count($tokens) >= 4) {
                        $r2['token'] = $tokens[3];
                    }
                    ContestController::validateDetails($r2);
                    if ($r2['contest_admin']) {
                        $response['contest_admin'][] = $r2['contest_alias'];
                    }
                    break;
                case 'problemset':
                    if (count($tokens) < 3) {
                        throw new InvalidParameterException('parameterInvalid', 'filter');
                    }
                    $r2 = ProblemsetController::wrapRequest(new Request([
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
                        throw new InvalidParameterException('parameterInvalid', 'filter');
                    }
                    $problem = ProblemsDAO::getByAlias($tokens[2]);
                    if (is_null($problem)) {
                        throw new NotFoundException('problemNotFound');
                    }
                    if (!is_null($identity) && Authorization::isProblemAdmin($identity->identity_id, $problem)) {
                        $response['problem_admin'][] = $tokens[2];
                    } elseif (!ProblemsDAO::isVisible($problem)) {
                        throw new ForbiddenAccessException('problemIsPrivate');
                    }

                    break;
            }
        }

        return $response;
    }

    private static function validateUser(Request $r) {
        // Validate request
        Validators::isValidUsername($r['username'], 'username');
        try {
            $r['user'] = UsersDAO::FindByUsername($r['username']);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }
        if (is_null($r['user'])) {
            throw new NotFoundException('userNotExist');
        }
    }

    private static function validateAddRemoveRole(Request $r) {
        if (!Authorization::isSystemAdmin($r['current_identity_id']) && !OMEGAUP_ALLOW_PRIVILEGE_SELF_ASSIGNMENT) {
            throw new ForbiddenAccessException();
        }

        self::validateUser($r);

        Validators::isStringNonEmpty($r['role'], 'role');
        $r['role'] = RolesDAO::getByName($r['role']);
        if (is_null($r['role'])) {
            throw new InvalidParameterException('parameterNotFound', 'role');
        }

        if ($r['role']->role_id == Authorization::ADMIN_ROLE && !OMEGAUP_ALLOW_PRIVILEGE_SELF_ASSIGNMENT) {
            // System-admin role cannot be added/removed from the UI, only when OMEGAUP_ALLOW_PRIVILEGE_SELF_ASSIGNMENT flag is on.
            throw new ForbiddenAccessException('userNotAllowed');
        }
    }

    private static function validateAddRemoveGroup(Request $r) {
        if (!OMEGAUP_ALLOW_PRIVILEGE_SELF_ASSIGNMENT) {
            throw new ForbiddenAccessException('userNotAllowed');
        }

        self::validateUser($r);

        Validators::isStringNonEmpty($r['group'], 'group');
        $r['group'] = GroupsDAO::getByName($r['group']);
        if (is_null($r['group'])) {
            throw new InvalidParameterException('parameterNotFound', 'group');
        }
    }

    /**
     * Adds the role to the user.
     *
     * @param Request $r
     */
    public static function apiAddRole(Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        self::authenticateRequest($r);
        self::validateAddRemoveRole($r);

        try {
            UserRolesDAO::save(new UserRoles([
                'user_id' => $r['user']->user_id,
                'role_id' => $r['role']->role_id,
                'acl_id' => Authorization::SYSTEM_ACL,
            ]));
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        return [
            'status' => 'ok',
        ];
    }

    /**
     * Removes the role from the user.
     *
     * @param Request $r
     */
    public static function apiRemoveRole(Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        self::authenticateRequest($r);
        self::validateAddRemoveRole($r);

        try {
            UserRolesDAO::delete(new UserRoles([
                'user_id' => $r['user']->user_id,
                'role_id' => $r['role']->role_id,
                'acl_id' => Authorization::SYSTEM_ACL,
            ]));
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        return [
            'status' => 'ok',
        ];
    }

    /**
     * Adds the identity to the group.
     *
     * @param Request $r
     */
    public static function apiAddGroup(Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        self::authenticateRequest($r);
        self::validateAddRemoveGroup($r);
        try {
            GroupsIdentitiesDAO::save(new GroupsIdentities([
                'identity_id' => $r['current_identity_id'],
                'group_id' => $r['group']->group_id
            ]));
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        return [
            'status' => 'ok',
        ];
    }

    /**
     * Removes the user to the group.
     *
     * @param Request $r
     */
    public static function apiRemoveGroup(Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        self::authenticateRequest($r);
        self::validateAddRemoveGroup($r);

        try {
            GroupsIdentitiesDAO::delete(new GroupsIdentities([
                'identity_id' => $r['current_identity_id'],
                'group_id' => $r['group']->group_id
            ]));
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        return [
            'status' => 'ok',
        ];
    }

    private static function validateAddRemoveExperiment(Request $r) {
        global $experiments;

        if (!Authorization::isSystemAdmin($r['current_identity_id'])) {
            throw new ForbiddenAccessException();
        }

        self::validateUser($r);

        Validators::isStringNonEmpty($r['experiment'], 'experiment');
        if (!in_array($r['experiment'], $experiments->getAllKnownExperiments())) {
            throw new InvalidParameterException('parameterNotFound', 'experiment');
        }
    }

    /**
     * Adds the experiment to the user.
     *
     * @param Request $r
     */
    public static function apiAddExperiment(Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        self::authenticateRequest($r);
        self::validateAddRemoveExperiment($r);

        try {
            UsersExperimentsDAO::save(new UsersExperiments([
                'user_id' => $r['user']->user_id,
                'experiment' => $r['experiment'],
            ]));
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        return [
            'status' => 'ok',
        ];
    }

    /**
     * Removes the experiment from the user.
     *
     * @param Request $r
     */
    public static function apiRemoveExperiment(Request $r) {
        if (OMEGAUP_LOCKDOWN) {
            throw new ForbiddenAccessException('lockdown');
        }

        self::authenticateRequest($r);
        self::validateAddRemoveExperiment($r);

        try {
            UsersExperimentsDAO::delete($r['user']->user_id, $r['experiment']);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        return [
            'status' => 'ok',
        ];
    }

    /**
     * Gets the last privacy policy saved in the data base
     * @throws ForbiddenAccessException
     */
    public static function getPrivacyPolicy(Request $r) {
        self::authenticateRequest($r);

        $user = self::resolveTargetUser($r);
        $identity = self::resolveTargetIdentity($r);

        $lang = 'es';
        if ($user->language_id == UserController::LANGUAGE_EN ||
            $user->language_id == UserController::LANGUAGE_PSEUDO) {
            $lang = 'en';
        } elseif ($user->language_id == UserController::LANGUAGE_PT) {
            $lang = 'pt';
        }
        $latest_statement = PrivacyStatementsDAO::getLatestPublishedStatement();
        return [
            'status' => 'ok',
            'policy_markdown' => file_get_contents(
                OMEGAUP_ROOT . "/privacy/privacy_policy/{$lang}.md"
            ),
            'has_accepted' => PrivacyStatementConsentLogDAO::hasAcceptedPrivacyStatement(
                $identity->identity_id,
                $latest_statement['privacystatement_id']
            ),
            'git_object_id' => $latest_statement['git_object_id'],
            'statement_type' => 'privacy_policy',
        ];
    }

    private static function getSelectedFilter($r) {
        $session = SessionController::apiCurrentSession($r)['session'];
        if (!$session['valid']) {
            return ['filteredBy' => null, 'value' => null];
        }
        $user = $session['user'];
        $filteredBy = $r['filter'];
        if ($filteredBy == 'country') {
            return ['filteredBy' => $filteredBy, 'value' => $user->country_id];
        }
        if ($filteredBy == 'state') {
            return ['filteredBy' => $filteredBy, 'value' => $user->country_id . '-' . $user->state_id];
        }
        if ($filteredBy == 'school') {
            return ['filteredBy' => $filteredBy, 'value' => $user->school_id];
        }
        return ['filteredBy' => null, 'value' => null];
    }

    /**
     * Gets the last privacy policy accepted by user
     *
     * @param Request $r
     */
    public static function apiLastPrivacyPolicyAccepted(Request $r) {
        self::authenticateRequest($r);

        $identity = self::resolveTargetIdentity($r);
        return [
            'status' => 'ok',
            'hasAccepted' => PrivacyStatementConsentLogDAO::hasAcceptedPrivacyStatement(
                $identity->identity_id,
                PrivacyStatementsDAO::getLatestPublishedStatement()['privacystatement_id']
            ),
        ];
    }

    /**
     * Keeps a record of a user who accepts the privacy policy
     *
     * @param Request $r
     * @throws DuplicatedEntryInDatabaseException
     */
    public static function apiAcceptPrivacyPolicy(Request $r) {
        self::authenticateRequest($r);
        $privacystatement_id = PrivacyStatementsDAO::getId($r['privacy_git_object_id'], $r['statement_type']);
        if (is_null($privacystatement_id)) {
            throw new NotFoundException('privacyStatementNotFound');
        }
        $identity = self::resolveTargetIdentity($r);

        try {
            $response = PrivacyStatementConsentLogDAO::saveLog(
                $identity->identity_id,
                $privacystatement_id
            );
            $sessionController = new SessionController();
            $sessionController->InvalidateCache();
        } catch (Exception $e) {
            throw new DuplicatedEntryInDatabaseException('userAlreadyAcceptedPrivacyPolicy');
        }

        return ['status' => 'ok'];
    }

    /**
     * Associates an identity to the logged user given the username
     *
     * @param Request $r
     * @throws InvalidParameterException
     * @throws InvalidDatabaseOperationException
     */
    public static function apiAssociateIdentity(Request $r) {
        global $experiments;
        $experiments->ensureEnabled(Experiments::IDENTITIES);
        self::authenticateRequest($r);

        Validators::isStringNonEmpty($r['username'], 'username');
        Validators::isStringNonEmpty($r['password'], 'password');

        $identity = IdentitiesDAO::getUnassociatedIdentity($r['username']);

        if (empty($identity)) {
            throw new InvalidParameterException('parameterInvalid', 'username');
        }

        $passwordCheck = SecurityTools::compareHashedStrings(
            $r['password'],
            $identity->password
        );

        if ($passwordCheck === false) {
            throw new InvalidParameterException('parameterInvalid', 'password');
        }

        try {
            IdentitiesDAO::associateIdentityWithUser($r['current_user_id'], $identity->identity_id);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        return ['status' => 'ok'];
    }

    /**
     * Get the identities that have been associated to the logged user
     *
     * @param Request $r
     * @throws InvalidDatabaseOperationException
     */
    public static function apiListAssociatedIdentities(Request $r) {
        global $experiments;
        $experiments->ensureEnabled(Experiments::IDENTITIES);
        self::authenticateRequest($r);

        try {
            return [
                'status' => 'ok',
                'identities' => IdentitiesDAO::getAssociatedIdentities($r['current_user_id'])
            ];
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }
    }
}

UserController::$urlHelper = new UrlHelper();
