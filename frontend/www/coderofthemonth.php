<?php

require_once('../server/bootstrap_smarty.php');

$session = SessionController::apiCurrentSession(new Request([
        'auth_token' => array_key_exists('ouat', $_REQUEST) ? $_REQUEST['ouat'] : null,
    ]))['session'];

$currentTimeStamp = Time::get();
$currentDate = date('Y-m-d', $currentTimeStamp);
$firstDayOfNextMonth = new DateTime($currentDate);
$firstDayOfNextMonth->modify('first day of next month');
$dateToSelect = $firstDayOfNextMonth->format('Y-m-d');
try {
    $responseCodersOfTheMonth = UserController::apiCoderOfTheMonthList(new Request([
        'auth_token' => $session['auth_token'],
    ]));
    $responseCodersOfPreviousMonth = UserController::apiCoderOfTheMonthList(new Request([
        'auth_token' => $session['auth_token'],
        'date' => $currentDate,
    ]));
    $isMentor = $session['valid'] && Authorization::isMentor($session['identity']->identity_id);

    $response = [
        'codersOfCurrentMonth' => $responseCodersOfTheMonth['coders'],
        'codersOfPreviousMonth' => $responseCodersOfPreviousMonth['coders'],
        'isMentor' => $isMentor,
    ];

    if ($isMentor) {
        $response['options'] = [
            'bestCoders' => CoderOfTheMonthDAO::calculateCoderOfMonthByGivenDate($dateToSelect),
            'canChooseCoder' => Authorization::canChooseCoder($currentTimeStamp),
            'coderIsSelected' => !empty(CoderOfTheMonthDAO::getByTime($dateToSelect)),
        ];
    }

    $smarty->assign('payload', $response);

    $smarty->display('../templates/codersofthemonth.tpl');
} catch (APIException $e) {
    header('HTTP/1.1 404 Not Found');
    die();
}
