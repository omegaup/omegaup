<?php
require_once(dirname(__DIR__, 2) . '/server/bootstrap_smarty.php');
$constant = 'constant';
$smarty->display("{$constant('OMEGAUP_ROOT')}/templates/contest.activity.tpl");
