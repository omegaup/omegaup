<?php

require_once('../server/bootstrap.php');

UITools::redirectToLoginIfNotLoggedIn();
UITools::setProfile($smarty);

$currentSession = SessionController::apiCurrentSession()['session'];

$smarty->assign('payload', [
    'email' => $currentSession['email'],
]);

$smarty->display('../templates/user.email.edit.tpl');
