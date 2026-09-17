<?php

require_once(dirname(__DIR__) . '/bootstrap.php');

$dirname = dirname(__DIR__, 2) . '/www/js/dist';
$suffix = '.deps.json';
$loader = new \OmegaUp\Template\Loader();
$twigOptions = [
    'cache' => TEMPLATE_CACHE_DIR,
];
/** @psalm-suppress TypeDoesNotContainType this can change depending on environment */
if (
    defined('OMEGAUP_ENVIRONMENT') &&
    OMEGAUP_ENVIRONMENT === 'development'
) {
    $twigOptions['debug'] = true;
}
$twig = new \Twig\Environment($loader, $twigOptions);
$twig->addTokenParser(new \OmegaUp\Template\EntrypointParser());
$twig->addTokenParser(new \OmegaUp\Template\VersionHashParser());
$twig->addTokenParser(new \OmegaUp\Template\JsIncludeParser());
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
