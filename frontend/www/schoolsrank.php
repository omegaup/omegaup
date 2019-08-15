<?php
require_once('../server/bootstrap_smarty.php');

try {
    $smartyProperties = SchoolController::getSchoolsRankForSmarty(
        /*$rowCount=*/ 100,
        /*$isIndex=*/false
    );
} catch (Exception $e) {
    ApiCaller::handleException($e);
}

foreach ($smartyProperties as $key => $value) {
    $smarty->assign($key, $value);
}

$smarty->display('../templates/rank.schools.tpl');
