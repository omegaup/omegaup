<?php
require_once('../../server/bootstrap_smarty.php');
$smarty->assign('payload', []);
$smarty->display('../../templates/arena.contest.admin.tpl');
