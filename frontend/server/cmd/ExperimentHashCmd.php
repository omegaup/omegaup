<?php

require_once(__DIR__ . '/../bootstrap.php');

if (count($argv) < 2) {
    echo "Usage: ${argv[0]} <experiment-name>\n";
    die(1);
}

$experiments = new \OmegaUp\Experiments(null, null);
echo $experiments->getExperimentHash($argv[1]) . "\n";
