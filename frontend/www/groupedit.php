<?php

require_once('../server/bootstrap_smarty.php');

$r = new Request($_REQUEST);
$session = SessionController::apiCurrentSession($r)['session'];
if (is_null($session['identity'])) {
    header('HTTP/1.1 404 Not Found');
    die();
}

$is_organizer = $experiments->isEnabled(Experiments::IDENTITIES) &&
    Authorization::canCreateGroupIdentities($session['identity']->identity_id);
$smarty->assign('IS_ORGANIZER', $is_organizer);
$smarty->assign('payload', [
    'countries' => CountriesDAO::getAll(null, null, 'name'),
]);
$smarty->display('../templates/group.edit.tpl');
