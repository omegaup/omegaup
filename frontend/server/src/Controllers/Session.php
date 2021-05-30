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
 * @psalm-type AssociatedIdentity=array{default: bool, username: string}
 * @psalm-type IdentityExt=array{classname: string, country_id: null|string, current_identity_school_id: int|null, gender: null|string, identity_id: int, language_id: int|null, name: null|string, password: null|string, state_id: null|string, user_id: int|null, username: string}
 * @psalm-type AuthIdentityExt=array{currentIdentity: IdentityExt, loginIdentity: IdentityExt}
 * @psalm-type CurrentSession=array{apiTokenId: int|null, associated_identities: list<AssociatedIdentity>, auth_token: null|string, cacheKey: null|string, classname: string, email: null|string, identity: \OmegaUp\DAO\VO\Identities|null, is_admin: bool, loginIdentity: \OmegaUp\DAO\VO\Identities|null, user: \OmegaUp\DAO\VO\Users|null, valid: bool}
 */
class Session extends \OmegaUp\Controllers\Controller {
    const AUTH_TOKEN_ENTROPY_SIZE = 15;
    /** @var null|CurrentSession */
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
     * @omegaup-request-param null|string $auth_token
     *
     * @return array{session: null|CurrentSession, time: int}
     */
    public static function apiCurrentSession(?\OmegaUp\Request $r = null): array {
        return [
            'session' => self::getCurrentSession($r),
            'time' => \OmegaUp\Time::get(),
        ];
    }

    /**
     * @return array{token:string, username:string|null, cacheKey: string}|null
     */
    private static function getAPIToken() {
        $token = self::getSessionManagerInstance()->getTokenAuthorization();
        if (is_null($token)) {
            return null;
        }
        if (strpos($token, ',') === false) {
            return [
                'token' => $token,
                'username' => null,
                'cacheKey' => "api-token:${token}",
            ];
        }
        $tokens = explode(',', $token);
        /** @var array<string, string> */
        $authorization = [];
        foreach ($tokens as $token) {
            $kvp = explode('=', $token, 2);
            if (count($kvp) != 2) {
                throw new \OmegaUp\Exceptions\UnauthorizedException();
            }
            $authorization[trim($kvp[0])] = trim($kvp[1]);
        }
        if (
            !isset($authorization['Credential']) ||
            !isset($authorization['Username'])
        ) {
            throw new \OmegaUp\Exceptions\UnauthorizedException();
        }
        return [
            'token' => $authorization['Credential'],
            'username' => $authorization['Username'],
            'cacheKey' => "api-token:${authorization['Credential']}:${authorization['Username']}",
        ];
    }

    /**
     * @omegaup-request-param null|string $auth_token
     */
    private static function getAuthToken(\OmegaUp\Request $r): ?string {
        $sessionManager = self::getSessionManagerInstance();
        $authToken = $r->ensureOptionalString('auth_token');
        if (is_null($authToken)) {
            $authToken = $sessionManager->getCookie(
                OMEGAUP_AUTH_TOKEN_COOKIE_NAME
            );
        }
        if (!is_null($authToken) && self::isAuthTokenValid($authToken)) {
            return $authToken;
        }

        $cookie = \OmegaUp\Request::getRequestVar(
            OMEGAUP_AUTH_TOKEN_COOKIE_NAME
        );
        if (!empty($cookie) && self::isAuthTokenValid($cookie)) {
            return $cookie;
        }
        return null;
    }

