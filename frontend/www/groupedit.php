<?php

require_once('../server/bootstrap.php');

$r = new Request($_REQUEST);
$session = SessionController::apiCurrentSession($r)['session'];
if (is_null($session['identity'])) {
    header('HTTP/1.1 404 Not Found');
    die();
}
//
//$is_organizer = Authorization::canOrganizeIdentitiesGroup($session['identity']->identity_id);
$is_organizer = true;
$smarty->assign('IS_UPDATE', 1);
$smarty->assign('IS_ORGANIZER', $is_organizer);
$smarty->display('../templates/group.edit.tpl');
