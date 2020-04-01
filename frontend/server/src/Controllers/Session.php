<?php

 namespace OmegaUp\Controllers;

class ScopedFacebook {
    /** @var \OmegaUp\ScopedSession */
    public $scopedSession;
    /** @var \Facebook\Facebook */
    public $facebook;

    public function __construct() {
        require_once 'libs/third_party/facebook-php-graph-sdk/src/Facebook/autoload.php';

        $this->scopedSession = new \OmegaUp\ScopedSession();
        $this->facebook = new \Facebook\Facebook([
            'app_id' => OMEGAUP_FB_APPID,
            'app_secret' => OMEGAUP_FB_SECRET,
            'default_graph_version' => 'v2.5',
        ]);
    }
}

/**
 * Session controller handles sessions.
 */
class Session extends \OmegaUp\Controllers\Controller {
    const AUTH_TOKEN_ENTROPY_SIZE = 15;
    /** @var null|array{valid: bool, email: string|null, user: \OmegaUp\DAO\VO\Users|null, identity: \OmegaUp\DAO\VO\Identities|null, auth_token: string|null, is_admin: bool} */
    private static $_currentSession = null;
    /** @var null|\OmegaUp\SessionManager */
    private static $_sessionManager = null;
    /** @var bool */
    private static $_setCookieOnRegisterSession = true;

    public static function getSessionManagerInstance(): \OmegaUp\SessionManager {
        if (is_null(self::$_sessionManager)) {
            self::$_sessionManager = new \OmegaUp\SessionManager();
        }
        return self::$_sessionManager;
    }

    public static function getFacebookLoginUrl(): string {
        $scopedFacebook = new ScopedFacebook();
        $helper = $scopedFacebook->facebook->getRedirectLoginHelper();
        return $helper->getLoginUrl(OMEGAUP_URL . '/login?fb', ['email']);
    }

    private static function isAuthTokenValid(string $authToken): bool {
        //do some other basic testing on authToken
        return true;
    }

    public static function currentSessionAvailable(): bool {
        $session = self::getCurrentSession();
        return !is_null($session['identity']);
    }

    /**
     * Returns information about current session. In order to avoid one full
     * server roundtrip (about ~100msec on each pageload), it also returns the
     * current time to be able to calculate the time delta between the
     * contestant's machine and the server.
     *
     * @return array{session: null|array{valid: bool, email: string|null, user: \OmegaUp\DAO\VO\Users|null, identity: \OmegaUp\DAO\VO\Identities|null, auth_token: string|null, is_admin: bool}, time: int}
     */
    public static function apiCurrentSession(?\OmegaUp\Request $r = null): array {
        return [
            'session' => self::getCurrentSession($r),
            'time' => \OmegaUp\Time::get(),
        ];
    }

    /**
     * @omegaup-request-param null|string $auth_token
     */
    private static function getAuthToken(\OmegaUp\Request $r): ?string {
        $sessionManager = self::getSessionManagerInstance();
        $authToken = null;
        if (!is_null($r['auth_token'])) {
            $authToken = strval($r['auth_token']);
        } else {
            $authToken = $sessionManager->getCookie(
                OMEGAUP_AUTH_TOKEN_COOKIE_NAME
            );
        }
        if (!is_null($authToken) && self::isAuthTokenValid($authToken)) {
            return $authToken;
        }
        if (
            isset($_REQUEST[OMEGAUP_AUTH_TOKEN_COOKIE_NAME])
                && self::isAuthTokenValid(
                    strval(
                        $_REQUEST[OMEGAUP_AUTH_TOKEN_COOKIE_NAME]
                    )
                )
        ) {
            return strval($_REQUEST[OMEGAUP_AUTH_TOKEN_COOKIE_NAME]);
        }
        return null;
    }

