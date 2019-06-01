<?php

require_once('../server/bootstrap_smarty.php');

$r = new Request($_REQUEST);
$session = SessionController::apiCurrentSession($r)['session'];
if (is_null($session['identity']) || ($experiments->isEnabled(Experiments::IDENTITIES) &&
    Authorization::canCreateGroupIdentities($session['identity']->identity_id))) {
    header('HTTP/1.1 404 Not Found');
    die();
}

$profile = UserController::apiProfile($r);

$smarty->assign('payload', [
    'countries' => CountriesDAO::getAll(null, null, 'name'),
    'identity' => $profile['userinfo'],
]);
$smarty->display('../templates/group.edit.identity.tpl');
