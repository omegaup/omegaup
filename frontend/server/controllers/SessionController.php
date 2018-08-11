<?php

/**
 * Description:
 *     Session controller handles sessions.
 *
 * Author:
 *     Alan Gonzalez alanboy@alanboy.net
 *
 * */

class SessionController extends Controller {
    const AUTH_TOKEN_ENTROPY_SIZE = 15;

    private static $current_session = null;
    private static $_facebook;
    public static $_sessionManager;
    public static $setCookieOnRegisterSession = true;

    public static function getSessionManagerInstance() {
        if (is_null(self::$_sessionManager)) {
            self::$_sessionManager = new SessionManager();
        }
        return self::$_sessionManager;
    }

    /**
     * @param string nombre Este es el nombre del dude
     *
     * */
    private static function getFacebookInstance() {
        if (is_null(self::$_facebook)) {
            self::$_facebook = new Facebook\Facebook([
                'app_id' => OMEGAUP_FB_APPID,
                'app_secret' => OMEGAUP_FB_SECRET,
                'default_graph_version' => 'v2.5',
            ]);
        }
        return self::$_facebook;
    }

    public static function getFacebookLoginUrl() {
        $facebook = self::getFacebookInstance();

        $helper = $facebook->getRedirectLoginHelper();
        return $helper->getLoginUrl(OMEGAUP_URL.'/login?fb', ['email']);
    }

    private static function isAuthTokenValid($authToken) {
        //do some other basic testing on authToken
        return true;
    }

    public static function CurrentSessionAvailable() {
        return self::apiCurrentSession()['session']['valid'];
    }

    /**
     * Returns information about current session. In order to avoid one full
     * server roundtrip (about ~100msec on each pageload), it also returns the
     * current time to be able to calculate the time delta between the
     * contestant's machine and the server.
     * */
    public static function apiCurrentSession(Request $r = null) {
        if (defined('OMEGAUP_SESSION_CACHE_ENABLED') &&
            OMEGAUP_SESSION_CACHE_ENABLED === true &&
            !is_null(self::$current_session)) {
            return [
                'status' => 'ok',
                'session' => self::$current_session,
                'time' => Time::get(),
            ];
        }
        if (is_null($r)) {
            $r = new Request();
        }
        if (is_null($r['auth_token'])) {
            $r['auth_token'] = SessionController::getAuthToken($r);
        }
        $authToken = $r['auth_token'];
        if ($authToken != null &&
            defined('OMEGAUP_SESSION_CACHE_ENABLED') &&
            OMEGAUP_SESSION_CACHE_ENABLED === true) {
            Cache::getFromCacheOrSet(
                Cache::SESSION_PREFIX,
                $authToken,
                $r,
                ['SessionController', 'getCurrentSession'],
                $session,
                APC_USER_CACHE_SESSION_TIMEOUT
            );
            self::$current_session = $session;
        } else {
            self::$current_session = SessionController::getCurrentSession($r);
        }
        return [
            'status' => 'ok',
            'session' => self::$current_session,
            'time' => Time::get(),
        ];
    }

    private static function getAuthToken(Request $r = null) {
        $SessionM = self::getSessionManagerInstance();
        $SessionM->sessionStart();
        $authToken = null;
        if (!is_null($r) && !is_null($r['auth_token'])) {
            $authToken = $r['auth_token'];
        } else {
            $authToken = $SessionM->getCookie(OMEGAUP_AUTH_TOKEN_COOKIE_NAME);
        }
        if (!is_null($authToken) && self::isAuthTokenValid($authToken)) {
            return $authToken;
        } elseif (isset($_REQUEST[OMEGAUP_AUTH_TOKEN_COOKIE_NAME])
                && self::isAuthTokenValid($_REQUEST[OMEGAUP_AUTH_TOKEN_COOKIE_NAME])) {
            return $_REQUEST[OMEGAUP_AUTH_TOKEN_COOKIE_NAME];
        } else {
            return null;
        }
    }

