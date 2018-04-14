<?php

require_once('../server/bootstrap.php');
$smarty->assign('LANGUAGES', array_keys(RunController::$kSupportedLanguages));
$smarty->display('../templates/contest.new.tpl');
