<?php

namespace OmegaUp;

/**
 * @psalm-type CommonPayload=array{associatedIdentities: list<array{default: bool, username: string}>, bootstrap4: bool, currentEmail: string, currentName: null|string, currentUsername: string, gravatarURL128: string, gravatarURL51: string, inContest: bool, isAdmin: bool, isLoggedIn: bool, isMainUserIdentity: bool, isReviewer: bool, lockDownImage: string, navbarSection: string, omegaUpLockDown: bool, profileProgress: float, userClassname: null|string, userCountry: string}
 * @psalm-type AssociatedIdentity=array{username: string, default: bool}
 * @psalm-type CurrentSession=array{associated_identities: list<AssociatedIdentity>, valid: bool, email: string|null, user: \OmegaUp\DAO\VO\Users|null, identity: \OmegaUp\DAO\VO\Identities|null, classname: string, auth_token: string|null, is_admin: bool}
 */
class UITools {
    /** @var ?\Smarty */
    private static $smarty = null;

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
     * @return \Smarty
     */
    public static function getSmartyInstance() {
        if (!is_null(self::$smarty)) {
            return self::$smarty;
        }
        require_once 'libs/third_party/smarty/libs/Smarty.class.php';

        $smarty = new \Smarty();
        $smarty->setTemplateDir(dirname(__DIR__, 2) . '/templates/');

        $smarty->assign('CURRENT_USER_IS_ADMIN', 0);
        if (defined('SMARTY_CACHE_DIR')) {
            $smarty->setCacheDir(
                SMARTY_CACHE_DIR
            )->setCompileDir(
                SMARTY_CACHE_DIR
            );
        }

        $smarty->assign('GOOGLECLIENTID', OMEGAUP_GOOGLE_CLIENTID);
        $smarty->assign(
            'ENABLE_SOCIAL_MEDIA_RESOURCES',
            OMEGAUP_ENABLE_SOCIAL_MEDIA_RESOURCES
        );
        $smarty->assign('LOGGED_IN', '0');

        /** @psalm-suppress RedundantCondition OMEGAUP_GA_TRACK may be defined differently. */
        if (defined('OMEGAUP_GA_TRACK')  && OMEGAUP_GA_TRACK) {
            $smarty->assign('OMEGAUP_GA_TRACK', 1);
            $smarty->assign('OMEGAUP_GA_ID', OMEGAUP_GA_ID);
        } else {
            $smarty->assign('OMEGAUP_GA_TRACK', 0);
        }

        // Not sure why this makes Psalm complain, but no other invocation of
        // getCurrentSession() does so.
        /** @var CurrentSession */
        [
            'email' => $email,
            'identity' => $identity,
            'user' => $user,
            'is_admin' => $isAdmin,
        ] = \OmegaUp\Controllers\Session::getCurrentSession();
        if (!is_null($identity) && !is_null($identity->username)) {
            $smarty->assign('LOGGED_IN', '1');

            $smarty->assign(
                'CURRENT_USER_USERNAME',
                $identity->username
            );
            $smarty->assign('CURRENT_USER_EMAIL', $email);
            $smarty->assign(
                'CURRENT_USER_IS_EMAIL_VERIFIED',
                empty($user) || $user->verified
            );
            $smarty->assign('CURRENT_USER_IS_ADMIN', $isAdmin);
            $smarty->assign(
                'CURRENT_USER_IS_REVIEWER',
                \OmegaUp\Authorization::isQualityReviewer($identity)
            );
            $smarty->assign(
                'CURRENT_USER_GRAVATAR_URL_128',
                \OmegaUp\UITools::getFormattedGravatarURL(md5($email), '128')
            );
            $smarty->assign(
                'CURRENT_USER_GRAVATAR_URL_16',
                \OmegaUp\UITools::getFormattedGravatarURL(md5($email), '16')
            );
            $smarty->assign(
                'CURRENT_USER_GRAVATAR_URL_32',
                \OmegaUp\UITools::getFormattedGravatarURL(md5($email), '32')
            );
            $smarty->assign(
                'CURRENT_USER_GRAVATAR_URL_51',
                \OmegaUp\UITools::getFormattedGravatarURL(md5($email), '51')
            );
        } else {
            $smarty->assign(
                'CURRENT_USER_GRAVATAR_URL_128',
                '/media/avatar_92.png'
            );
            $smarty->assign(
                'CURRENT_USER_GRAVATAR_URL_16',
                '/media/avatar_16.png'
            );
        }

        /** @psalm-suppress TypeDoesNotContainType OMEGAUP_ENVIRONMENT is a configurable value. */
        if (
            defined('OMEGAUP_ENVIRONMENT') &&
            OMEGAUP_ENVIRONMENT === 'development'
        ) {
            $smarty->force_compile = true;
        } else {
            $smarty->compile_check = false;
        }

        /** @var string */
        $_lang = \OmegaUp\Controllers\Identity::getPreferredLanguage($identity);
        $smarty->configLoad(dirname(__DIR__, 2) . "/templates/{$_lang}.lang");
        $smarty->addPluginsDir(dirname(__DIR__, 2) . '/smarty_plugins/');

        // TODO: It should be removed when all templates call render function
        \OmegaUp\UITools::assignSmartyNavbarHeader($smarty);

        $smarty->assign(
            'ENABLED_EXPERIMENTS',
            \OmegaUp\Experiments::getInstance()->getEnabledExperiments()
        );
        self::$smarty = $smarty;
        return $smarty;
    }

