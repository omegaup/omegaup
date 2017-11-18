<?php

require_once('../server/bootstrap.php');

UITools::redirectToLoginIfNotLoggedIn();
UITools::setProfile($smarty);

$ses = SessionController::apiCurrentSession()['session'];

if (is_null($ses['user']->password)) {
    $smarty->display('../templates/user.basicedit.tpl');
} else {
    $languages = RunController::$kSupportedLanguages;
    $countries = CountriesDAO::getAll();
    $smarty->assign('LANGUAGES', $languages);
    $smarty->assign('COUNTRIES', $countries);
    $smarty->display('../templates/user.edit.tpl');
}
