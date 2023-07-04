<?php

namespace OmegaUp;

/**
 * @psalm-type AssociatedIdentity=array{default: bool, username: string}
 * @psalm-type ApiToken=array{name: string, timestamp: \OmegaUp\Timestamp, last_used: \OmegaUp\Timestamp, rate_limit: array{reset: \OmegaUp\Timestamp, limit: int, remaining: int}}
 * @psalm-type CommonPayload=array{associatedIdentities: list<AssociatedIdentity>, currentEmail: string, currentName: null|string, currentUsername: string, gravatarURL128: string, gravatarURL51: string, isAdmin: bool, inContest: bool, isLoggedIn: bool, isMainUserIdentity: bool, isReviewer: bool, lockDownImage: string, navbarSection: string, omegaUpLockDown: bool, profileProgress: float, userClassname: string, userCountry: string, userTypes: list<string>, apiTokens: list<ApiToken>}
 * @psalm-type CurrentSession=array{associated_identities: list<AssociatedIdentity>, valid: bool, email: string|null, user: \OmegaUp\DAO\VO\Users|null, identity: \OmegaUp\DAO\VO\Identities|null, classname: string, auth_token: string|null, is_admin: bool}
 * @psalm-type RenderCallbackPayload=array{templateProperties: array{fullWidth?: bool, hideFooterAndHeader?: bool, payload: array<string, mixed>, scripts?: list<string>, title: \OmegaUp\TranslationString}, entrypoint: string, inContest?: bool, navbarSection?: string}
 */
class UITools {
    /** @var ?\Twig\Environment */
    private static $twig = null;

    /** @var array<string, mixed> */
    private static $twigContext = [];

    /**
     * If user is not logged in, redirect to login page
     */
    public static function redirectToLoginIfNotLoggedIn(): void {
        if (
            !is_null(
                \OmegaUp\Controllers\Session::getCurrentSession()['identity']
            )
        ) {
            return;
        }
        header(
            'Location: /login.php?redirect=' . urlencode(
                \OmegaUp\Request::getServerVar('REQUEST_URI') ?? '/'
            )
        );
        die();
    }

    /**
     * If user is not logged in or isn't an admin, redirect to home page
     */
    public static function redirectIfNoAdmin(): void {
        if (\OmegaUp\Controllers\Session::getCurrentSession()['is_admin']) {
            return;
        }
        header('Location: /');
        die();
    }

    /**
     * @return array{twig: \Twig\Environment, twigContext: array<string, mixed>}
     */
    public static function getTwigInstance() {
        if (!is_null(self::$twig)) {
            return [
                'twig' => self::$twig,
                'twigContext' => self::$twigContext,
            ];
        }

        $loader = new \OmegaUp\Template\Loader();
        $twigOptions = [
            'cache' => TEMPLATE_CACHE_DIR,
        ];
        /** @psalm-suppress TypeDoesNotContainType this can change depending on environment */
        if (
            defined('OMEGAUP_ENVIRONMENT') &&
            OMEGAUP_ENVIRONMENT === 'development'
        ) {
            $twigOptions['debug'] = true;
        }
        $twig = new \Twig\Environment($loader, $twigOptions);
        $twig->addTokenParser(new \OmegaUp\Template\EntrypointParser());
        $twig->addTokenParser(new \OmegaUp\Template\VersionHashParser());
        $twig->addTokenParser(new \OmegaUp\Template\JsIncludeParser());

        /** @var array<string, mixed> */
        $twigContext = [
            'GOOGLECLIENTID' => OMEGAUP_GOOGLE_CLIENTID,
            'NEW_RELIC_SCRIPT' => NEW_RELIC_SCRIPT,
            'ENABLE_SOCIAL_MEDIA_RESOURCES' => OMEGAUP_ENABLE_SOCIAL_MEDIA_RESOURCES,
            'ENABLED_EXPERIMENTS' => \OmegaUp\Experiments::getInstance()->getEnabledExperiments(),
            'OMEGAUP_GA_TRACK' => (defined(
                'OMEGAUP_GA_TRACK'
            )  && OMEGAUP_GA_TRACK && self::shouldReportToAnalytics()),
            'OMEGAUP_LOCKDOWN' => (defined(
                'OMEGAUP_LOCKDOWN'
            )  && OMEGAUP_LOCKDOWN),
            'OMEGAUP_MAINTENANCE' => (defined(
                'OMEGAUP_MAINTENANCE'
            )  && OMEGAUP_MAINTENANCE),
        ] + \OmegaUp\UITools::getNavbarHeaderContext();

        [
            'identity' => $identity,
        ] = \OmegaUp\Controllers\Session::getCurrentSession();

        $twigContext['LOCALE'] = \OmegaUp\Controllers\Identity::getPreferredLanguage(
            $identity
        );

        self::$twig = $twig;
        self::$twigContext = $twigContext;
        return [
            'twig' => self::$twig,
            'twigContext' => self::$twigContext,
        ];
    }

