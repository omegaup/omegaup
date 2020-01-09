<?php

require_once('../server/bootstrap_smarty.php');

try {
    \OmegaUp\Controllers\User::apiVerifyEmail(new \OmegaUp\Request($_REQUEST));

    if (!empty($_REQUEST['redirecttointerview'])) {
        /** @psalm-suppress MixedArrayAccess $_REQUEST is okay. */
        header(
            'Location: /login/?redirect=/interview/' .
            urlencode(strval($_REQUEST['redirecttointerview'])) .
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
