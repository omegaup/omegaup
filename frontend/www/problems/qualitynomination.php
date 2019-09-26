<?php

require_once(dirname(__DIR__, 2) . '/server/bootstrap_smarty.php');
$qualitynomination_id = isset($_GET['qualitynomination_id']) ? $_GET['qualitynomination_id'] : null;

try {
    if ($qualitynomination_id != null) {
        $payload = \OmegaUp\Controllers\QualityNomination::apiDetails(new \OmegaUp\Request([
        'qualitynomination_id' => $qualitynomination_id
        ]));
        $template = OMEGAUP_ROOT . '/templates/quality.nomination.details.tpl';
    } else {
        $payload = [
        'nominations' => \OmegaUp\Controllers\QualityNomination::apiList(new \OmegaUp\Request([]))['nominations'],
        'myView' => false,
        ];
        if (!is_null($session['identity'])) {
            $payload['currentUser'] = $session['user']->username;
        }
        $template = OMEGAUP_ROOT . '/templates/quality.nomination.list.tpl';
    }
    $smarty->assign('payload', $payload);
    $smarty->display($template);
} catch (\OmegaUp\Exceptions\ApiException $e) {
    Logger::getLogger('qualitynomination')->error('APIException ' . $e);
    header('HTTP/1.1 404 Not Found');
    die();
}
