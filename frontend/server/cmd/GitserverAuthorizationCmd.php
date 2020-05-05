<?php

require_once(__DIR__ . '/../bootstrap.php');

if (count($argv) < 3) {
    echo "Usage: ${argv[0]} <problem> <username>\n";
    die(1);
}

echo \OmegaUp\SecurityTools::getGitserverauthorizationHeader(
    $argv[1],
    $argv[2]
) . "\n";
