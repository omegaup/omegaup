<?php

require_once('../../server/bootstrap.php');

UITools::redirectToLoginIfNotLoggedIn();

if (!Authorization::isSupportTeamMember($session['user']->user_id)) {
    header('HTTP/1.1 404 Not found');
    die();
}

$payload = [];

$smarty->assign('payload', $payload);

$smarty->display('../templates/admin.support.tpl');
