<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

$password = \OmegaUp\SecurityTools::randomString(8);
echo "{$password}\n";
echo \OmegaUp\SecurityTools::hashString($password) . "\n";
