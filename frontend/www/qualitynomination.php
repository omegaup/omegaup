<?php

require_once('../server/bootstrap.php');
$qualitynomination_id = isset($_GET['qualitynomination_id']) ? $_GET['qualitynomination_id'] : null;

try {
  if ($qualitynomination_id != null) {
    $payload = QualityNominationController::apiDetails(new Request([
      'qualitynomination_id' => $qualitynomination_id
    ]));
    $template = '../templates/quality.nomination.details.tpl';
  } else {
    $payload = [
      'nominations' => QualityNominationController::apiList(new Request([]))['nominations'],
      'currentUser' => $session['user']->username,
      'myView' => false,
    ];
    $template = '../templates/quality.nomination.list.tpl';
  }
  $smarty->assign('payload', $payload);
  $smarty->display($template);
} catch (APIException $e) {
    Logger::getLogger('qualitynomination')->error('APIException ' . $e);
    header('HTTP/1.1 404 Not Found');
    die();
}