    public static function getCurrentSession(Request $r) {
        $authToken = $r['auth_token'];

        if (is_null($authToken)) {
            return [
                'valid' => false,
                'email' => null,
                'user' => null,
                'identity' => null,
                'auth_token' => null,
                'is_admin' => false,
            ];
        }

        $currentUser = AuthTokensDAO::getUserByToken($authToken);
        $currentIdentity = AuthTokensDAO::getIdentityByToken($authToken);

        if (is_null($currentUser) && is_null($currentIdentity)) {
            // Means user has auth token, but does not exist in DB
            return [
                'valid' => false,
                'email' => null,
                'user' => null,
                'identity' => null,
                'auth_token' => null,
                'is_admin' => false,
            ];
        }

        // Get email via their id
        if (!is_null($currentUser)) {
            $email = EmailsDAO::getByPK($currentUser->main_email_id);
        }

        return [
            'valid' => true,
            'email' => !empty($email) ? $email->email : '',
            'username' => $currentIdentity->username,
            'user' => $currentUser,
            'identity' => $currentIdentity,
            'auth_token' => $authToken,
            'is_admin' => Authorization::isSystemAdmin($currentIdentity->identity_id),
        ];
    }

    /**
     * Invalidates the current user's session cache.
     */
    public function InvalidateCache() {
        $currentSession = self::apiCurrentSession()['session'];
        Cache::deleteFromCache(Cache::SESSION_PREFIX, $currentSession['auth_token']);
    }

    /**
     * Invalidates the current request's session cache.
     */
    public function InvalidateLocalCache() {
        self::$current_session = null;
    }

    public function UnRegisterSession() {
        $this->InvalidateCache();

        $currentSession = self::apiCurrentSession()['session'];
        $authToken = new AuthTokens(['token' => $currentSession['auth_token']]);

        $this->InvalidateLocalCache();

        try {
            AuthTokensDAO::delete($authToken);
        } catch (Exception $e) {
        }

        setcookie(OMEGAUP_AUTH_TOKEN_COOKIE_NAME, 'deleted', 1, '/');
    }

    private function RegisterSession(Identities $identity, $b_ReturnAuthTokenAsString = false) {
        // Log the login.
        IdentityLoginLogDAO::save(new IdentityLoginLog([
            'identity_id' => $identity->identity_id,
            'ip' => ip2long($_SERVER['REMOTE_ADDR']),
        ]));

        $this->InvalidateLocalCache();

        //find if this user has older sessions
        $authToken = new AuthTokens();
        $authToken->user_id = $identity->user_id;
        $authToken->identity_id = $identity->identity_id;

        //erase expired tokens
        try {
            $tokens_erased = AuthTokensDAO::expireAuthTokens($identity->identity_id);
        } catch (Exception $e) {
            // Best effort
            self::$log->error("Failed to delete expired tokens: {$e->getMessage()}");
        }

        // Create the new token
        $entropy = bin2hex(random_bytes(SessionController::AUTH_TOKEN_ENTROPY_SIZE));
        $hash = hash('sha256', OMEGAUP_MD5_SALT . $identity->identity_id . $entropy);
        $s_AuthT = "{$entropy}-{$identity->identity_id}-{$hash}";

        $authToken = new AuthTokens();
        $authToken->user_id = $identity->user_id;
        $authToken->identity_id = $identity->identity_id;
        $authToken->token = $s_AuthT;

        try {
            AuthTokensDAO::save($authToken);
        } catch (Exception $e) {
            throw new InvalidDatabaseOperationException($e);
        }

        if (self::$setCookieOnRegisterSession) {
            $sm = $this->getSessionManagerInstance();
            $sm->setCookie(OMEGAUP_AUTH_TOKEN_COOKIE_NAME, $s_AuthT, 0, '/');
        }

        Cache::deleteFromCache(Cache::SESSION_PREFIX, $s_AuthT);

        if ($b_ReturnAuthTokenAsString) {
            return $s_AuthT;
        }
    }

