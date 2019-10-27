<?php

require_once('../server/bootstrap_smarty.php');
$qualitynomination_id = isset(
    $_GET['qualitynomination_id']
) ? $_GET['qualitynomination_id'] : null;

[
    'identity' => $identity,
] = \OmegaUp\Controllers\Session::getCurrentSession();

try {
    if (!is_null($qualitynomination_id)) {
        $payload = \OmegaUp\Controllers\QualityNomination::apiDetails(new \OmegaUp\Request([
            'qualitynomination_id' => $qualitynomination_id,
        ]));
        $template = '../templates/quality.nomination.details.tpl';
    } else {
        $payload = [
        'nominations' => \OmegaUp\Controllers\QualityNomination::apiList(
            new \OmegaUp\Request(
                []
            )
        )['nominations'],
        'myView' => false,
        ];
        if (!is_null($identity)) {
            $payload['currentUser'] = $identity->username;
        }
        $template = '../templates/quality.nomination.list.tpl';
    }
    $smarty->assign('payload', $payload);
    $smarty->display($template);
} catch (\OmegaUp\Exceptions\ApiException $e) {
    Logger::getLogger('qualitynomination')->error('APIException ' . $e);
    header('HTTP/1.1 404 Not Found');
    die();
}
