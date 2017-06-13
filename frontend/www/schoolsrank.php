<?php
require_once('../server/bootstrap.php');

// Fetch ranking
try {
    $schoolRankPayload = SchoolController::apiRank(new Request(['rowcount' => 100]));
    $smarty->assign('schoolRankPayload', $schoolRankPayload['rank']);
} catch (Exception $e) {
    // Oh, well...
}

$smarty->display('../templates/rank.schools.tpl');
