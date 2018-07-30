<?php
require_once('../../server/bootstrap.php');

$r = new Request($_REQUEST);
$session = SessionController::apiCurrentSession($r)['session'];
$r['statement_type'] = 'markdown';
$r['show_solvers'] = true;
try {
    $result = ProblemController::apiDetails($r);
    $problem = ProblemsDAO::GetByAlias($result['alias']);
    $nominationStatus = null;
    if ($session['valid']) {
        $nominationStatus = QualityNominationsDAO::getNominationStatusForProblem(
            $problem,
            $session['identity']
        );
    } else {
        $nominationStatus = ['solved' => false, 'nominated' => false, 'dismissed' => false];
    }
} catch (ApiException $e) {
    header('HTTP/1.1 404 Not Found');
    die(file_get_contents('../404.html'));
}
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
$smarty->assign('input_limit', $result['input_limit'] / 1024 . ' KiB');
$smarty->assign('solvers', $result['solvers']);
$smarty->assign('quality_payload', [
    'solved' => (bool) $nominationStatus['solved'],
    'nominated' => (bool) $nominationStatus['nominated'],
    'dismissed' => (bool) $nominationStatus['dismissed'],
    'problem_alias' => $result['alias'],
    'language' => $result['statement']['language'],
]);
$smarty->assign('qualitynomination_reportproblem_payload', [
    'problem_alias' => $result['alias'],
]);
$smarty->assign('karel_problem', count(array_intersect(
    $result['languages'],
    ['kp', 'kj']
)) == 2);
if (isset($result['sample_input'])) {
    $smarty->assign('sample_input', $result['sample_input']);
}

$result['user'] = [
    'logged_in' => $session['valid'],
    'admin' => $session['valid'] ?
        Authorization::isProblemAdmin($session['identity']->identity_id, $problem) :
        false
];
$smarty->assign('problem_admin', $result['user']['admin']);

$result['histogram'] = [
    'difficulty_histogram' => $problem->difficulty_histogram,
    'quality_histogram' => $problem->quality_histogram,
    'quality' => floatval($problem->quality),
    'difficulty' => floatval($problem->difficulty)];
$smarty->assign('payload', $result);

$smarty->display('../../templates/arena.problem.tpl');
