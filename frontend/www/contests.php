<?php

	require_once("../server/bootstrap.php");

	// If the user have private material (contests/problems), an alert is issued
	// suggesting to contribute to the community by releasing the material to
	// the public. This flag ensures that this alert is shown only once per
	// session, the first time the user visits the "My contests" page.
	if ($session['valid'] && $session['private_contests_count'] > 0 && !isset($_SESSION['private_contests_alert'])) {
		$_SESSION['private_contests_alert'] = 1;
		$smarty->assign("PRIVATE_CONTESTS_ALERT", 1);
	} else {
		$smarty->assign("PRIVATE_CONTESTS_ALERT", 0);
	}

	$smarty->display('../templates/contest.tpl');
