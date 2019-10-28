<?php
require_once('../../server/bootstrap_smarty.php');

$r = new \OmegaUp\Request($_REQUEST);
$r['statement_type'] = 'markdown';
$r['show_solvers'] = true;
try {
    $result = \OmegaUp\Controllers\Problem::apiDetails($r);
    $problem = \OmegaUp\DAO\Problems::GetByAlias($result['alias']);
} catch (\OmegaUp\Exceptions\ApiException $e) {
    header('HTTP/1.1 404 Not Found');
    die(file_get_contents('../404.html'));
}

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

$smarty->assign('payload', $result);

$smarty->display('../../templates/arena.problem.print.tpl');
