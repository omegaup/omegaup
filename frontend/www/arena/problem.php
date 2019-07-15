<?php
require_once('../../server/bootstrap_smarty.php');
require_once('libs/dao/QualityNominations.dao.php');

$r = new Request($_REQUEST);
$problemAlias = $r['problem_alias'];
$session = SessionController::apiCurrentSession($r)['session'];
$r['show_solvers'] = true;
try {
    $result = ProblemController::apiDetails($r);
    if (is_null($result) || empty($result['exists'])) {
        header('HTTP/1.1 404 Not Found');
        die(file_get_contents('../404.html'));
    }
    $problem = ProblemsDAO::GetByAlias($problemAlias);
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
$smarty->assign('time_limit', $result['settings']['limits']['TimeLimit']);
$smarty->assign('overall_wall_time_limit', $result['settings']['limits']['OverallWallTimeLimit']);
$smarty->assign('memory_limit', ($result['settings']['limits']['MemoryLimit'] / 1024 / 1024) . ' MiB');
$smarty->assign('input_limit', ($result['input_limit'] / 1024) . ' KiB');
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
if (isset($result['settings']['cases'])
    && isset($result['settings']['cases']['sample'])
    && isset($result['settings']['cases']['sample']['in'])
) {
    $smarty->assign('sample_input', $result['settings']['cases']['sample']['in']);
}

$result['user'] = [
    'logged_in' => $session['valid'],
    'admin' => $session['valid'] ?
        Authorization::isProblemAdmin($session['identity'], $problem) :
        false
];
$smarty->assign('problem_admin', $result['user']['admin']);

$result['histogram'] = [
    'difficulty_histogram' => $problem->difficulty_histogram,
    'quality_histogram' => $problem->quality_histogram,
    'quality' => floatval($problem->quality),
    'difficulty' => floatval($problem->difficulty)];
$result['shouldShowFirstAssociatedIdentityRunWarning'] =
    !$session['is_logged_with_main_identity'] &&
    ProblemsetsDAO::shouldShowFirstAssociatedIdentityRunWarning(
        $session['user']
    );
$smarty->assign('payload', $result);

$smarty->display('../../templates/arena.problem.tpl');
