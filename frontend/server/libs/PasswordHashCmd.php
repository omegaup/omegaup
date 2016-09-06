<?php

require_once(__DIR__ . '/SecurityTools.php');

$password = SecurityTools::randomString(8);
echo "$password\n";
echo SecurityTools::hashString($password) . "\n";
