<?php

require_once('../server/bootstrap.php');
if (\OmegaUp\Controllers\Session::currentSessionAvailable()) {
    \OmegaUp\Controllers\Session::unregisterSession();
}

require_once('../server/bootstrap_smarty.php');
$smarty->display('../templates/logout.tpl');
