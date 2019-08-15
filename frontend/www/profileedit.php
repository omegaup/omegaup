<?php

require_once('../server/bootstrap_smarty.php');

UITools::redirectToLoginIfNotLoggedIn();
UITools::setProfile($smarty);

$ses = SessionController::apiCurrentSession()['session'];

$smarty->assign('PROGRAMMING_LANGUAGES', RunController::$kSupportedLanguages);
$smarty->assign('COUNTRIES', CountriesDAO::getAll(null, 100, 'name'));
if (is_null($ses['user']->password)) {
    $smarty->display('../templates/user.basicedit.tpl');
} else {
    $smarty->display('../templates/user.edit.tpl');
}
