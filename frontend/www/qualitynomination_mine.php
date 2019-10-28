<?php

require_once('../server/bootstrap_smarty.php');

$identity = \OmegaUp\Controllers\Session::getCurrentSession()['identity'];
if (is_null($identity)) {
    \OmegaUp\UITools::redirectToLoginIfNotLoggedIn();
    die();
}

try {
    $payload = [
    'nominations' => \OmegaUp\Controllers\QualityNomination::apiMyList(
        new \OmegaUp\Request(
            []
        )
    )['nominations'],
    'currentUser' => $identity->username,
    'myView' => true,
    ];
    $smarty->assign('payload', $payload);
    $smarty->display('../templates/quality.nomination.list.tpl');
} catch (\OmegaUp\Exceptions\ApiException $e) {
    Logger::getLogger('qualitynomination')->error('APIException ' . $e);
    header('HTTP/1.1 404 Not Found');
    die(file_get_contents('404.html'));
}
