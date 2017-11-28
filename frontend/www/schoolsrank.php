<?php
require_once('../server/bootstrap.php');

// Fetch ranking
try {
    $schoolRankPayload = SchoolController::apiRank(new Request(['rowcount' => 100]));
    // Show top 100 schools rank
    $smarty->assign('schoolRankPayload', ['rowcount' => 2, 'rank' => $schoolRankPayload['rank']]);
} catch (Exception $e) {
    // Oh, well...
}

$smarty->display('../templates/rank.schools.tpl');
