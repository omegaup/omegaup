<?php

require_once('../../server/bootstrap_smarty.php');

try {
    $response = \OmegaUp\Controllers\User::apiProfile(new \OmegaUp\Request([
        'username' => array_key_exists('username', $_REQUEST) ? $_REQUEST['username'] : null,
    ]));
    $response['userinfo']['graduation_date'] = empty($response['userinfo']['graduation_date']) ?
            null : gmdate('Y-m-d', $response['userinfo']['graduation_date']);
    $smarty->assign('profile', $response);
} catch (\OmegaUp\Exceptions\ApiException $e) {
    $smarty->assign('STATUS_ERROR', $e->getErrorMessage());
}

$smarty->assign('admin', true);
$smarty->assign('practice', false);

$smarty->display('../../templates/interviews.results.tpl');
