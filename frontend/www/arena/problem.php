<?php
require_once('../../server/bootstrap.php');

$r = new Request($_REQUEST);
$session = SessionController::apiCurrentSession($r)['session'];
$r['statement_type'] = 'html';
$r['show_solvers'] = true;
try {
    $result = ProblemController::apiDetails($r);
    $problem = ProblemsDAO::GetByAlias($result['alias']);
    $key = new QualityNominations([
            'user_id' => $session['user'],
            'problem_id' => $problem->problem_id,
            'nomination' => 'dismissal',
            'contents' => json_encode([
                'rationale' => 'dismiss' ]),
            'status' => 'open',
        ]);
    $nominationStatus = null;
    $dismissal = null ;
    $problem_dismissed = QualityNominationsDAO::search($key);
    if ($session['valid']) {
        $nominationStatus = QualityNominationsDAO::getNominationStatusForProblem(
            $problem,
            $session['user']
        );
        $dismissal = count($problem_dismissed) > 0;
        $nominationStatus['dismissal'] = $dismissal;
    } else {
        $nominationStatus = ['solved' => false, 'nominated' => false, 'dismissal' => false];
    }
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
$smarty->assign('quality_payload', [
    'solved' => (bool) $nominationStatus['solved'],
    'nominated' => (bool) $nominationStatus['nominated'],
    'dismissal' => (bool) $nominationStatus['dismissal'],
    'problem_alias' => $result['alias'],
    'language' => $result['problem_statement_language'],
]);
$smarty->assign('karel_problem', count(array_intersect(
    explode(',', $result['languages']),
    ['kp', 'kj']
)) == 2);
if (isset($result['sample_input'])) {
    $smarty->assign('sample_input', $result['sample_input']);
}

$result['user'] = [
    'logged_in' => $session['valid'],
    'admin' => $session['valid'] ?
        Authorization::isProblemAdmin($session['user']->user_id, $problem) :
        false
];
$smarty->assign('problem_admin', $result['user']['admin']);

// Remove the largest element to reduce the payload.
unset($result['problem_statement']);
$smarty->assign('problem', json_encode($result));

$smarty->display('../../templates/arena.problem.tpl');
