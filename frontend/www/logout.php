<?php

require_once('../server/bootstrap.php');

$c_Session = new SessionController;

if ($c_Session->CurrentSessionAvailable()) {
    $c_Session->UnRegisterSession();
}

if (isset($_REQUEST['redirect'])) {
    die(header('Location: ' . $_REQUEST['redirect']));
} else {
    die(header('Location: /login/?logout'));
}
