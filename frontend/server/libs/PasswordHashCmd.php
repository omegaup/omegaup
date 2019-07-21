<?php

require_once(__DIR__ . '/SecurityTools.php');
ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . __DIR__ . '/../');

$password = SecurityTools::randomString(8);
echo "$password\n";
echo SecurityTools::hashString($password) . "\n";