    /**
     * @omegaup-request-param null|string $auth_token
     *
     * @return CurrentSession
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
        $apiToken = self::getAPIToken();
        if (!is_null($apiToken)) {
            if (
                defined('OMEGAUP_SESSION_CACHE_ENABLED') &&
                OMEGAUP_SESSION_CACHE_ENABLED === true
            ) {
                self::$_currentSession = \OmegaUp\Cache::getFromCacheOrSet(
                    \OmegaUp\Cache::SESSION_PREFIX,
                    $apiToken['cacheKey'],
                    fn () => self::getCurrentSessionImplForAPIToken(
                        $apiToken['token'],
                        $apiToken['username'],
                        $apiToken['cacheKey'],
                    ),
                    APC_USER_CACHE_SESSION_TIMEOUT
                );
            } else {
                self::$_currentSession = self::getCurrentSessionImplForAPIToken(
                    $apiToken['token'],
                    $apiToken['username'],
                    $apiToken['cacheKey'],
                );
            }
            if (is_null(self::$_currentSession['apiTokenId'])) {
                throw new \OmegaUp\Exceptions\UnauthorizedException();
            }
            $now = new \OmegaUp\Timestamp(\OmegaUp\Time::get());
            $usageData = \OmegaUp\DAO\APITokens::updateUsage(
                self::$_currentSession['apiTokenId'],
                $now,
            );
            if (is_null($usageData)) {
                throw new \OmegaUp\Exceptions\UnauthorizedException();
            }
            $sessionManagerInstance = self::getSessionManagerInstance();
            $sessionManagerInstance->setHeader(
                "X-RateLimit-Limit: {$usageData['limit']}"
            );
            $sessionManagerInstance->setHeader(
                "X-RateLimit-Remaining: ${usageData['remaining']}"
            );
            $sessionManagerInstance->setHeader(
                "X-RateLimit-Reset: {$usageData['reset']->time}"
            );
            if ($usageData['remaining'] === 0) {
                $retryAfter = $usageData['reset']->time - $now->time;
                $sessionManagerInstance->setHeader(
                    "Retry-After: {$retryAfter}"
                );
                throw new \OmegaUp\Exceptions\RateLimitExceededException();
            }
        } else {
            $authToken = $r->ensureOptionalString('auth_token');
            if (is_null($authToken)) {
                $authToken = self::getAuthToken($r);
                $r['auth_token'] = $authToken;
            }
            if (
                defined('OMEGAUP_SESSION_CACHE_ENABLED') &&
                OMEGAUP_SESSION_CACHE_ENABLED === true &&
                !is_null($authToken)
            ) {
                self::$_currentSession = \OmegaUp\Cache::getFromCacheOrSet(
                    \OmegaUp\Cache::SESSION_PREFIX,
                    $authToken,
                    fn () => self::getCurrentSessionImplForAuthToken(
                        $authToken
                    ),
                    APC_USER_CACHE_SESSION_TIMEOUT
                );
            } else {
                self::$_currentSession = self::getCurrentSessionImplForAuthToken(
                    $authToken
                );
            }
        }
        return self::$_currentSession;
    }

    /**
     * @return CurrentSession
     */
    private static function getCurrentSessionImplForAuthToken(?string $authToken): array {
        if (!empty($authToken)) {
            $identityExt = \OmegaUp\DAO\AuthTokens::getIdentityByToken(
                $authToken
            );
        } else {
            $identityExt = null;
        }
        if (is_null($identityExt) || is_null($authToken)) {
            // Means user has auth token, but does not exist in DB
            return [
                'valid' => false,
                'email' => null,
                'user' => null,
                'identity' => null,
                'loginIdentity' => null,
                'classname' => 'user-rank-unranked',
                'apiTokenId' => null,
                'auth_token' => null,
                'cacheKey' => null,
                'is_admin' => false,
                'associated_identities' => [],
            ];
        }
        return self::getCurrentSessionImpl(
            $identityExt,
            $authToken,
            $authToken,
            /*$apiTokenId=*/null,
        );
    }

    /**
     * @return CurrentSession
     */
    private static function getCurrentSessionImplForAPIToken(
        string $apiToken,
        ?string $username,
        string $cacheKey
    ): array {
        $identityExt = \OmegaUp\DAO\APITokens::getIdentityByToken(
            $apiToken,
            $username
        );
        if (is_null($identityExt)) {
            throw new \OmegaUp\Exceptions\UnauthorizedException();
        }
        $apiTokenId = $identityExt['apiTokenId'];
        unset($identityExt['apiTokenId']);
        return self::getCurrentSessionImpl(
            $identityExt,
            $cacheKey,
            null,
            $apiTokenId,
        );
    }

    /**
     * @param AuthIdentityExt $identityExt
     *
     * @return CurrentSession
     */
    private static function getCurrentSessionImpl(
        $identityExt,
        string $cacheKey,
        ?string $authToken,
        ?int $apiTokenId
    ): array {
        [
            'currentIdentity' => $currentIdentityExt,
            'loginIdentity' => $loginIdentityExt,
        ] = $identityExt;
        $identityClassname = $currentIdentityExt['classname'];
        unset($currentIdentityExt['classname'], $loginIdentityExt['classname']);
        $currentIdentity = new \OmegaUp\DAO\VO\Identities($currentIdentityExt);
        $loginIdentity = new \OmegaUp\DAO\VO\Identities($loginIdentityExt);

        $associatedIdentities = [];
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
            if ($currentUser->main_identity_id === $loginIdentity->identity_id) {
                $associatedIdentities = \OmegaUp\DAO\Identities::getAssociatedIdentities(
                    $currentIdentity
                );
            }
        }

