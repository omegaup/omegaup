<?php
require_once(dirname(__DIR__, 2) . '/server/bootstrap_smarty.php');

try {
    $smartyProperties = \OmegaUp\Controllers\School::getSchoolsRankForSmarty(
        /*$rowCount=*/ 100,
        /*$isIndex=*/false
    );
} catch (Exception $e) {
    \OmegaUp\ApiCaller::handleException($e);
}

foreach ($smartyProperties as $key => $value) {
    $smarty->assign($key, $value);
}

$smarty->display(OMEGAUP_ROOT . '/templates/rank.schools.tpl');
