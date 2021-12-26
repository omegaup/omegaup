<?php

require_once(dirname(__DIR__) . '/bootstrap.php');

$dirname = dirname(__DIR__, 2) . '/www/js/dist';
$suffix = '.deps.json';
['twig' => $twig] = \OmegaUp\UITools::getTwigInstance();
if ($handle = opendir($dirname)) {
    while (false !== ($entry = readdir($handle))) {
        if ($entry == '.' || $entry == '..') {
            continue;
        }
        if (!str_ends_with($entry, $suffix)) {
            continue;
        }
        $twig->load(substr($entry, 0, strlen($entry) - strlen($suffix)));
    }
    closedir($handle);
}
$twig->load('template.tpl');