    private static function getUniqueUsernameFromEmail($s_Email) {
        $idx = strpos($s_Email, '@');
        $username = substr($s_Email, 0, $idx);

        try {
            Validators::isValidUsername($username, 'username');
        } catch (InvalidParameterException $e) {
            // How can we know whats wrong with the username?
            // Things that could go wrong:
            //		generated email is too short
            $username = 'OmegaupUser';
        }

        $suffix = '';
        for (;;) {
            // Maybe we can bring all records from db
            // with prefix $username, beacuse this:
            $userexists = UsersDAO::FindByUsername($username . $suffix);
            // will query db every single time probably.

            if (empty($userexists)) {
                break;
            }

            if (empty($suffix)) {
                $suffix = 1;
            } else {
                $suffix++;
            }
        }
        return $username . $suffix;
    }

    public static function apiGoogleLogin(Request $r = null) {
        if (is_null($r['storeToken'])) {
            throw new InvalidParameterException('parameterNotFound', 'storeToken');
        }

        $client = new Google_Client();
        $client->setClientId(OMEGAUP_GOOGLE_CLIENTID);
        $client->setClientSecret(OMEGAUP_GOOGLE_SECRET);
        $client->setRedirectUri('postmessage');
        $client->setScopes([
            'https://www.googleapis.com/auth/userinfo.email',
            'https://www.googleapis.com/auth/userinfo.profile']);

        try {
            $client->authenticate($r['storeToken']);
        } catch (Google_Auth_Exception $ge) {
            self::$log->error($ge->getMessage());
            throw new InternalServerErrorException($ge);
        }

        if ($client->getAccessToken()) {
            $request = new Google_Http_Request('https://www.googleapis.com/oauth2/v2/userinfo?alt=json');
            $userinfo = $client->getAuth()->authenticatedRequest($request);
            $responseJson = json_decode($userinfo->getResponseBody(), true);

            // responseJson will have:
            //    [id] => 103621569728764469767
            //    [email] => johndoe@gmail.com
            //    [verified_email] => 1
            //    [name] => Alan Gonzalez
            //    [given_name] => Alan
            //    [family_name] => Gonzalez
            //    [link] => https://plus.google.com/123621569728764469767
            //    [picture] => https://lh3.googleusercontent.com/-zrLvBe-AU/AAAAAAAAAAI/AAAAAAAAATU/hh0yUXEisCI/photo.jpg
            //    [gender] => male
            //    [locale] => en

            $controller = (new SessionController())->LoginViaGoogle($responseJson['email']);
        } else {
            throw new InternalServerErrorException(new Exception());
        }

        return ['status' => 'ok'];
    }

    public function LoginViaGoogle($s_Email) {
        return $this->ThirdPartyLogin('Google', $s_Email);
    }

    /**
     * Logs in via Facebook API.
     *
     * @return array An associative array with a 'status' field that has 'ok'
     *               on success or 'error' on error. An 'error' field with an
     *               i18n string may also appear on the response.
     */
    public function LoginViaFacebook() {
        // Mostly taken from
        // https://developers.facebook.com/docs/php/howto/example_facebook_login
        $facebook = self::getFacebookInstance();

        $helper = $facebook->getRedirectLoginHelper();
        try {
            $access_token = $helper->getAccessToken();
        } catch (Facebook\Exceptions\FacebookResponseException $e) {
            return ['status' => 'error', 'error' => $e->getMessage()];
        } catch (Facebook\Exceptions\FacebookSDKException $e) {
            return ['status' => 'error', 'error' => $e->getMessage()];
        }

        if (!isset($access_token)) {
            $response = ['status' => 'error'];
            if ($helper->getError()) {
                $response['error'] = $helper->getError() . ' ' . $helper->getErrorDescription();
            }
        }

        try {
            $fb_response = $facebook->get('/me?fields=name,email', $access_token);
        } catch (Facebook\Exceptions\FacebookResponseException $e) {
            return ['status' => 'error', 'error' => $e->getMessage()];
        } catch (Facebook\Exceptions\FacebookSDKException $e) {
            return ['status' => 'error', 'error' => $e->getMessage()];
        }

        $fb_user_profile = $fb_response->getGraphUser();
        self::$log->info('User is logged in via facebook !!');
        if (!isset($fb_user_profile['email'])) {
            self::$log->error('Facebook email empty');
            global $smarty;
            return [
                'status' => 'error',
                'error' => $smarty->getConfigVariable(
                    'loginFacebookEmptyEmailError'
                ),
            ];
        }

        return $this->ThirdPartyLogin(
            'Facebook',
            $fb_user_profile['email'],
            $fb_user_profile['name']
        );
    }

