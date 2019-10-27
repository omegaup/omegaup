<?php

require_once __DIR__ . '/bootstrap.php';
require_once 'libs/third_party/smarty/libs/Smarty.class.php';

$smarty = new Smarty();
$smarty->setTemplateDir(__DIR__ . '/../templates/');

$smarty->assign('CURRENT_USER_IS_ADMIN', 0);
if (defined('SMARTY_CACHE_DIR')) {
    $smarty->setCacheDir(SMARTY_CACHE_DIR)->setCompileDir(SMARTY_CACHE_DIR);
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

/** @var array{valid: bool, email: string|null, user: \OmegaUp\DAO\VO\Users|null, identity: \OmegaUp\DAO\VO\Identities|null, auth_token: string|null, is_admin: bool} */
[
    'email' => $_email,
    'identity' => $_identity,
    'user' => $_user,
    'is_admin' => $_is_admin,
    'auth_token' => $_auth_token,
] = \OmegaUp\Controllers\Session::getCurrentSession();
if (!is_null($_identity)) {
    $smarty->assign('LOGGED_IN', '1');

    $smarty->assign(
        'CURRENT_USER_USERNAME',
        $_identity->username
    );
    $smarty->assign('CURRENT_USER_EMAIL', $_email);
    $smarty->assign(
        'CURRENT_USER_IS_EMAIL_VERIFIED',
        empty($_user) || $_user->verified
    );
    $smarty->assign('CURRENT_USER_IS_ADMIN', $_is_admin);
    $smarty->assign(
        'CURRENT_USER_IS_REVIEWER',
        \OmegaUp\Authorization::isQualityReviewer($_identity)
    );
    $smarty->assign('CURRENT_USER_AUTH_TOKEN', $_auth_token);
    $smarty->assign(
        'CURRENT_USER_GRAVATAR_URL_128',
        '<img src="https://secure.gravatar.com/avatar/' . md5(
            $_email
        ) . '?s=92">'
    );
    $smarty->assign(
        'CURRENT_USER_GRAVATAR_URL_16',
        '<img src="https://secure.gravatar.com/avatar/' . md5(
            $_email
        ) . '?s=16">'
    );
    $smarty->assign(
        'CURRENT_USER_GRAVATAR_URL_32',
        '<img src="https://secure.gravatar.com/avatar/' . md5(
            $_email
        ) . '?s=32">'
    );
    $smarty->assign(
        'CURRENT_USER_GRAVATAR_URL_51',
        '<img src="https://secure.gravatar.com/avatar/' . md5(
            $_email
        ) . '?s=51">'
    );

    $smarty->assign(
        'currentUserInfo',
        [
            'username' => $_identity->username,
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
    $_identity
) ? null : $_identity->username;
/** @var string */
$_lang = \OmegaUp\Controllers\Identity::getPreferredLanguage(
    $identityRequest
);
$smarty->configLoad(__DIR__ . "/../templates/{$_lang}.lang");
$smarty->addPluginsDir(__DIR__ . '/../smarty_plugins/');

$smarty->assign(
    'ENABLED_EXPERIMENTS',
    \OmegaUp\Experiments::getInstance()->getEnabledExperiments()
);
