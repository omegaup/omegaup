<?php

require_once('../../server/bootstrap_smarty.php');

UITools::redirectToLoginIfNotLoggedIn();

if (!Authorization::isSupportTeamMember($session['identity'])) {
    header('HTTP/1.1 404 Not found');
    die();
}

$smarty->display('../templates/admin.support.tpl');
