<?php
require_once(dirname(__DIR__, 2) . '/server/bootstrap_smarty.php');
$smarty->assign('titleClassName', 'course-title');
$smarty->display(sprintf('%s/templates/arena.scoreboard.tpl', OMEGAUP_ROOT));
