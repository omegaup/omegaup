<?php

require_once('../server/bootstrap.php');

UITools::redirectToLoginIfNotLoggedIn();
UITools::setProfile($smarty);

$ses = SessionController::apiCurrentSession()['session'];

$payload = [
    'email' => $ses['email'],
];
$smarty->assign('payload', $payload);

$smarty->display('../templates/user.email.edit.tpl');
