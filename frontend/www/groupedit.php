<?php

require_once('../server/bootstrap.php');

if (!$experiments->isEnabled(Experiments::IDENTITIES)) {
    header('HTTP/1.1 404 Not Found');
    die();
}

$r = new Request($_REQUEST);
$session = SessionController::apiCurrentSession($r)['session'];
if (is_null($session['identity'])) {
    header('HTTP/1.1 404 Not Found');
    die();
}

$is_organizer = Authorization::canCreateGroupIdentities($session['identity']->identity_id);
$smarty->assign('IS_UPDATE', 1);
$smarty->assign('IS_ORGANIZER', $is_organizer);
$smarty->display('../templates/group.edit.tpl');
