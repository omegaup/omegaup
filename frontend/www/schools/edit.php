<?php
require_once(dirname(__DIR__, 2) . '/server/bootstrap_smarty.php');
$smarty->assign('IS_UPDATE', 1);
$constant = 'constant';
$smarty->display("{$constant('OMEGAUP_ROOT')}/templates/course.edit.tpl");
