<?php

require_once('../server/bootstrap.php');

UITools::redirectToLoginIfNotLoggedIn();
UITools::setProfile($smarty);

$ses = SessionController::apiCurrentSession()['session'];

// When user does not have password, it assumes is a social-login user
$smarty->assign('IS_SOCIAL_LOGIN', is_null($ses['user']->password));

$smarty->assign('PROGRAMMING_LANGUAGES', RunController::$kSupportedLanguages);
$smarty->assign('COUNTRIES', CountriesDAO::getAll());
$smarty->display('../templates/user.edit.tpl');
