<?php

require_once('../server/bootstrap.php');
$smarty->assign('LANGUAGES', RunController::$kSupportedLanguages);
$smarty->assign('IS_UPDATE', 1);
$smarty->display('../templates/contest.edit.tpl');
