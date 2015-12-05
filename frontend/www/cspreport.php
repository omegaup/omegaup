<?php
require_once( "../server/bootstrap.php" );

if (empty($_SERVER['CONTENT_TYPE']) || $_SERVER['CONTENT_TYPE'] != 'application/csp-report') {
	header('HTTP/1.1 400 Bad Request');
	die();
}

$log = Logger::getLogger("csp");
$log->error(file_get_contents('php://input'));
die();
