<?php
require_once('../../server/bootstrap.php');
$smarty->assign('jsfile', '/ux/admin.js');
$smarty->assign('admin', true);
$smarty->assign('practice', false);
$smarty->display('../../templates/arena.contest.tpl');
