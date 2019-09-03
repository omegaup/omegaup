<?php

require_once('../server/bootstrap_smarty.php');

$c_Session = new \OmegaUp\Controllers\Session();

if ($c_Session->currentSessionAvailable()) {
    $c_Session->UnRegisterSession();
}

if (isset($_REQUEST['redirect'])) {
    die(header('Location: ' . $_REQUEST['redirect']));
} else {
    die(header('Location: /login'));
}
