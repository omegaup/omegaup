<?php

require_once(dirname(__DIR__, 2) . '/server/bootstrap_smarty.php');

$smarty->display(OMEGAUP_ROOT . '/templates/problem.stats.tpl');
