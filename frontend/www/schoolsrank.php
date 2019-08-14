<?php
require_once('../server/bootstrap_smarty.php');

try {
    $smartyProperties = SchoolController::getSchoolsRankForSmarty(
        new Request(['rowcount' => 100])
    );
} catch (Exception $e) {
    ApiCaller::handleException($e);
}

foreach ($smartyProperties as $key => $value) {
    $smarty->assign($key, $value);
}

$smarty->display('../templates/rank.schools.tpl');
