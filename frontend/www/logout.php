<?php

require_once('../server/bootstrap_smarty.php');

if (\OmegaUp\Controllers\Session::currentSessionAvailable()) {
    \OmegaUp\Controllers\Session::unregisterSession();
}

if (isset($_REQUEST['redirect'])) {
    die(header('Location: ' . $_REQUEST['redirect']));
} else {
    die(header('Location: /login'));
}
