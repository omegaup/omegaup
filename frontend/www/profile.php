<?php

require_once('../server/bootstrap.php');

UITools::setProfile($smarty);

// Fetch contests
try {
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

$smarty->display('../templates/profile.tpl');
