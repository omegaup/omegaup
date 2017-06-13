<?php

require_once('../server/bootstrap.php');

try {
    $payload = [
    'nominations' => QualityNominationController::apiMyList(new Request([]))['nominations'],
    'currentUser' => $session['user']->username,
    'myView' => true,
    ];
    $smarty->assign('payload', $payload);
    $smarty->display('../templates/quality.nomination.list.tpl');
} catch (APIException $e) {
    Logger::getLogger('qualitynomination')->error('APIException ' . $e);
    header('HTTP/1.1 404 Not Found');
    die();
}
