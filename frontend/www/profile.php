<?php

require_once('../server/bootstrap.php');

UITools::setProfile($smarty);

// Fetch contests
try {
    $myContestsListPayload  = ContestController::apiListParticipating(new Request([]));
    $smarty->assign('myContestsListPayload', $myContestsListPayload);
} catch (Exception $e) {
    // Oh, well...
}

$smarty->display('../templates/profile.tpl');
