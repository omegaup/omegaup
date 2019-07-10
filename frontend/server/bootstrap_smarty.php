<?php

require_once __DIR__ . '/bootstrap.php';
require_once 'libs/third_party/smarty/libs/Smarty.class.php';
require_once 'libs/UITools.php';

$smarty = new Smarty();
$smarty->setTemplateDir(__DIR__ . '/../templates/');

if (!defined('IS_TEST') || IS_TEST !== true) {
    $smarty->assign('CURRENT_USER_IS_ADMIN', 0);
    if (defined('SMARTY_CACHE_DIR')) {
        $smarty->setCacheDir(SMARTY_CACHE_DIR)->setCompileDir(SMARTY_CACHE_DIR);
    }

    $smarty->assign('GOOGLECLIENTID', OMEGAUP_GOOGLE_CLIENTID);

    $smarty->assign('LOGGED_IN', '0');
    UITools::$IsLoggedIn = false;

    if (defined('OMEGAUP_GA_TRACK')  && OMEGAUP_GA_TRACK) {
        $smarty->assign('OMEGAUP_GA_TRACK', 1);
        $smarty->assign('OMEGAUP_GA_ID', OMEGAUP_GA_ID);
    } else {
        $smarty->assign('OMEGAUP_GA_TRACK', 0);
    }

    $identityRequest = new Request($_REQUEST);
    $session = SessionController::apiCurrentSession($identityRequest)['session'];
    if ($session['valid']) {
        $smarty->assign('LOGGED_IN', '1');
        UITools::$IsLoggedIn = true;

        $smarty->assign('CURRENT_USER_USERNAME', $session['identity']->username);
        $smarty->assign('CURRENT_USER_EMAIL', $session['email']);
        $smarty->assign('CURRENT_USER_IS_EMAIL_VERIFIED', empty($session['user']) || $session['user']->verified);
        $smarty->assign('CURRENT_USER_IS_ADMIN', $session['is_admin']);
        $smarty->assign('CURRENT_USER_IS_REVIEWER', Authorization::isQualityReviewer($session['identity']->identity_id));
        $smarty->assign('CURRENT_USER_AUTH_TOKEN', $session['auth_token']);
        $smarty->assign('CURRENT_USER_GRAVATAR_URL_128', '<img src="https://secure.gravatar.com/avatar/' . md5($session['email']) . '?s=92">');
        $smarty->assign('CURRENT_USER_GRAVATAR_URL_16', '<img src="https://secure.gravatar.com/avatar/' . md5($session['email']) . '?s=16">');
        $smarty->assign('CURRENT_USER_GRAVATAR_URL_32', '<img src="https://secure.gravatar.com/avatar/' . md5($session['email']) . '?s=32">');
        $smarty->assign('CURRENT_USER_GRAVATAR_URL_51', '<img src="https://secure.gravatar.com/avatar/' . md5($session['email']) . '?s=51">');

        $smarty->assign(
            'currentUserInfo',
            [
                'username' => $session['username'],
            ]
        );

        UITools::$IsAdmin = $session['is_admin'];
        $identityRequest['username'] = $session['username'];
    } else {
        $identityRequest['username'] = null;
        $smarty->assign('CURRENT_USER_GRAVATAR_URL_128', '<img src="/media/avatar_92.png">');
        $smarty->assign('CURRENT_USER_GRAVATAR_URL_16', '<img src="/media/avatar_16.png">');
    }

    $lang = IdentityController::getPreferredLanguage($identityRequest);

    if (defined('OMEGAUP_ENVIRONMENT') && OMEGAUP_ENVIRONMENT === 'development') {
        $smarty->force_compile = true;
    } else {
        $smarty->compile_check = false;
    }
} else {
    // During testing We need smarty to load strings from *.lang files
    $lang = 'pseudo';
    $session = ['valid' => false];
}

$smarty->configLoad(__DIR__ . '/../templates/'. $lang . '.lang');
$smarty->addPluginsDir(__DIR__ . '/../smarty_plugins/');

$smarty->assign('ENABLED_EXPERIMENTS', $experiments->getEnabledExperiments());
