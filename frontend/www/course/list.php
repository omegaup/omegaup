<?php
require_once(dirname(__DIR__, 2) . '/server/bootstrap_smarty.php');
\OmegaUp\UITools::redirectToLoginIfNotLoggedIn();
$smarty->display(OMEGAUP_ROOT . '/templates/course.list.tpl');
