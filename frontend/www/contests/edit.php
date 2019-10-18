<?php
require_once(dirname(__DIR__, 2) . '/server/bootstrap_smarty.php');

try {
    $result = \OmegaUp\Controllers\Contest::getContestNewDetailsForSmarty(
        new \OmegaUp\Request($_REQUEST),
        /*$isUpdate=*/true
    );
} catch (Exception $e) {
    \OmegaUp\ApiCaller::handleException($e);
}

foreach ($result as $key => $value) {
    $smarty->assign($key, $value);
}

$smarty->display(sprintf('%s/templates/contest.edit.tpl', OMEGAUP_ROOT));