    /**
     * @omegaup-request-param null|string $auth_token
     *
     * @return array{valid: bool, email: ?string, user: ?\OmegaUp\DAO\VO\Users, identity: ?\OmegaUp\DAO\VO\Identities, auth_token: ?string, is_admin: bool}
     */
    public static function getCurrentSession(?\OmegaUp\Request $r = null): array {
        if (
            defined('OMEGAUP_SESSION_CACHE_ENABLED') &&
            OMEGAUP_SESSION_CACHE_ENABLED === true &&
            !is_null(self::$_currentSession)
        ) {
            return self::$_currentSession;
        }
        if (is_null($r)) {
            $r = new \OmegaUp\Request();
        }
        if (is_null($r['auth_token'])) {
            $authToken = self::getAuthToken($r);
            $r['auth_token'] = $authToken;
        } else {
            $authToken = strval($r['auth_token']);
        }
        if (
            defined('OMEGAUP_SESSION_CACHE_ENABLED') &&
            OMEGAUP_SESSION_CACHE_ENABLED === true &&
            !is_null($authToken)
        ) {
            self::$_currentSession = \OmegaUp\Cache::getFromCacheOrSet(
                \OmegaUp\Cache::SESSION_PREFIX,
                $authToken,
                /** @return array{valid: bool, email: string|null, user: \OmegaUp\DAO\VO\Users|null, identity: \OmegaUp\DAO\VO\Identities|null, auth_token: string|null, is_admin: bool} */
                function () use ($r) {
                    return self::getCurrentSessionImpl($r);
                },
                APC_USER_CACHE_SESSION_TIMEOUT
            );
            return self::$_currentSession;
        }
        self::$_currentSession = self::getCurrentSessionImpl($r);
        return self::$_currentSession;
    }

    /**
     * @omegaup-request-param null|string $auth_token
     *
     * @return array{valid: bool, email: string|null, user: \OmegaUp\DAO\VO\Users|null, identity: \OmegaUp\DAO\VO\Identities|null, auth_token: string|null, is_admin: bool}
     */
    private static function getCurrentSessionImpl(\OmegaUp\Request $r): array {
        if (empty($r['auth_token'])) {
            return [
                'valid' => false,
                'email' => null,
                'user' => null,
                'identity' => null,
                'auth_token' => null,
                'is_admin' => false,
            ];
        }
        $authToken = strval($r['auth_token']);

        $currentIdentity = \OmegaUp\DAO\AuthTokens::getIdentityByToken(
            $authToken
        );
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
            $currentUser = \OmegaUp\DAO\Users::getByPK(
                $currentIdentity->user_id
            );
            if (is_null($currentUser)) {
                throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
            }
            $email = !is_null($currentUser->main_email_id) ?
                \OmegaUp\DAO\Emails::getByPK($currentUser->main_email_id) :
                null;
        }

