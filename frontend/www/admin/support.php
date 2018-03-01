<?php

require_once('../../server/bootstrap.php');

UITools::redirectToLoginIfNotLoggedIn();

$is_suport_member = Authorization::isSupportTeamMember($session['user']->user_id);

if (!$is_suport_member) {
    header('HTTP/1.1 404 Not found');
    die();
}

$payload = [];

$smarty->assign('payload', $payload);

$smarty->display('../templates/admin.support.tpl');
