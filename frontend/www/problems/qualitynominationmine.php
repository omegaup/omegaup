<?php

require_once(dirname(__DIR__, 2) . '/server/bootstrap_smarty.php');
\OmegaUp\UITools::redirectToLoginIfNotLoggedIn();

try {
    $payload = [
    'nominations' => \OmegaUp\Controllers\QualityNomination::apiMyList(new \OmegaUp\Request([]))['nominations'],
    'currentUser' => $session['user']->username,
    'myView' => true,
    ];
    $smarty->assign('payload', $payload);
    $smarty->display(OMEGAUP_ROOT . '/templates/quality.nomination.list.tpl');
} catch (\OmegaUp\Exceptions\ApiException $e) {
    Logger::getLogger('qualitynomination')->error('APIException ' . $e);
    header('HTTP/1.1 404 Not Found');
    die(file_get_contents('404.html'));
}
