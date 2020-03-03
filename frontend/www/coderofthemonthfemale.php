<?php

require_once('../server/bootstrap_smarty.php');

[
    'identity' => $identity,
] = \OmegaUp\Controllers\Session::getCurrentSession();

try {
    $_REQUEST['category']='female';
    $smartyProperties = \OmegaUp\Controllers\User::getCoderOfTheMonthDetailsForSmarty(
        new \OmegaUp\Request($_REQUEST),
        $identity
    );
} catch (Exception  $e) {
    \OmegaUp\ApiCaller::handleException($e);
}

foreach ($smartyProperties as $key => $value) {
    $smarty->assign($key, $value);
}

$smarty->display('../templates/codersofthemonthfemale.tpl');