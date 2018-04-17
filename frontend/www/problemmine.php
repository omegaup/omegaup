<?php

require_once('../server/bootstrap.php');

$private_problems_alert = 0;
$is_admin = false;

if ($session['valid']) {
    if (!isset($_SESSION['private_problems_alert']) &&
        ProblemsDAO::getPrivateCount($session['user']) > 0) {
        $_SESSION['private_problems_alert'] = 1;
        $private_problems_alert = 1;
    }
    $is_admin = Authorization::isSystemAdmin($session['identity']->identity_id);
}

$smarty->assign('PRIVATE_PROBLEMS_ALERT', $private_problems_alert);
$smarty->assign('IS_SYSADMIN', $is_admin);

$smarty->display('../templates/problem.mine.tpl');
