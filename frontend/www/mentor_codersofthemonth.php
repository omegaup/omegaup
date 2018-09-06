<?php

require_once('../server/bootstrap.php');
$session = SessionController::apiCurrentSession(new Request([
        'auth_token' => array_key_exists('ouat', $_REQUEST) ? $_REQUEST['ouat'] : null,
    ]))['session'];

if (!$session['valid'] || !Authorization::isMentor($session['identity']->identity_id)) {
    header('HTTP/1.1 404 Not Found');
    die();
}
try {
    $canChoseCoder = Authorization::canChooseCoder();
    $dateToCalculate = $canChoseCoder['canChoose'] ? date('Y-' . $canChoseCoder['monthToChoose'] . '-d') : 'now';
    $smarty->assign('payload', [
        'bestCoders' => CoderOfTheMonthDAO::calculateCoderOfTheMonth($dateToCalculate, true),
        'canChooseCoder' => $canChoseCoder['canChoose'],
    ]);
    $smarty->display('../templates/mentor.codersofthemonth.tpl');
} catch (APIException $e) {
    header('HTTP/1.1 404 Not Found');
    die();
}
