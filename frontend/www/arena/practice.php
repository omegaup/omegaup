<?php
require_once('../../server/bootstrap.php');
$smarty->assign('bodyid', 'practice');
$smarty->assign('jsfile', '/ux/contest.js');
$smarty->assign('admin', false);
$smarty->assign('practice', true);
$smarty->display('../../templates/arena.contest.tpl');
