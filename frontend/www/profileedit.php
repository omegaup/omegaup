<?php

require_once('../server/bootstrap_smarty.php');

\OmegaUp\UITools::redirectToLoginIfNotLoggedIn();
try {
    $response = UserController::apiProfile(new \OmegaUp\Request([
        'username' => array_key_exists('username', $_REQUEST) ? $_REQUEST['username'] : null,
    ]));
    $response['userinfo']['graduation_date'] = empty($response['userinfo']['graduation_date']) ?
            null : gmdate('Y-m-d', $response['userinfo']['graduation_date']);
    $smarty->assign('profile', $response);
} catch (\OmegaUp\ApiException $e) {
    $smarty->assign('STATUS_ERROR', $e->getErrorMessage());
}

$ses = SessionController::apiCurrentSession()['session'];

$smarty->assign('PROGRAMMING_LANGUAGES', RunController::$kSupportedLanguages);
$smarty->assign('COUNTRIES', CountriesDAO::getAll(null, 100, 'name'));
if (is_null($ses['user']->password)) {
    $smarty->display('../templates/user.basicedit.tpl');
} else {
    $smarty->display('../templates/user.edit.tpl');
}
