<?php

/**
 * Description:
 *     Session controller handles sessions.
 *
 * Author:
 *     Alan Gonzalez alanboy@alanboy.net
 *
 */
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
            require_once 'libs/third_party/facebook-php-graph-sdk/src/Facebook/autoload.php';
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

    public static function currentSessionAvailable() {
        return self::apiCurrentSession()['session']['valid'];
    }

    /**
     * Returns information about current session. In order to avoid one full
     * server roundtrip (about ~100msec on each pageload), it also returns the
     * current time to be able to calculate the time delta between the
     * contestant's machine and the server.
     * */
    public static function apiCurrentSession(?Request $r = null) : array {
        if (defined('OMEGAUP_SESSION_CACHE_ENABLED') &&
            OMEGAUP_SESSION_CACHE_ENABLED === true &&
            !is_null(self::$current_session)
        ) {
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
        if (defined('OMEGAUP_SESSION_CACHE_ENABLED') &&
            OMEGAUP_SESSION_CACHE_ENABLED === true &&
            !is_null($authToken)
         ) {
            self::$current_session = Cache::getFromCacheOrSet(
                Cache::SESSION_PREFIX,
                $authToken,
                function () use ($r) {
                    return SessionController::getCurrentSession($r);
                },
                APC_USER_CACHE_SESSION_TIMEOUT
            );
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

        $currentIdentity = AuthTokensDAO::getIdentityByToken($authToken);
        if (is_null($currentIdentity)) {
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

        if (is_null($currentIdentity->user_id)) {
            $currentUser = null;
            $email = null;
        } else {
            $currentUser = UsersDAO::getByPK($currentIdentity->user_id);
            $email = !is_null($currentUser->main_email_id) ? EmailsDAO::getByPK($currentUser->main_email_id) : null;
        }

        return [
            'valid' => true,
            'email' => !empty($email) ? $email->email : '',
            'username' => $currentIdentity->username,
            'user' => $currentUser,
            'identity' => $currentIdentity,
            'auth_token' => $authToken,
            'is_admin' => Authorization::isSystemAdmin($currentIdentity),
        ];
    }

    /**
     * Invalidates the current user's session cache.
     */
    public function InvalidateCache() {
        $currentSession = self::apiCurrentSession()['session'];
        if (is_null($currentSession['auth_token'])) {
            return;
        }
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
            // Best effort
            self::$log->error("Failed to delete expired tokens: {$e->getMessage()}");
        }

        setcookie(OMEGAUP_AUTH_TOKEN_COOKIE_NAME, 'deleted', 1, '/');
    }

    private function registerSession(Identities $identity) : string {
        // Log the login.
        IdentityLoginLogDAO::create(new IdentityLoginLog([
            'identity_id' => $identity->identity_id,
            'ip' => ip2long($_SERVER['REMOTE_ADDR']),
        ]));

        $this->InvalidateLocalCache();

        //erase expired tokens
        try {
            AuthTokensDAO::expireAuthTokens($identity->identity_id);
        } catch (Exception $e) {
            // Best effort
            self::$log->error("Failed to delete expired tokens: {$e->getMessage()}");
        }

        // Create the new token
        $entropy = bin2hex(random_bytes(SessionController::AUTH_TOKEN_ENTROPY_SIZE));
        $hash = hash('sha256', OMEGAUP_MD5_SALT . $identity->identity_id . $entropy);
        $token = "{$entropy}-{$identity->identity_id}-{$hash}";

        AuthTokensDAO::replace(new AuthTokens([
            'user_id' => $identity->user_id,
            'identity_id' => $identity->identity_id,
            'token' => $token,
        ]));

        if (self::$setCookieOnRegisterSession) {
            $this->getSessionManagerInstance()->setCookie(OMEGAUP_AUTH_TOKEN_COOKIE_NAME, $token, 0, '/');
        }

        Cache::deleteFromCache(Cache::SESSION_PREFIX, $token);
        return $token;
    }

    private static function getUniqueUsernameFromEmail($s_Email) {
        $idx = strpos($s_Email, '@');
        $username = substr($s_Email, 0, $idx);

        try {
            Validators::validateValidUsername($username, 'username');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            // How can we know whats wrong with the username?
            // Things that could go wrong:
            //      generated email is too short
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
            throw new \OmegaUp\Exceptions\InvalidParameterException('parameterNotFound', 'storeToken');
        }

        require_once 'libs/third_party/google-api-php-client/src/Google/autoload.php';

        $client = new Google_Client();
        $client->setClientId(OMEGAUP_GOOGLE_CLIENTID);
        $client->setClientSecret(OMEGAUP_GOOGLE_SECRET);

        try {
            $loginTicket = $client->verifyIdToken($r['storeToken']);
        } catch (Google_Auth_Exception $ge) {
            throw new UnauthorizedException('loginRequired', $ge);
        }

        $payload = $loginTicket->getAttributes()['payload'];

        // payload will have a superset of:
        //    [email] => johndoe@gmail.com
        //    [email_verified] => 1
        //    [name] => Alan Gonzalez
        //    [picture] => https://lh3.googleusercontent.com/-zrLvBe-AU/AAAAAAAAAAI/AAAAAAAAATU/hh0yUXEisCI/photo.jpg
        //    [locale] => en

        $controller = new SessionController();
        $controller->LoginViaGoogle(
            $payload['email'],
            (isset($payload['name']) ? $payload['name'] : null)
        );

        return ['status' => 'ok'];
    }

    public function LoginViaGoogle($email, $name = null) {
        return $this->ThirdPartyLogin('Google', $email, $name);
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
            return [
                'status' => 'error',
                'error' => \OmegaUp\Translations::getInstance()->get(
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
        $identity = null;

        Validators::validateStringNonEmpty($r['password'], 'password');

        if (null != $r['returnAuthToken']) {
            $returnAuthToken = $r['returnAuthToken'];
        } else {
            $returnAuthToken = false;
        }

        try {
            $identity = IdentityController::resolveIdentity($r['usernameOrEmail']);
        } catch (\OmegaUp\Exceptions\ApiException $e) {
            self::$log->warn("Identity {$r['usernameOrEmail']} not found.");
            return false;
        }

        if (!IdentityController::testPassword($identity, $r['password'])) {
            self::$log->warn("Identity {$identity->username} has introduced invalid credentials.");
            return false;
        }
        if (SecurityTools::isOldHash($identity->password)) {
            // Update the password using the new Argon2i algorithm.
            self::$log->warn("Identity {$identity->username}'s password hash is being upgraded.");
            try {
                DAO::transBegin();
                $identity->password = SecurityTools::hashString($r['password']);
                IdentitiesDAO::update($identity);
                if (!is_null($identity->user_id)) {
                    $user = UsersDAO::getByPK($identity->user_id);
                    $user->password = $identity->password;
                    UsersDAO::update($user);
                }
                DAO::transEnd();
            } catch (Exception $e) {
                DAO::transRollback();
                throw $e;
            }
        }

        self::$log->info("Identity {$identity->username} has logged in natively.");

        if (!is_null($identity->user_id)) {
            $user = UsersDAO::getByPK($identity->user_id);
            UserController::checkEmailVerification($user);
        }

        try {
            return $this->registerSession($identity, $returnAuthToken);
        } catch (Exception $e) {
            self::$log->error($e);
            return false;
            //@TODO actuar en base a la exception
        }
    }

    public static function getLinkedInInstance() {
        require_once 'libs/LinkedIn.php';
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
        } catch (\OmegaUp\Exceptions\ApiException $e) {
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
            ]);

            try {
                $res = UserController::apiCreate($r);
            } catch (\OmegaUp\Exceptions\ApiException $e) {
                self::$log->error("Unable to login via $provider: $e");
                return $e->asResponseArray();
            }
            $identity = IdentitiesDAO::findByUsername($res['username']);
        }

        $this->registerSession($identity);
        return ['status' => 'ok'];
    }
}
