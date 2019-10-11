<?php
require_once(dirname(__DIR__, 2) . '/server/bootstrap_smarty.php');
$smarty->assign('IS_UPDATE', 1);
$smarty->display(sprintf('%s/templates/course.edit.tpl', OMEGAUP_ROOT));
