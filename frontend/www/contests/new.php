<?php

require_once(dirname(__DIR__, 2) . '/server/bootstrap_smarty.php');
$smarty->assign('LANGUAGES', array_keys(\OmegaUp\Controllers\Run::SUPPORTED_LANGUAGES));
$smarty->display(OMEGAUP_ROOT . '/templates/contest.new.tpl');
