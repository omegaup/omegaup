<?php
require_once(dirname(__DIR__, 2) . '/server/bootstrap_smarty.php');
$smarty->assign('titleClassName', 'course-title');
$smarty->display(OMEGAUP_ROOT . '/templates/arena.scoreboard.tpl');