        return [
            'valid' => true,
            'email' => !empty($email) ? $email->email : '',
            'user' => $currentUser,
            'identity' => $currentIdentity,
            'loginIdentity' => $loginIdentity,
            'classname' => $identityClassname,
            'cacheKey' => $cacheKey,
            'apiTokenId' => $apiTokenId,
            'auth_token' => $authToken,
            'is_admin' => \OmegaUp\Authorization::isSystemAdmin(
                $currentIdentity
            ),
            'associated_identities' => $associatedIdentities,
        ];
    }

    /**
     * Invalidates the current user's session cache.
     */
    public static function invalidateCache(): void {
        $currentSession = self::getCurrentSession();
        if (
            is_null($currentSession['cacheKey'])
        ) {
            return;
        }
        \OmegaUp\Cache::deleteFromCache(
            \OmegaUp\Cache::SESSION_PREFIX,
            $currentSession['cacheKey']
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

    private static function registerSession(
        \OmegaUp\DAO\VO\Identities $identity,
        ?\OmegaUp\DAO\VO\Users $user
    ): string {
        // Log the login.
        \OmegaUp\DAO\IdentityLoginLog::create(new \OmegaUp\DAO\VO\IdentityLoginLog([
            'identity_id' => intval($identity->identity_id),
            'ip' => ip2long(
                \OmegaUp\Request::getServerVar('REMOTE_ADDR') ?? ''
            ),
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
     * @return array{isAccountCreation: bool}
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
     * @return array{isAccountCreation: bool}
     */
    public static function LoginViaGoogle(
        string $email,
        ?string $name = null
    ): array {
        return self::thirdPartyLogin('Google', $email, $name);
    }

    /**
     * Logs in via Facebook API.
     */
    public static function loginViaFacebook(): void {
        // Mostly taken from
        // https://developers.facebook.com/docs/php/howto/example_facebook_login
        $scopedFacebook = new ScopedFacebook();
        $helper = $scopedFacebook->facebook->getRedirectLoginHelper();
        try {
            $accessToken = $helper->getAccessToken();
        } catch (\Facebook\Exceptions\FacebookResponseException $e) {
            $errorMessage = $e->getMessage();
            self::$log->error("Graph returned an error: {$errorMessage}");
            throw $e;
        } catch (\Facebook\Exceptions\FacebookSDKException $e) {
            $errorMessage = $e->getMessage();
            self::$log->error(
                "Facebook SDK returned an error: {$errorMessage}"
            );
            throw $e;
        }

        if (is_null($accessToken) && !is_null($helper->getError())) {
            $errorDescription = $helper->getErrorDescription();
            self::$log->error(
                "Unable to login via Facebook: {$errorDescription}"
            );
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'parameterNotFound',
                'token'
            );
        }

        try {
            $fbResponse = $scopedFacebook->facebook->get(
                '/me?fields=name,email',
                $accessToken
            );
        } catch (\Facebook\Exceptions\FacebookResponseException $e) {
            self::$log->error("Unable to login via Facebook: {$e}");
            throw $e;
        } catch (\Facebook\Exceptions\FacebookSDKException $e) {
            self::$log->error("Unable to login via Facebook: {$e}");
            throw $e;
        }

        $fbUserProfile = $fbResponse->getGraphUser();
        self::$log->info('User is logged in via facebook !!');
        if (is_null($fbUserProfile->getEmail())) {
            self::$log->error('Facebook email empty');
            throw new \OmegaUp\Exceptions\InvalidParameterException(
                'loginFacebookEmptyEmailError',
                'error'
            );
        }

        \OmegaUp\Controllers\Session::thirdPartyLogin(
            'Facebook',
            strval($fbUserProfile->getEmail()),
            $fbUserProfile->getName()
        );

        self::redirect();
    }

    public static function loginViaLinkedIn(
        string $code,
        string $state,
        ?string $redirect
    ): void {
        try {
            $li = self::getLinkedInInstance($redirect);
            $authToken = $li->getAuthToken($code, $state);
            $profile = $li->getProfileInfo($authToken);
            $redirect = $li->extractRedirect($state);
        } catch (\OmegaUp\Exceptions\ApiException $e) {
            self::$log->error("Unable to login via LinkedIn: $e");
            throw $e;
        }
        \OmegaUp\Controllers\Session::thirdPartyLogin(
            'LinkedIn',
            $profile['emailAddress'],
            "{$profile['firstName']} {$profile['lastName']}"
        );

        self::redirect($redirect);
    }

    private static function getLinkedInInstance(
        ?string $redirect = null
    ): \OmegaUp\LinkedIn {
        return new \OmegaUp\LinkedIn(
            OMEGAUP_LINKEDIN_CLIENTID,
            OMEGAUP_LINKEDIN_SECRET,
            OMEGAUP_URL . '/login?linkedin',
            $redirect
        );
    }

    public static function getLinkedInLoginUrl(): string {
        return self::getLinkedInInstance()->getLoginUrl();
    }

    private static function getRedirectUrl(?string $url = null): string {
        $defaultRedirectUrl = '/profile/';
        if (is_null($url)) {
            return $defaultRedirectUrl;
        }
        $redirectParsedUrl = parse_url($url);
        // If a malformed URL is given, don't redirect.
        if ($redirectParsedUrl === false) {
            return $defaultRedirectUrl;
        }
        // Just the path portion of the URL was given.
        if (
            empty($redirectParsedUrl['scheme']) ||
            empty($redirectParsedUrl['host'])
        ) {
            $path = $redirectParsedUrl['path'] ?? '';
            return $path !== '/logout/' ? $url : $defaultRedirectUrl;
        }
        $redirectUrl = "{$redirectParsedUrl['scheme']}://{$redirectParsedUrl['host']}";
        if (isset($redirectParsedUrl['port'])) {
            $redirectUrl .= ":{$redirectParsedUrl['port']}";
        }
        return $redirectUrl === OMEGAUP_URL ? $url : $defaultRedirectUrl;
    }

    private static function redirect(?string $redirect = null): void {
        $redirectUrl = self::getRedirectUrl($redirect);
        header("Location: {$redirectUrl}");
        throw new \OmegaUp\Exceptions\ExitException();
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

        $user = null;
        if (!is_null($identity->user_id)) {
            $user = \OmegaUp\DAO\Users::getByPK($identity->user_id);
            if (is_null($user)) {
                throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
            }
            \OmegaUp\Controllers\User::checkEmailVerification($user, $identity);
        }

        try {
            return self::registerSession($identity, $user);
        } catch (\Exception $e) {
            self::$log->error($e);
            throw new \OmegaUp\Exceptions\InvalidCredentialsException();
        }
    }

    /**
     * Does login for an identity given username or email. Password no needed
     * because user should have a successful native login
     *
     * @omegaup-request-param null|string $auth_token
     */
    public static function loginWithAssociatedIdentity(
        \OmegaUp\Request $r,
        string $usernameOrEmail,
        \OmegaUp\DAO\VO\Identities $loggedIdentity
    ): void {
        // Only users that originally logged in from their main identities can
        // select another identity.
        if (!$r->isLoggedAsMainIdentity()) {
            throw new \OmegaUp\Exceptions\UnauthorizedException(
                'userNotAllowed'
            );
        }

        $currentSession = self::getCurrentSession($r);
        if (is_null($currentSession['auth_token'])) {
            self::$log->warn('Auth token not found.');
            throw new \OmegaUp\Exceptions\UnauthorizedException(
                'loginRequired'
            );
        }

        $identity = \OmegaUp\DAO\Identities::resolveAssociatedIdentity(
            $usernameOrEmail,
            $loggedIdentity
        );
        if (is_null($identity) || is_null($identity->identity_id)) {
            self::$log->warn("Identity {$usernameOrEmail} not found.");
            throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
        }

        self::$log->info(
            "User {$loggedIdentity->username} has logged with associated identity {$identity->username}."
        );

        try {
            \OmegaUp\DAO\AuthTokens::updateActingIdentityId(
                $currentSession['auth_token'],
                $identity->identity_id
            );
            self::invalidateCache();

            self::getCurrentSession($r);
        } catch (\OmegaUp\Exceptions\ApiException $e) {
            self::$log->error($e);
            throw $e;
        }
    }

    /**
     * @return array{isAccountCreation: bool}
     */
    private static function thirdPartyLogin(
        string $provider,
        string $email,
        ?string $name = null
    ): array {
        // We trust this user's identity
        self::$log->info("User is logged in via $provider");
        $results = \OmegaUp\DAO\Identities::findByEmail($email);
        $isAccountCreation = true;
        if (!is_null($results)) {
            self::$log->info("User has been here before with $provider");
            $identity = $results;
            $isAccountCreation = false;
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
                throw $e;
            }
            $identity = \OmegaUp\DAO\Identities::findByUsername($username);
            if (is_null($identity)) {
                throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
            }
        }
        if (is_null($identity->username) || is_null($identity->user_id)) {
            throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
        }
        $user = \OmegaUp\DAO\Users::getByPK($identity->user_id);
        if (is_null($user)) {
            throw new \OmegaUp\Exceptions\NotFoundException('userNotExist');
        }

        self::registerSession($identity, $user);

        return ['isAccountCreation' => $isAccountCreation];
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
