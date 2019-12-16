<?php
require_once('../../server/bootstrap_smarty.php');
$smartyProperties = [];
try {
    $smartyProperties = \OmegaUp\Controllers\Problem::getProblemDetailsForSmarty(
        new \OmegaUp\Request($_REQUEST)
    );
} catch (Exception $e) {
    \OmegaUp\ApiCaller::handleException($e);
}

foreach ($smartyProperties as $key => $value) {
    $smarty->assign($key, $value);
}

$smarty->display('../../templates/arena.problem.tpl');
