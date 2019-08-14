<?php
require_once('../server/bootstrap_smarty.php');

if (OMEGAUP_LOCKDOWN) {
    header('Location: /arena/');
    die();
}

// Fetch ranks
try {
    $coderOfTheMonthResponse = UserController::apiCoderOfTheMonth(new Request());
    $smarty->assign('coderOfTheMonthData', $coderOfTheMonthResponse['userinfo']);

    $smartyProperties = SchoolController::getSchoolsRankForSmarty(
        new Request(['rowcount' => 5, 'is_index' => true])
    );
} catch (Exception $e) {
    // Oh, well...
     ApiCaller::handleException($e);
}
foreach ($smartyProperties as $key => $value) {
    $smarty->assign($key, $value);
}

$smarty->display('../templates/index.tpl');
