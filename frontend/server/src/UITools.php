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
        if (!is_null($identity)) {
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
                '<img src="https://secure.gravatar.com/avatar/' . md5(
                    $email
                ) . '?s=92">'
            );
            $smarty->assign(
                'CURRENT_USER_GRAVATAR_URL_16',
                '<img src="https://secure.gravatar.com/avatar/' . md5(
                    $email
                ) . '?s=16">'
            );
            $smarty->assign(
                'CURRENT_USER_GRAVATAR_URL_32',
                '<img src="https://secure.gravatar.com/avatar/' . md5(
                    $email
                ) . '?s=32">'
            );
            $smarty->assign(
                'CURRENT_USER_GRAVATAR_URL_51',
                '<img src="https://secure.gravatar.com/avatar/' . md5(
                    $email
                ) . '?s=51">'
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
                '<img src="/media/avatar_92.png">'
            );
            $smarty->assign(
                'CURRENT_USER_GRAVATAR_URL_16',
                '<img src="/media/avatar_16.png">'
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

        $smarty->assign(
            'ENABLED_EXPERIMENTS',
            \OmegaUp\Experiments::getInstance()->getEnabledExperiments()
        );
        self::$smarty = $smarty;
        return $smarty;
    }
}
