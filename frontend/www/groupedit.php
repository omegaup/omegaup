<?php

require_once('../server/bootstrap_smarty.php');

$r = new \OmegaUp\Request($_REQUEST);
$session = SessionController::apiCurrentSession($r)['session'];
if (is_null($session['identity'])) {
    header('HTTP/1.1 404 Not Found');
    die();
}

$is_organizer = $experiments->isEnabled(\OmegaUp\Experiments::IDENTITIES) &&
    Authorization::canCreateGroupIdentities($session['identity']);
$smarty->assign('IS_ORGANIZER', $is_organizer);
$smarty->assign('payload', [
    'countries' => CountriesDAO::getAll(null, 100, 'name'),
]);
$smarty->display('../templates/group.edit.tpl');
