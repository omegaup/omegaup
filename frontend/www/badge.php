<?php

require_once('../server/bootstrap_smarty.php');
if (isset($_REQUEST['badge'])) {
    $badgeAlias = $_REQUEST['badge'];
    try {
        BadgeController::badgeExists($_REQUEST['badge']);
        $smarty->assign('badge', $_REQUEST['badge']);
    } catch (NotFoundException $e) {
        $smarty->assign('STATUS_ERROR', $e->getErrorMessage());
    }
    $smarty->display('../templates/badge.details.tpl');
}
