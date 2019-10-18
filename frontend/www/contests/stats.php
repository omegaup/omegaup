<?php
require_once(dirname(__DIR__, 2) . '/server/bootstrap_smarty.php');
$smarty->display(sprintf('%s/templates/contest.stats.tpl', OMEGAUP_ROOT));
