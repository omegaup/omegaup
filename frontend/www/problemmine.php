<?php

require_once("../server/bootstrap.php");

if ($session['valid'] && $session['private_contests_count'] > 0 && !isset($_SESSION['free_problems_alert'])) {
	$_SESSION['free_problems_alert'] = 1;
	$smarty->assign("FREE_PROBLEMS_ALERT", 1);
} else {
	$smarty->assign("FREE_PROBLEMS_ALERT", 0);
}

$smarty->display('../templates/myproblems.list.tpl');

