<?php

require_once('../../server/bootstrap.php');

UITools::setProfile($smarty);

$smarty->assign('admin', true);
$smarty->assign('practice', false);

$smarty->display('../../templates/interviews.results.tpl');
