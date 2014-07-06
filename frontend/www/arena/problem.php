<?php
require_once('../../server/bootstrap.php');
$r = new Request($_REQUEST);
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
$smarty->assign('source', $result['source']);
$smarty->assign('title', $result['title']);
$smarty->assign('points', $result['points']);
$smarty->assign('validator', $result['validator']);
$smarty->assign('time_limit', $result['time_limit'] / 1000 . 's');
$smarty->assign('memory_limit', $result['memory_limit'] / 1024 . 'MB');
$smarty->assign('solvers', $result['solvers']);

$session = SessionController::apiCurrentSession($r);

$result['user'] = array(
	'logged_in' => $session['valid'],
	'admin' => Authorization::CanEditProblem($session['id'], $problem)
);

// Remove the largest element to reduce the payload.
unset($result['problem_statement']);
$smarty->assign('problem', json_encode($result));

$smarty->display('../../templates/arena.problem.tpl');
