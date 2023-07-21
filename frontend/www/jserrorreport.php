<?php
require_once('../server/bootstrap.php');

if (
    !isset($_SERVER['REQUEST_METHOD'])
    || $_SERVER['REQUEST_METHOD'] != 'POST'
) {
    header('HTTP/1.1 400 Bad Request');
    die();
}

$log = \Monolog\Registry::omegaup()->withName('jserror');
$log->error(file_get_contents('php://input'));
die();
