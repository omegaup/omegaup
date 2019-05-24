<?php
require_once('../../server/bootstrap_smarty.php');
$session = SessionController::apiCurrentSession(new Request([
        'auth_token' => array_key_exists('ouat', $_REQUEST) ? $_REQUEST['ouat'] : null,
    ]))['session'];
if (!$session['valid'] || !Authorization::isMentor($session['identity']->identity_id)) {
    header('HTTP/1.1 404 Not Found');
    die();
}
try {
    $currentTimeStamp = Time::get();
    $currentDate = date('Y-m-d', $currentTimeStamp);
    $firstDayOfNextMonth = new DateTime($currentDate);
    $firstDayOfNextMonth->modify('first day of next month');
    $dateToSelect = $firstDayOfNextMonth->format('Y-m-d');
    $smarty->assign('payload', [
        'bestCoders' => CoderOfTheMonthDAO::calculateCoderOfMonthByGivenDate($dateToSelect),
        'canChooseCoder' => Authorization::canChooseCoder($currentTimeStamp),
    ]);
    $smarty->display('../templates/mentor.codersofthemonth.tpl');
} catch (APIException $e) {
    header('HTTP/1.1 404 Not Found');
    die();
}