    /**
     * Does login for a user given username or email and password.
     * Expects in request:
     * usernameOrEmail
     * password
     *
     * @param Request $r
     * @return boolean
     */
    public function NativeLogin(Request $r) {
        $c_Users = new UserController();
        $identity = null;

        if (null != $r['returnAuthToken']) {
            $returnAuthToken = $r['returnAuthToken'];
        } else {
            $returnAuthToken = false;
        }

        try {
            $identity = IdentityController::resolveIdentity($r['usernameOrEmail']);
            $r['user_id'] = $identity->user_id;
            $r['identity_id'] = $identity->identity_id;
            $r['user'] = $identity;
        } catch (ApiException $e) {
            self::$log->warn('Identity ' . $r['usernameOrEmail'] . ' not found.');
            return false;
        }

        $b_Valid = $c_Users->TestPassword($r);

        if (!$b_Valid) {
            self::$log->warn('Identity ' . $r['usernameOrEmail'] . ' has introduced invalid credentials.');
            return false;
        }

        self::$log->info('Identity ' . $r['usernameOrEmail'] . ' has loged in natively.');

        UserController::checkEmailVerification($r);

        try {
            return $this->RegisterSession($identity, $returnAuthToken);
        } catch (Exception $e) {
            self::$log->error($e);
            return false;
            //@TODO actuar en base a la exception
        }
    }

    public static function getLinkedInInstance() {
        return new LinkedIn(
            OMEGAUP_LINKEDIN_CLIENTID,
            OMEGAUP_LINKEDIN_SECRET,
            OMEGAUP_URL.'/login?linkedin',
            isset($_GET['redirect']) ? $_GET['redirect'] : null
        );
    }
    public static function getLinkedInLoginUrl() {
        return self::getLinkedInInstance()->getLoginUrl();
    }

    public function LoginViaLinkedIn() {
        if (empty($_GET['code']) || empty($_GET['state'])) {
            return ['status' => 'error'];
        }

        try {
            $li = self::getLinkedInInstance();
            $auth_token = $li->getAuthToken($_GET['code'], $_GET['state']);
            $profile = $li->getProfileInfo($auth_token);
            $li->maybeResetRedirect($_GET['state']);

            return $this->ThirdPartyLogin(
                'LinkedIn',
                $profile['emailAddress'],
                $profile['firstName'] . ' ' . $profile['lastName']
            );
        } catch (ApiException $e) {
            self::$log->error("Unable to login via LinkedIn: $e");
            return $e->asResponseArray();
        }
    }

    private function ThirdPartyLogin($provider, $email, $name = null) {
        // We trust this user's identity
        self::$log->info("User is logged in via $provider");
        $results = IdentitiesDAO::FindByEmail($email);

        if (!is_null($results)) {
            self::$log->info("User has been here before with $provider");
            $identity = $results;
        } else {
            // The user has never been here before, let's register them
            self::$log->info("LoginVia$provider: Creating new user for $email");

            // I have a problem with this:
            $username = self::getUniqueUsernameFromEmail($email);
            // Even if the user gave us their email, we should not
            // just go ahead and assume its ok to share with the world
            // maybe we could do:
            // $username = str_replace(" ", "_", $fb_user_profile["name"] ),
            UserController::$permissionKey = uniqid();

            $r = new Request([
                'name' => (!is_null($name) ? $name : $username),
                'username' => $username,
                'email' => $email,
                'password' => null,
                'permission_key' => UserController::$permissionKey,
                'ignore_password' => true
                // TODO(lhchavez): Do we actually need this? It's stored but never used.
                //'facebook_user_id' => $fb_user_profile['id'],
            ]);

            try {
                $res = UserController::apiCreate($r);
            } catch (ApiException $e) {
                self::$log->error("Unable to login via $provider: $e");
                return $e->asResponseArray();
            }
            $identity = IdentitiesDAO::FindByUsername($res['username']);
        }

        $this->RegisterSession($identity);
        return ['status' => 'ok'];
    }
}
