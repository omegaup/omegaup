<?php

require_once('../server/bootstrap_smarty.php');

$r = new \OmegaUp\Request($_REQUEST);
$session = \OmegaUp\Controllers\Session::apiCurrentSession($r)['session'];
if (is_null($session['identity'])) {
    header('HTTP/1.1 404 Not Found');
    die();
}

$isOrganizer = \OmegaUp\Experiments::getInstance()->isEnabled(\OmegaUp\Experiments::IDENTITIES) &&
    \OmegaUp\Authorization::canCreateGroupIdentities($session['identity']);
$smarty->assign('IS_ORGANIZER', $isOrganizer);
$smarty->assign('payload', [
    'countries' => \OmegaUp\DAO\Countries::getAll(null, 100, 'name'),
]);
$smarty->display('../templates/group.edit.tpl');
