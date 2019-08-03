<?php

require_once('../server/bootstrap_smarty.php');

try {
    $session = SessionController::apiCurrentSession(
        new Request($_REQUEST)
    )['session'];
    $smartyProperties = UserController::getCoderOfTheMonthDetailsForSmarty(
        new Request($_REQUEST),
        $session['identity']
    );
} catch (Exception  $e) {
    ApiCaller::handleException($e);
}

foreach ($smartyProperties as $key => $value) {
    $smarty->assign($key, $value);
}

$smarty->display('../templates/codersofthemonth.tpl');
