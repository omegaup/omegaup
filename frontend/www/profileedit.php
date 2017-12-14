<?php

require_once('../server/bootstrap.php');

UITools::redirectToLoginIfNotLoggedIn();
UITools::setProfile($smarty);

$ses = SessionController::apiCurrentSession()['session'];

if (is_null($ses['user']->password)) {
    $smarty->display('../templates/user.basicedit.tpl');
} else {
    $smarty->assign('PROGRAMMING_LANGUAGES', RunController::$kSupportedLanguages);
    $smarty->assign('COUNTRIES', CountriesDAO::getAll());
    $smarty->display('../templates/user.edit.tpl');
}