    public static function getFormattedGravatarURL(
        string $hashedEmail,
        string $size
    ): string {
        return "https://secure.gravatar.com/avatar/{$hashedEmail}?s={$size}";
    }

    /**
     * @param array<string, mixed> $payload
     */
    private static function assignSmartyNavbarHeader(
        \Smarty $smarty,
        $payload = [],
        bool $inContest = false,
        bool $supportsBootstrap4 = false,
        string $navbarSection = ''
    ): void {
        $headerPayload = self::getCommonPayload(
            $smarty,
            $inContest,
            $supportsBootstrap4,
            $navbarSection
        );
        $smarty->assign('payload', $payload + $headerPayload);
        $smarty->assign('headerPayload', $headerPayload);
    }

    /**
     * Returns whether Bootstrap 4 will be used. This is only true if either:
     *
     * - The user explicitly requests bootstrap4 to be used regardless of
     *   underlying support by the components by passing the request parameter
     *   bootstrap4=force.
     * - The user requests bootstrap4 to be used if the component supports it
     *   by passing the request parameter bootstrap4=true.
     */
    private static function useBootstrap4(
        bool $supportsBootstrap4
    ): bool {
        if (!isset($_REQUEST['bootstrap4'])) {
            return false;
        }
        if ($_REQUEST['bootstrap4'] === 'force') {
            // User can force the use of bootstrap4, just to see how awful it
            // would look like.
            return true;
        }
        return (
            $supportsBootstrap4 &&
            boolval($_REQUEST['bootstrap4'])
        );
    }

    /**
     * @return CommonPayload
     */
    private static function getCommonPayload(
        \Smarty $smarty,
        bool $inContest = false,
        bool $supportsBootstrap4 = false,
        string $navbarSection = ''
    ) {
        [
            'email' => $email,
            'identity' => $identity,
            'classname' => $userClassname,
            'user' => $user,
            'is_admin' => $isAdmin,
            'associated_identities' => $associatedIdentities,
        ] = \OmegaUp\Controllers\Session::getCurrentSession();
        return [
            'omegaUpLockDown' => OMEGAUP_LOCKDOWN,
            'bootstrap4' => self::useBootstrap4($supportsBootstrap4),
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
        ];
    }

    /**
     * @param callable(\OmegaUp\Request):array{smartyProperties: array{fullWidth?: bool, payload: array<string, mixed>, scripts?: list<string>, title: \OmegaUp\TranslationString}, entrypoint: string, template?: string, inContest?: bool, supportsBootstrap4?: bool, navbarSection?: string}|callable(\OmegaUp\Request):array{smartyProperties: array<string, mixed>, entrypoint?: string, template?: string, inContest?: bool, supportsBootstrap4?: bool, navbarSection?: string} $callback
     */
    public static function render(callable $callback): void {
        $smarty = self::getSmartyInstance();
        try {
            $response = $callback(new Request($_REQUEST));
            $smartyProperties = $response['smartyProperties'];
            $entrypoint = $response['entrypoint'] ?? null;
            $template = $response['template'] ?? '';
            $supportsBootstrap4 = $response['supportsBootstrap4'] ?? false;
            $inContest = $response['inContest'] ?? false;
            $navbarSection = $response['navbarSection'] ?? '';
            /** @var array<string, mixed> */
            $payload = $smartyProperties['payload'] ?? [];
        } catch (\OmegaUp\Exceptions\ExitException $e) {
            // The callback explicitly requested to exit.
            exit;
        } catch (\Exception $e) {
            \OmegaUp\ApiCaller::handleException($e);
        }

        if (!is_null($entrypoint)) {
            if (
                isset($smartyProperties['title'])  &&
                is_object($smartyProperties['title']) &&
                is_a($smartyProperties['title'], 'OmegaUp\TranslationString')
            ) {
                $titleVar = $smartyProperties['title']->message;
                /** @var string */
                $translationString = $smarty->getConfigVars($titleVar);
                $smartyProperties['title'] = \OmegaUp\ApiUtils::formatString(
                    $translationString,
                    $smartyProperties['title']->args
                );
            } elseif (
                !isset($smartyProperties['title']) ||
                !is_string($smartyProperties['title'])
            ) {
                $titleVar = (
                    'omegaupTitle' .
                    str_replace('_', '', ucwords($entrypoint, '_'))
                );
                /** @var string */
                $smartyProperties['title'] = $smarty->getConfigVars($titleVar);
            }
        }

        /** @var mixed $value */
        foreach ($smartyProperties as $key => $value) {
            $smarty->assign($key, $value);
        }

        \OmegaUp\UITools::assignSmartyNavbarHeader(
            $smarty,
            $payload,
            $inContest,
            $supportsBootstrap4,
            $navbarSection
        );

        if (!is_null($entrypoint)) {
            $smarty->display(
                sprintf(
                    (
                        'extends:file:%s/templates/template.tpl|' .
                        'string:{block name="entrypoint"}{js_include entrypoint="' .
                        $entrypoint .
                        '" async}{/block}'
                    ),
                    strval(OMEGAUP_ROOT),
                    $template
                )
            );
        } else {
            $smarty->display(
                sprintf(
                    '%s/templates/%s',
                    strval(OMEGAUP_ROOT),
                    $template
                )
            );
        }
    }

    /**
     * Return the path of a Smarty template.
     */
    public static function templatePath(string $templateName): string {
        return sprintf(
            '%s/templates/%s.tpl',
            strval(OMEGAUP_ROOT),
            $templateName
        );
    }
}
