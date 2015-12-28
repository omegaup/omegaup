<?php
require_once('../../server/bootstrap.php');
require_once('../api/ApiCaller.php');
$r = new Request(array(
        'contest_alias' => $_REQUEST['alias'],
        'auth_token' => $smarty->getTemplateVars('CURRENT_USER_AUTH_TOKEN'),
    ));
$r->method = 'ContestController::apiDetails';
$contest = ApiCaller::call($r);

$problems = $contest['problems'];
foreach ($problems as &$problem) {
    $r = new Request(array(
        'contest_alias' => $_REQUEST['contest'],
        'problem_alias' => $problem['alias'],
        'auth_token' => $smarty->getTemplateVars('CURRENT_USER_AUTH_TOKEN'),
    ));

    $r->method = 'ProblemController::apiDetails';
    $response = ApiCaller::call($r);

    $problem['statement'] = $response['problem_statement'];
}

$smarty->assign('contestName', $contest['title']);
$smarty->assign('problems', $problems);
$smarty->display('../templates/contest.print.tpl');
