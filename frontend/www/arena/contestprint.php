<?php
require_once('../../server/bootstrap.php');

try {
    $r = new Request([
            'contest_alias' => $_REQUEST['alias'],
            'auth_token' => array_key_exists('ouat', $_REQUEST) ? $_REQUEST['ouat'] : null,
        ]);

    // Open the contest for the current user
    $contest = ContestController::apiOpen($r);

    // with the contest opened, request the contest details
    $contest = ContestController::apiDetails($r);
} catch (Exception $e) {
    header('HTTP/1.1 404 Not Found');
    die(file_get_contents('../404.html'));
}

$problems = $contest['problems'];
foreach ($problems as &$problem) {
    $r = new Request([
        'contest_alias' => $_REQUEST['alias'],
        'problem_alias' => $problem['alias'],
        'auth_token' => $smarty->getTemplateVars('CURRENT_USER_AUTH_TOKEN'),
    ]);

    $r->method = 'ProblemController::apiDetails';
    $response = ApiCaller::call($r);

    $problem['statement'] = $response['problem_statement'];
}

$smarty->assign('contestName', $contest['title']);
$smarty->assign('problems', $problems);
$smarty->display('../templates/contest.print.tpl');
