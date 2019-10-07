<?php
require_once(dirname(__DIR__, 2) . '/server/bootstrap_smarty.php');

try {
    /** @var array{IS_ORGANIZER: bool, payload: array{countries: array<int, \OmegaUp\DAO\VO\Countries>}} */
    $result = \OmegaUp\Controllers\Group::getGroupEditDetailsForSmarty(
        new \OmegaUp\Request($_REQUEST)
    );
} catch (Exception $e) {
    \OmegaUp\ApiCaller::handleException($e);
}

foreach ($result as $key => $value) {
    $smarty->assign($key, $value);
}

$constant = 'constant';
$smarty->display("{$constant('OMEGAUP_ROOT')}/templates/group.edit.tpl");
