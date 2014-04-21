<?php
require_once('../../server/bootstrap.php');
$smarty->assign('jsfile', '/ux/contest.js');
$smarty->assign('admin', false);
$smarty->assign('practice', false);
$smarty->display('../../templates/arena.contest.tpl');
