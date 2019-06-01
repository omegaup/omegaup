<?php
require_once('../server/bootstrap_smarty.php');

$smarty->assign('IS_UPDATE', 1);
$smarty->display('../templates/course.edit.tpl');
