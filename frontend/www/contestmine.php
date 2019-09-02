<?php

require_once('../server/bootstrap_smarty.php');

try {
    $payload = \OmegaUp\Controllers\Contest::apiMyList(new \OmegaUp\Request([]));

    // If the user have private material (contests/problems), an alert is issued
    // suggesting to contribute to the community by releasing the material to
    // the public. This flag ensures that this alert is shown only once per
    // session, the first time the user visits the "My contests" page.
    $privateContestsAlert = ($session['valid'] &&
        !isset($_SESSION['private_contests_alert']) &&
        \OmegaUp\DAO\Contests::getPrivateContestsCount($session['user']) > 0);

    if ($privateContestsAlert) {
        $_SESSION['private_contests_alert'] = true;
    }

    $smarty->assign('privateContestsAlert', $privateContestsAlert);
    $smarty->assign('payload', $payload);
    $smarty->display('../templates/contest.mine.tpl');
} catch (\OmegaUp\Exceptions\ApiException $e) {
    Logger::getLogger('contestlist')->error('APIException ' . $e);
    header('HTTP/1.1 404 Not Found');
    die(file_get_contents('404.html'));
}
