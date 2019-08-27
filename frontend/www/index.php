<?php
require_once('../server/bootstrap_smarty.php');

if (OMEGAUP_LOCKDOWN) {
    header('Location: /arena/');
    die();
}

// Fetch ranks
try {
    $coderOfTheMonthResponse = UserController::apiCoderOfTheMonth(new \OmegaUp\Request());
    $smarty->assign('coderOfTheMonthData', $coderOfTheMonthResponse['userinfo']);

    $smartyProperties = SchoolController::getSchoolsRankForSmarty(
        /*$rowCount=*/ 5,
        /*$isIndex=*/true
    );
} catch (Exception $e) {
     ApiCaller::handleException($e);
}
foreach ($smartyProperties as $key => $value) {
    $smarty->assign($key, $value);
}

$smarty->display('../templates/index.tpl');
