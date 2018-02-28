<?php

require_once('../../server/bootstrap.php');

UITools::redirectToLoginIfNotLoggedIn();

$is_suport_member = Authorization::isSupportTeamMember($session['user']->user_id);

if (!$is_suport_member) {
    header('Location: /');
    die();
}

$payload = [];

$smarty->assign('payload', $payload);

$smarty->display('../templates/admin.support.tpl');
