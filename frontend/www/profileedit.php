<?php

require_once('../server/bootstrap_smarty.php');

\OmegaUp\UITools::redirectToLoginIfNotLoggedIn();
try {
    $response = \OmegaUp\Controllers\User::apiProfile(new \OmegaUp\Request([
        'username' => array_key_exists(
            'username',
            $_REQUEST
        ) ? $_REQUEST['username'] : null,
    ]));
    $response['userinfo']['graduation_date'] = empty(
        $response['userinfo']['graduation_date']
    ) ?
            null : gmdate('Y-m-d', $response['userinfo']['graduation_date']);
    $smarty->assign('profile', $response);
} catch (\OmegaUp\Exceptions\ApiException $e) {
    $smarty->assign('STATUS_ERROR', $e->getErrorMessage());
}

/** @var array{valid: bool, email: string|null, user: \OmegaUp\DAO\VO\Users|null, identity: \OmegaUp\DAO\VO\Identities|null, auth_token: string|null, is_admin: bool} */
[
    'identity' => $_identity,
] = \OmegaUp\Controllers\Session::getCurrentSession();

$smarty->assign(
    'PROGRAMMING_LANGUAGES',
    \OmegaUp\Controllers\Run::SUPPORTED_LANGUAGES
);
$smarty->assign('COUNTRIES', \OmegaUp\DAO\Countries::getAll(null, 100, 'name'));
if (is_null($_identity) || is_null($_identity->password)) {
    $smarty->display('../templates/user.basicedit.tpl');
} else {
    $smarty->display('../templates/user.edit.tpl');
}
