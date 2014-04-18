<?php
require_once('../../server/bootstrap.php');
$smarty->assign('jsfile', '/ux/admin.js');
$smarty->assign('admin', true);
$smarty->display('../../templates/arena.contest.tpl');
