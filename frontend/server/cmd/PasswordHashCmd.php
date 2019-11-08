<?php

ini_set(
    'include_path',
    ini_get(
        'include_path'
    ) . PATH_SEPARATOR . __DIR__ . '/../'
);
require_once 'autoload.php';

$password = \OmegaUp\SecurityTools::randomString(8);
echo "{$password}\n";
echo \OmegaUp\SecurityTools::hashString($password) . "\n";