        return [
            'valid' => true,
            'email' => !empty($email) ? $email->email : '',
            'user' => $currentUser,
            'identity' => $currentIdentity,
            'auth_token' => $authToken,
            'is_admin' => \OmegaUp\Authorization::isSystemAdmin(
                $currentIdentity
            ),
        ];
    }

    /**
     * Invalidates the current user's session cache.
     */
    public static function invalidateCache(): void {
        $currentSession = self::getCurrentSession();
        if (
            is_null($currentSession['auth_token'])
        ) {
            return;
        }
        \OmegaUp\Cache::deleteFromCache(
            \OmegaUp\Cache::SESSION_PREFIX,
            $currentSession['auth_token']
        );
    }

    /**
     * Invalidates the current request's session cache.
     */
    public static function invalidateLocalCache(): void {
        self::$_currentSession = null;
    }

    public static function unregisterSession(): void {
        self::invalidateCache();

        $currentSession = self::getCurrentSession();
        if (
            is_null($currentSession['auth_token'])
        ) {
            return;
        }
        $authToken = new \OmegaUp\DAO\VO\AuthTokens([
            'token' => $currentSession['auth_token'],
        ]);

        self::invalidateLocalCache();

        try {
            \OmegaUp\DAO\AuthTokens::delete($authToken);
        } catch (\Exception $e) {
            // Best effort
            self::$log->error('Failed to delete expired tokens', $e);
        }

        setcookie(OMEGAUP_AUTH_TOKEN_COOKIE_NAME, 'deleted', 1, '/');
    }

    private static function registerSession(\OmegaUp\DAO\VO\Identities $identity): string {
        // Log the login.
        \OmegaUp\DAO\IdentityLoginLog::create(new \OmegaUp\DAO\VO\IdentityLoginLog([
            'identity_id' => intval($identity->identity_id),
            'ip' => ip2long(strval($_SERVER['REMOTE_ADDR'])),
        ]));

        self::invalidateLocalCache();

        //erase expired tokens
        try {
            \OmegaUp\DAO\AuthTokens::expireAuthTokens(
                intval($identity->identity_id)
            );
        } catch (\Exception $e) {
            // Best effort
            self::$log->error(
                "Failed to delete expired tokens: {$e->getMessage()}"
            );
        }

        // Create the new token
        $entropy = bin2hex(random_bytes(self::AUTH_TOKEN_ENTROPY_SIZE));
        /** @var int $identity->identity_id */
        $hash = hash(
            'sha256',
            OMEGAUP_MD5_SALT . $identity->identity_id . $entropy
        );
        $token = "{$entropy}-{$identity->identity_id}-{$hash}";

        \OmegaUp\DAO\AuthTokens::replace(new \OmegaUp\DAO\VO\AuthTokens([
            'user_id' => $identity->user_id,
            'identity_id' => $identity->identity_id,
            'token' => $token,
        ]));

        if (self::$_setCookieOnRegisterSession) {
            self::getSessionManagerInstance()->setCookie(
                OMEGAUP_AUTH_TOKEN_COOKIE_NAME,
                $token,
                0,
                '/'
            );
        }

        \OmegaUp\Cache::deleteFromCache(\OmegaUp\Cache::SESSION_PREFIX, $token);
        return $token;
    }

    private static function getUniqueUsernameFromEmail(string $email): string {
        $idx = strpos($email, '@');
        if ($idx === false) {
            $username = 'OmegaupUser';
        } else {
            $username = substr($email, 0, $idx);
        }

        try {
            \OmegaUp\Validators::validateValidUsername($username, 'username');
        } catch (\OmegaUp\Exceptions\InvalidParameterException $e) {
            // How can we know whats wrong with the username?
            // Things that could go wrong:
            //      generated email is too short
            $username = 'OmegaupUser';
        }

        /** @var string|int */
        $suffix = '';
        for (;;) {
            // Maybe we can bring all records from db
            // with prefix $username, beacuse this:
            $userexists = \OmegaUp\DAO\Users::FindByUsername(
                "{$username}{$suffix}"
            );
            // will query db every single time probably.

            if (empty($userexists)) {
                break;
            }

            if (is_int($suffix)) {
                $suffix++;
            } else {
                $suffix = 1;
            }
        }
        return "{$username}{$suffix}";
    }

    /**
     * @omegaup-request-param string $storeToken
     *
     * @return array<string, string>
     */
    public static function apiGoogleLogin(\OmegaUp\Request $r): array {
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['storeToken'],
            'storeToken'
        );

        require_once 'libs/third_party/google-api-php-client/src/Google/autoload.php';

        $client = new \Google_Client();
        $client->setClientId(OMEGAUP_GOOGLE_CLIENTID);
        $client->setClientSecret(OMEGAUP_GOOGLE_SECRET);

        try {
            $loginTicket = $client->verifyIdToken($r['storeToken']);
        } catch (\Google_Auth_Exception $ge) {
            throw new \OmegaUp\Exceptions\UnauthorizedException(
                'loginRequired',
                $ge
            );
        }

        $payload = $loginTicket->getAttributes()['payload'];

        // payload will have a superset of:
        //    [email] => johndoe@gmail.com
        //    [email_verified] => 1
        //    [name] => Alan Gonzalez
        //    [picture] => https://lh3.googleusercontent.com/-zrLvBe-AU/AAAAAAAAAAI/AAAAAAAAATU/hh0yUXEisCI/photo.jpg
        //    [locale] => en

        return self::LoginViaGoogle(
            $payload['email'],
            (isset($payload['name']) ? $payload['name'] : null)
        );
    }

    /**
     * @return array<string, string>
     */
    public static function LoginViaGoogle(
        string $email,
        ?string $name = null
    ): array {
        return self::ThirdPartyLogin('Google', $email, $name);
    }

    /**
     * Logs in via Facebook API.
     *
     * @return array<string, mixed>
     */
    public static function LoginViaFacebook(): array {
        // Mostly taken from
        // https://developers.facebook.com/docs/php/howto/example_facebook_login
        $scopedFacebook = new ScopedFacebook();
        $helper = $scopedFacebook->facebook->getRedirectLoginHelper();
        try {
            $accessToken = $helper->getAccessToken();
        } catch (\Facebook\Exceptions\FacebookResponseException $e) {
            return ['status' => 'error', 'error' => $e->getMessage()];
        } catch (\Facebook\Exceptions\FacebookSDKException $e) {
            return ['status' => 'error', 'error' => $e->getMessage()];
        }

        if (is_null($accessToken)) {
            $response = ['status' => 'error'];
            if (!is_null($helper->getError())) {
                $response['error'] = strval(
                    $helper->getError()
                ) . ' ' . strval(
                    $helper->getErrorDescription()
                );
            }
        }

        try {
            $fbResponse = $scopedFacebook->facebook->get(
                '/me?fields=name,email',
                $accessToken
            );
        } catch (\Facebook\Exceptions\FacebookResponseException $e) {
            return ['status' => 'error', 'error' => $e->getMessage()];
        } catch (\Facebook\Exceptions\FacebookSDKException $e) {
            return ['status' => 'error', 'error' => $e->getMessage()];
        }

        $fbUserProfile = $fbResponse->getGraphUser();
        self::$log->info('User is logged in via facebook !!');
        if (is_null($fbUserProfile->getEmail())) {
            self::$log->error('Facebook email empty');
            return [
                'status' => 'error',
                'error' => \OmegaUp\Translations::getInstance()->get(
                    'loginFacebookEmptyEmailError'
                ),
            ];
        }

        return self::ThirdPartyLogin(
            'Facebook',
            strval($fbUserProfile->getEmail()),
            $fbUserProfile->getName()
        );
    }

    /**
     * Does login for a user given username or email and password.
     *
     * @omegaup-request-param string $password
     * @omegaup-request-param string $usernameOrEmail
     */
    public static function nativeLogin(\OmegaUp\Request $r): string {
        \OmegaUp\Validators::validateStringNonEmpty($r['password'], 'password');
        \OmegaUp\Validators::validateStringNonEmpty(
            $r['usernameOrEmail'],
            'usernameOrEmail'
        );

        try {
            $identity = \OmegaUp\Controllers\Identity::resolveIdentity(
                $r['usernameOrEmail']
            );
        } catch (\OmegaUp\Exceptions\ApiException $e) {
            self::$log->warn("Identity {$r['usernameOrEmail']} not found.");
            throw new \OmegaUp\Exceptions\InvalidCredentialsException();
        }

        if (
            !\OmegaUp\Controllers\Identity::testPassword(
                $identity,
                $r['password']
            )
        ) {
            self::$log->warn(
                "Identity {$identity->username} has introduced invalid credentials."
            );
            throw new \OmegaUp\Exceptions\InvalidCredentialsException();
        }
        if (
            !is_null($identity->password)
            && \OmegaUp\SecurityTools::isOldHash($identity->password)
        ) {
            // Update the password using the new Argon2i algorithm.
            self::$log->warn(
                "Identity {$identity->username}'s password hash is being upgraded."
            );
            $identity->password = \OmegaUp\SecurityTools::hashString(
                $r['password']
            );
            \OmegaUp\DAO\Identities::update($identity);
        }

        self::$log->info(
            "Identity {$identity->username} has logged in natively."
        );

        if (!is_null($identity->user_id)) {
            $user = \OmegaUp\DAO\Users::getByPK($identity->user_id);
            if (is_null($user)) {
                throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
            }
            \OmegaUp\Controllers\User::checkEmailVerification($user, $identity);
        }

        try {
            return self::registerSession($identity);
        } catch (\Exception $e) {
            self::$log->error($e);
            throw new \OmegaUp\Exceptions\InvalidCredentialsException();
        }
    }

    public static function getLinkedInInstance(): \OmegaUp\LinkedIn {
        return new \OmegaUp\LinkedIn(
            OMEGAUP_LINKEDIN_CLIENTID,
            OMEGAUP_LINKEDIN_SECRET,
            OMEGAUP_URL . '/login?linkedin',
            isset($_GET['redirect']) ? strval($_GET['redirect']) : null
        );
    }
    public static function getLinkedInLoginUrl(): string {
        return self::getLinkedInInstance()->getLoginUrl();
    }

    /**
     * @return array<string, mixed>
     */
    public static function LoginViaLinkedIn(): array {
        if (empty($_GET['code']) || empty($_GET['state'])) {
            return ['status' => 'error'];
        }

        try {
            $li = self::getLinkedInInstance();
            $authToken = $li->getAuthToken(
                strval($_GET['code']),
                strval($_GET['state'])
            );
            $profile = $li->getProfileInfo($authToken);
            $redirect = $li->extractRedirect(strval($_GET['state']));
            if (!is_null($redirect)) {
                $_GET['redirect'] = $redirect;
            }

            return self::ThirdPartyLogin(
                'LinkedIn',
                $profile['emailAddress'],
                $profile['firstName'] . ' ' . $profile['lastName']
            );
        } catch (\OmegaUp\Exceptions\ApiException $e) {
            self::$log->error("Unable to login via LinkedIn: $e");
            return $e->asResponseArray();
        }
    }

    /**
     * @return array<string, string>
     */
    private static function ThirdPartyLogin(
        string $provider,
        string $email,
        ?string $name = null
    ) {
        // We trust this user's identity
        self::$log->info("User is logged in via $provider");
        $results = \OmegaUp\DAO\Identities::findByEmail($email);

        if (!is_null($results)) {
            self::$log->info("User has been here before with $provider");
            $identity = $results;
        } else {
            // The user has never been here before, let's register them
            self::$log->info("LoginVia$provider: Creating new user for $email");

            $username = self::getUniqueUsernameFromEmail($email);

            try {
                \OmegaUp\Controllers\User::createUser(
                    new \OmegaUp\CreateUserParams([
                        'name' => (!is_null($name) ? $name : $username),
                        'username' => $username,
                        'email' => $email,
                    ]),
                    /*ignorePassword=*/true,
                    /*forceVerification=*/true
                );
            } catch (\OmegaUp\Exceptions\ApiException $e) {
                self::$log->error("Unable to login via $provider: $e");
                /** @var array<string, string> */
                return $e->asResponseArray();
            }
            $identity = \OmegaUp\DAO\Identities::findByUsername($username);
            if (is_null($identity)) {
                throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
            }
        }

        self::registerSession($identity);
        return ['status' => 'ok'];
    }

    public static function setSessionManagerForTesting(
        \OmegaUp\SessionManager $sessionManager
    ): void {
        self::$_sessionManager = $sessionManager;
    }

    public static function setCookieOnRegisterSessionForTesting(bool $newValue): bool {
        $oldValue = self::$_setCookieOnRegisterSession;
        self::$_setCookieOnRegisterSession = $newValue;
        return $oldValue;
    }
}
