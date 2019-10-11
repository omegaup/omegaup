<?php
require_once(dirname(__DIR__, 2) . '/server/bootstrap_smarty.php');
\OmegaUp\UITools::redirectToLoginIfNotLoggedIn();
$smarty->display(sprintf('%s/templates/course.list.tpl', OMEGAUP_ROOT));
