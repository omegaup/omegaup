<?php
require_once('../../server/bootstrap_smarty.php');

$r = new Request($_REQUEST);
$session = SessionController::apiCurrentSession($r)['session'];
$r['statement_type'] = 'markdown';
$r['show_solvers'] = true;
try {
    $result = ProblemController::apiDetails($r);
    $problem = ProblemsDAO::GetByAlias($result['alias']);
} catch (ApiException $e) {
    header('HTTP/1.1 404 Not Found');
    die(file_get_contents('../404.html'));
}

$smarty->assign('source', $result['source']);
$smarty->assign('problemsetter', $result['problemsetter']);
$smarty->assign('title', $result['title']);
$smarty->assign('points', $result['points']);
$smarty->assign('time_limit', $result['time_limit'] / 1000 . 's');
$smarty->assign('validator_time_limit', $result['validator_time_limit'] / 1000 . 's');
$smarty->assign('overall_wall_time_limit', $result['overall_wall_time_limit'] / 1000 . 's');
$smarty->assign('memory_limit', $result['memory_limit'] / 1024 . 'MB');

$smarty->assign('payload', $result);

$smarty->display('../../templates/arena.problem.print.tpl');
