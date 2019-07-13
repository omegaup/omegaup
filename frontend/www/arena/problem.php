<?php
require_once('../../server/bootstrap_smarty.php');

$r = new Request($_REQUEST);
$r['show_solvers'] = true;
try {
    $details = ProblemController::apiDetails($r);
    if (is_null($details) || empty($details['exists'])) {
        header('HTTP/1.1 404 Not Found');
        die(file_get_contents('../404.html'));
    }
    [$result, $extraInformation] = ProblemController::getExtraInformation(
        $r,
        $details
    );
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
$smarty->assign(
    'overall_wall_time_limit',
    $result['settings']['limits']['OverallWallTimeLimit']
);
$smarty->assign(
    'memory_limit',
    ($result['settings']['limits']['MemoryLimit'] / 1024 / 1024) . ' MiB'
);
$smarty->assign('input_limit', ($result['input_limit'] / 1024) . ' KiB');
$smarty->assign('solvers', $result['solvers']);
$smarty->assign('quality_payload', $extraInformation['nomination_status']);
$smarty->assign('qualitynomination_reportproblem_payload', [
    'problem_alias' => $result['alias'],
]);
$smarty->assign('karel_problem', $extraInformation['karel_problem']);
if (!is_null($extraInformation['sample_input'])) {
    $smarty->assign('sample_input', $extraInformation['sample_input']);
}

$smarty->assign('problem_admin', $extraInformation['problem_admin']);
$smarty->assign('payload', $result);

$smarty->display('../../templates/arena.problem.tpl');
