<?php

// Required so that the facebook SDK does not explode.
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/';

require_once(__DIR__.'/../bootstrap.php');

if (count($argv) < 2) {
    echo "Usage: ${argv[0]} <experiment-name>\n";
    die(1);
}

$experiments = new Experiments(array());
echo $experiments->getExperimentHash($argv[1]) . "\n";