    public static function getFormattedGravatarURL(
        string $hashedEmail,
        string $size
    ): string {
        return "https://secure.gravatar.com/avatar/{$hashedEmail}?s={$size}";
    }

    private static function shouldReportToAnalytics(): bool {
        if (!isset($_COOKIE['accept_cookies'])) {
            return true;
        }
        return boolval($_COOKIE['accept_cookies']);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private static function getNavbarHeaderContext(
        $payload = [],
        bool $inContest = false,
        string $navbarSection = ''
    ): array {
        $headerPayload = self::getCommonPayload(
            $inContest,
            $navbarSection
        );
        return [
            'payload' => $payload + $headerPayload,
            'headerPayload' => $headerPayload,
        ];
    }

    /**
     * @return CommonPayload
     */
    private static function getCommonPayload(
        bool $inContest = false,
        string $navbarSection = ''
    ) {
        [
            'email' => $email,
            'identity' => $identity,
            'classname' => $userClassname,
            'user' => $user,
            'is_admin' => $isAdmin,
            'associated_identities' => $associatedIdentities,
            'api_tokens' => $apiTokens,
        ] = \OmegaUp\Controllers\Session::getCurrentSession();
        return [
            'omegaUpLockDown' => boolval(OMEGAUP_LOCKDOWN),
            'inContest' => $inContest,
            'isLoggedIn' => !is_null($identity),
            'isReviewer' => (
                !is_null($identity) ?
                \OmegaUp\Authorization::isQualityReviewer($identity) :
                false
            ),
            'gravatarURL51' => (
                is_null($email) ?
                '' :
                self::getFormattedGravatarURL(md5($email), '51')
            ),
            'gravatarURL128' => (
                is_null($email) ?
                '' :
                self::getFormattedGravatarURL(md5($email), '128')
            ),
            'currentUsername' => (
                !is_null($identity) && !is_null($identity->username) ?
                $identity->username :
                ''
            ),
            'currentName' => !is_null($identity) ? $identity->name : null,
            'currentEmail' => $email ?? '',
            'associatedIdentities' => $associatedIdentities,
            'apiTokens' => $apiTokens,
            'userClassname' => $userClassname,
            'userCountry' => (!is_null(
                $identity
            ) ? $identity->country_id : null) ?? 'xx',
            'profileProgress' => \OmegaUp\Controllers\User::getProfileProgress(
                $user
            ),
            'isMainUserIdentity' => !is_null($user),
            'isAdmin' => $isAdmin,
            'lockDownImage' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAA6UlEQVQ4jd2TMYoCMRiFv5HBwnJBsFqEiGxtISps6RGmFD2CZRr7aQSPIFjmCGsnrFYeQJjGytJKRERsfp2QmahY+iDk5c97L/wJCchBFCclYAD8SmkBTI1WB1cb5Ji/gT+g7mxtgK7RausNiOIEYAm0pHSWOZR5BbSNVndPwTmlaZnnQFnGXGot0XgDfiw+NlrtjVZ7YOzRZAJCix893NZkAi4eYejRpJcYxckQ6AENKf0DO+EVoCN8DcyMVhM3eQR8WesO+WgAVWDituC28wiFDHkXHxBgv0IfKL7oO+UF1Ei/7zMsbuQKTFoqpb8KS2AAAAAASUVORK5CYII=',
            'navbarSection' => $navbarSection,
            'userTypes' => (
                !is_null($identity) &&
                !is_null($user) ?
                \OmegaUp\Controllers\User::getUserTypes($user, $identity) :
                []
            ),
        ];
    }

    /**
     * @param callable(\OmegaUp\Request):RenderCallbackPayload $callback
     */
    public static function render(callable $callback): void {
        [
            'twig' => $twig,
            'twigContext' => $twigContext,
        ] = self::getTwigInstance();
        try {
            $response = $callback(new Request($_REQUEST));
            $twigProperties = $response['templateProperties'];
            $entrypoint = $response['entrypoint'];
            $inContest = $response['inContest'] ?? false;
            $navbarSection = $response['navbarSection'] ?? '';
            /** @var array<string, mixed> */
            $payload = $twigProperties['payload'] ?? [];
        } catch (\OmegaUp\Exceptions\ExitException $e) {
            // The callback explicitly requested to exit.
            exit;
        } catch (\Exception $e) {
            \OmegaUp\ApiCaller::handleException($e);
        }

        $titleVar = $twigProperties['title']->message;
        /**
         * @psalm-suppress TranslationStringNotALiteralString this is being
         * checked from the constructor of the exception
         */
        $localizedText = \OmegaUp\Translations::getInstance()->get($titleVar);
        $twigProperties['title'] = \OmegaUp\ApiUtils::formatString(
            $localizedText,
            $twigProperties['title']->args
        );

        $twigContext = array_merge(
            $twigContext,
            $twigProperties,
            \OmegaUp\UITools::getNavbarHeaderContext(
                $payload,
                $inContest,
                $navbarSection
            ),
        );

        $twig->display($entrypoint, $twigContext);
    }
}
