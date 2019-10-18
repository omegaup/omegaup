<?php
require_once(dirname(__DIR__, 2) . '/server/bootstrap_smarty.php');
$smarty->display(sprintf('%s/templates/group.new.tpl', OMEGAUP_ROOT));
