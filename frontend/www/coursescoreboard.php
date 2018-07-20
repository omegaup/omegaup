<?php
require_once('../server/bootstrap.php');
$smarty->assign('titleClassName', 'course-title');
$smarty->display('../templates/arena.scoreboard.tpl');
