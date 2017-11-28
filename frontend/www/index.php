<?php

require_once('../server/bootstrap.php');

if (OMEGAUP_LOCKDOWN) {
    header('Location: /arena/');
    die();
}

// Fetch ranks
try {
    $coderOfTheMonthResponse = UserController::apiCoderOfTheMonth(new Request());
    $smarty->assign('coderOfTheMonthData', $coderOfTheMonthResponse['userinfo']);

    $schoolRankPayload = SchoolController::apiRank(new Request(['rowcount' => 100]));
    // Show top 5 schools rank
    $smarty->assign('schoolRankPayload', ['rowCount' => 5, 'rank' => $schoolRankPayload['rank']]);
} catch (Exception $e) {
    // Oh, well...
}

$smarty->display('../templates/index.tpl');
