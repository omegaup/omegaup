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

    $schoolRankPayload = SchoolController::apiRank(new Request(['rowcount' => 5]));
    $smarty->assign('schoolRankPayload', $schoolRankPayload['rank']);

    $carouselPayload = json_decode(file_get_contents('carousel.json'));
    $smarty->assign('carouselPayload', $carouselPayload);
} catch (Exception $e) {
    // Oh, well...
}

$smarty->display('../templates/index.tpl');
