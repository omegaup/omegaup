<?php
require_once('../server/bootstrap_smarty.php');
\OmegaUp\UITools::redirectToLoginIfNotLoggedIn();

$smarty->display('../templates/course.list.tpl');
