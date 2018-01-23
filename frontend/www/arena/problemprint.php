<?php
require_once('../../server/bootstrap.php');

$r = new Request($_REQUEST);
$session = SessionController::apiCurrentSession($r)['session'];
$r['statement_type'] = 'html';
$r['show_solvers'] = true;
try {
    $result = ProblemController::apiDetails($r);
    $problem = ProblemsDAO::GetByAlias($result['alias']);
} catch (ApiException $e) {
    header('HTTP/1.1 404 Not Found');
    die(file_get_contents('../404.html'));
}

$smarty->assign('problem_statement', $result['problem_statement']);
$smarty->assign('problem_statement_language', $result['problem_statement_language']);
$smarty->assign('problem_alias', $result['alias']);
$smarty->assign('visibility', $result['visibility']);
$smarty->assign('source', $result['source']);
$smarty->assign('problemsetter', $result['problemsetter']);
$smarty->assign('title', $result['title']);
$smarty->assign('points', $result['points']);
$smarty->assign('validator', $result['validator']);
$smarty->assign('time_limit', $result['time_limit'] / 1000 . 's');
$smarty->assign('validator_time_limit', $result['validator_time_limit'] / 1000 . 's');
$smarty->assign('overall_wall_time_limit', $result['overall_wall_time_limit'] / 1000 . 's');
$smarty->assign('memory_limit', $result['memory_limit'] / 1024 . 'MB');
$smarty->assign('solvers', $result['solvers']);
$smarty->assign('karel_problem', count(array_intersect(
    $result['languages'],
    ['kp', 'kj']
)) == 2);
if (isset($result['sample_input'])) {
    $smarty->assign('sample_input', $result['sample_input']);
}

$result['user'] = [
    'logged_in' => $session['valid'],
    'admin' => Authorization::isProblemAdmin($session['user']->user_id, $problem)
];
$smarty->assign('problem_admin', $result['user']['admin']);

// Remove the largest element to reduce the payload.
unset($result['problem_statement']);
$smarty->assign('problem', json_encode($result));

$smarty->display('../../templates/problem.print.tpl');
