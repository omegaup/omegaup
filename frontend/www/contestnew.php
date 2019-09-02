<?php

require_once('../server/bootstrap_smarty.php');
$smarty->assign('LANGUAGES', array_keys(\OmegaUp\Controllers\Run::SUPPORTED_LANGUAGES));
$smarty->display('../templates/contest.new.tpl');
