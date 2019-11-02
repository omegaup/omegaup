<?php

require_once('../server/bootstrap_smarty.php');

try {
    $smartyProperties = \OmegaUp\Controllers\Problem::getProblemsMineInfoForSmarty(
        new \OmegaUp\Request($_REQUEST)
    );
} catch (Exception $e) {
    \OmegaUp\ApiCaller::handleException($e);
}

foreach ($smartyProperties as $key => $value) {
    $smarty->assign($key, $value);
}
$smarty->display('../templates/problem.mine.tpl');
