<?php
require_once('../../server/bootstrap_smarty.php');
$smarty->assign('payload', []);
$smarty->display('../../templates/arena.course.admin.tpl');
