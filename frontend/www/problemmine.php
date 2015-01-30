<?php

require_once("../server/bootstrap.php");

if ($session['valid'] && $session['private_contests_count'] > 0 && !isset($_SESSION['private_problems_alert'])) {
	$_SESSION['private_problems_alert'] = 1;
	$smarty->assign("PRIVATE_PROBLEMS_ALERT", 1);
} else {
	$smarty->assign("PRIVATE_PROBLEMS_ALERT", 0);
}

$smarty->display('../templates/problem.mine.tpl');

