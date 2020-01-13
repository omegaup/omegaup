<?php

namespace OmegaUp;

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
                strval(
                    $_SERVER['REQUEST_URI']
                )
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
        /** @var array{valid: bool, email: string|null, user: \OmegaUp\DAO\VO\Users|null, identity: \OmegaUp\DAO\VO\Identities|null, auth_token: string|null, is_admin: bool} */
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

            $smarty->assign(
                'currentUserInfo',
                [
                    'username' => $identity->username,
                ]
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

        $identityRequest = new \OmegaUp\Request();
        $identityRequest['username'] = is_null(
            $identity
        ) ? null : $identity->username;
        /** @var string */
        $_lang = \OmegaUp\Controllers\Identity::getPreferredLanguage(
            $identityRequest
        );
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

    private static function assignSmartyNavbarHeader(
        \Smarty $smarty,
        bool $inContest = false,
        string $navbarSection = ''
    ): void {
        [
            'email' => $email,
            'identity' => $identity,
            'is_admin' => $isAdmin,
        ] = \OmegaUp\Controllers\Session::getCurrentSession();
        $smarty->assign(
            'headerPayload',
            [
                'omegaUpLockDown' => OMEGAUP_LOCKDOWN,
                'inContest' => $inContest,
                'isLoggedIn' => !is_null($identity),
                'isReviewer' => !is_null(
                    $identity
                ) ? \OmegaUp\Authorization::isQualityReviewer(
                    $identity
                ) : false,
                'gravatarURL51' => is_null($email) ? '' :
                  self::getFormattedGravatarURL(md5($email), '51'),
                'currentUsername' =>
                    !is_null(
                        $identity
                    ) && !is_null(
                        $identity->username
                    ) ? $identity->username :
                    '',
                'isAdmin' => $isAdmin,
                'lockDownImage' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAA6UlEQVQ4jd2TMYoCMRiFv5HBwnJBsFqEiGxtISps6RGmFD2CZRr7aQSPIFjmCGsnrFYeQJjGytJKRERsfp2QmahY+iDk5c97L/wJCchBFCclYAD8SmkBTI1WB1cb5Ji/gT+g7mxtgK7RausNiOIEYAm0pHSWOZR5BbSNVndPwTmlaZnnQFnGXGot0XgDfiw+NlrtjVZ7YOzRZAJCix893NZkAi4eYejRpJcYxckQ6AENKf0DO+EVoCN8DcyMVhM3eQR8WesO+WgAVWDituC28wiFDHkXHxBgv0IfKL7oO+UF1Ei/7zMsbuQKTFoqpb8KS2AAAAAASUVORK5CYII=',
                'navbarSection' => $navbarSection,
            ]
        );
    }

    /**
     * @param callable(\OmegaUp\Request):array{smartyProperties: array<string, mixed>, template: string, inContest?: bool, navbarSection?: string} $callback
     */
    public static function render(callable $callback): void {
        $smarty = self::getSmartyInstance();
        try {
            $response = $callback(new Request($_REQUEST));
            $smartyProperties = $response['smartyProperties'];
            $template = $response['template'];
            $inContest = $response['inContest'] ?? false;
            $navbarSection = $response['navbarSection'] ?? '';
        } catch (\Exception $e) {
            \OmegaUp\ApiCaller::handleException($e);
        }

        \OmegaUp\UITools::assignSmartyNavbarHeader(
            $smarty,
            $inContest,
            $navbarSection
        );

        /** @var mixed $value */
        foreach ($smartyProperties as $key => $value) {
            $smarty->assign($key, $value);
        }

        $smarty->display(
            sprintf(
                '%s/templates/%s',
                strval(OMEGAUP_ROOT),
                $template
            )
        );
    }
}
