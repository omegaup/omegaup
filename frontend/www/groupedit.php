<?php

require_once('../server/bootstrap.php');

$r = new Request($_REQUEST);
$session = SessionController::apiCurrentSession($r)['session'];
$is_organizer = Authorization::canOrganizeIdentitiesGroup($session['identity']->identity_id);

$smarty->assign('IS_UPDATE', 1);
$smarty->assign('IS_ORGANIZER', $is_organizer);
$smarty->display('../templates/group.edit.tpl');
