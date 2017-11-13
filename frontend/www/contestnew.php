<?php

require_once('../server/bootstrap.php');
$smarty->assign('LANGUAGES', RunController::$kSupportedLanguages);
$smarty->display('../templates/contest.new.tpl');
