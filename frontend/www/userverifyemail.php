<?php

require_once('../server/bootstrap_smarty.php');

try {
    $r = new \OmegaUp\Request($_REQUEST);
    \OmegaUp\Controllers\User::apiVerifyEmail($r);

    header('Location: /login/');
    die();
} catch (\OmegaUp\Exceptions\ApiException $e) {
    $smarty->assign('STATUS_ERROR', $e->getErrorMessage());
    $smarty->display('../templates/empty.tpl');
}
