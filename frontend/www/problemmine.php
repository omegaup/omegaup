<?php

require_once('../server/bootstrap.php');

$private_problems_alert = 0;

if ($session['valid'] && !isset($_SESSION['private_problems_alert'])) {
    if (ProblemsDAO::getPrivateCount($session['user']) > 0) {
        $_SESSION['private_problems_alert'] = 1;
        $private_problems_alert = 1;
    }
}

$smarty->assign('PRIVATE_PROBLEMS_ALERT', $private_problems_alert);

$smarty->display('../templates/problem.mine.tpl');
