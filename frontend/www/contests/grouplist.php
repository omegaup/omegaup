<?php
require_once(dirname(__DIR__, 2) . '/server/bootstrap_smarty.php');

try {
    $result = \OmegaUp\Controllers\Group::getGroupListForSmarty(
        new \OmegaUp\Request($_REQUEST)
    );
} catch (Exception $e) {
    \OmegaUp\ApiCaller::handleException($e);
}

foreach ($result as $key => $value) {
    $smarty->assign($key, $value);
}

$smarty->display(sprintf('%s/templates/group.list.tpl', OMEGAUP_ROOT));
