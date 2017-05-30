<?php

require_once('../server/bootstrap.php');

// If the user have private material (contests/problems), an alert is issued
// suggesting to contribute to the community by releasing the material to
// the public. This flag ensures that this alert is shown only once per
// session, the first time the user visits the "My contests" page.
$private_contests_alert = 0;

if ($session['valid'] && !isset($_SESSION['private_contests_alert'])) {
    if (ContestsDAO::getPrivateContestsCount($session['user']) > 0) {
        $_SESSION['private_contests_alert'] = 1;
        $private_contests_alert = 1;
    }
}

$smarty->assign('PRIVATE_CONTESTS_ALERT', $private_contests_alert);
try {
    $payload = ContestController::apiMyList(new Request([]));
    $smarty->assign('payload', $payload);
    $smarty->display('../templates/contest.mine.tpl');
} catch (APIException $e) {
    Logger::getLogger('contestlist')->error('APIException ' . $e);
    header('HTTP/1.1 404 Not Found');
    die();
}
