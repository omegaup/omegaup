<?php

	require_once("../server/bootstrap.php");

	$smarty->display('../templates/contest.tpl');

    // If the user have private material (contests/problems), an alert is issued
    // suggesting to contribute to the community by releasing the material to
    // the public. This flag ensures that this alert is shown only once per
    // session, the first time the user visits the "My contests" page.
    if (!isset($_SESSION['contributeAlert'])) {
        $_SESSION['contributeAlert'] = 1;
    }
