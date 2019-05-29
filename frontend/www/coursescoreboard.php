<?php
require_once('../server/bootstrap_smarty.php');
$smarty->assign('titleClassName', 'course-title');
$smarty->display('../templates/arena.scoreboard.tpl');
