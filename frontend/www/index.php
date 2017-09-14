<?php

require_once('../server/bootstrap.php');

if (OMEGAUP_LOCKDOWN) {
    header('Location: /arena/');
    die();
}

// Fetch ranks and contests
try {
    $coderOfTheMonthResponse = UserController::apiCoderOfTheMonth(new Request());
    $smarty->assign('coderOfTheMonthData', $coderOfTheMonthResponse['userinfo']);

    $schoolRankPayload = SchoolController::apiRank(new Request(['rowcount' => 5]));
    $smarty->assign('schoolRankPayload', $schoolRankPayload['rank']);

    $myContestsListPayload  = ContestController::apiList(
        new Request([
            'active' => 'ACTIVE',
            'recommended' => 'NOT_RECOMMENDED',
            'page_size' => 1000
            ])
    );
        $smarty->assign('myContestsListPayload', $myContestsListPayload['results']);
} catch (Exception $e) {
    // Oh, well...
}

$smarty->display('../templates/index.tpl');
