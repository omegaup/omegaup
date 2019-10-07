<?php
require_once(dirname(__DIR__, 2) . '/server/bootstrap_smarty.php');

try {
    /** @var array{LANGUAGES: array<int, string>, IS_UPDATE?: int} */
    $result = \OmegaUp\Controllers\Contest::getContestNewDetailsForSmarty(
        new \OmegaUp\Request($_REQUEST)
    );
} catch (Exception $e) {
    \OmegaUp\ApiCaller::handleException($e);
}

foreach ($result as $key => $value) {
    $smarty->assign($key, $value);
}

$constant = 'constant';
$smarty->display("{$constant('OMEGAUP_ROOT')}/templates/contest.new.tpl");
