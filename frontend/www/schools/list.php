<?php
require_once(dirname(__DIR__, 2) . '/server/bootstrap_smarty.php');
\OmegaUp\UITools::redirectToLoginIfNotLoggedIn();
$constant = 'constant';
$smarty->display("{$constant('OMEGAUP_ROOT')}/templates/course.list.tpl");
