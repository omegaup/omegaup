<?php
require_once(dirname(__DIR__, 2) . '/server/bootstrap_smarty.php');

try {
    /** @var array{payload: array{groups: array<array-key, array{alias: string, create_time: int, description: string, name: string}>}}*/
    $result = \OmegaUp\Controllers\Group::getGroupListForSmarty(
        new \OmegaUp\Request($_REQUEST)
    );
} catch (Exception $e) {
    \OmegaUp\ApiCaller::handleException($e);
}

foreach ($result as $key => $value) {
    $smarty->assign($key, $value);
}

$constant = 'constant';
$smarty->display("{$constant('OMEGAUP_ROOT')}/templates/group.list.tpl");
