<?php

require_once('../server/bootstrap_smarty.php');

if (empty($_REQUEST['badge_alias'])) {
    header('HTTP/1.1 404 Not found');
    die();
}

try {
    Validators::validateBadgeExists($_REQUEST['badge_alias'], BadgeController::getAllBadges());
    $smarty->assign('badge_alias', $_REQUEST['badge_alias']);
} catch (NotFoundException $e) {
    $smarty->assign('STATUS_ERROR', $e->getErrorMessage());
}
$smarty->display('../templates/badge.details.tpl');
