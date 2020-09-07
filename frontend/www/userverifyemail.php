<?php

require_once('../server/bootstrap_smarty.php');

try {
    $r = new \OmegaUp\Request($_REQUEST);
    \OmegaUp\Controllers\User::apiVerifyEmail($r);

    $redirectToInterview = $r->ensureOptionalString('redirecttointerview');
    if (!empty($redirectToInterview)) {
        header(
            'Location: /login/?redirect=/interview/' .
            urlencode($redirectToInterview) .
            '/arena/'
        );
    } else {
        header('Location: /login/');
    }
    die();
} catch (\OmegaUp\Exceptions\ApiException $e) {
    $smarty->assign('STATUS_ERROR', $e->getErrorMessage());
    $smarty->display('../templates/empty.tpl');
}
