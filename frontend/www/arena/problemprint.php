<?php
require_once('../../server/bootstrap_smarty.php');

$r = new \OmegaUp\Request($_REQUEST);
$r['statement_type'] = 'markdown';
$r['show_solvers'] = true;
try {
    $result = \OmegaUp\Controllers\Problem::apiDetails($r);
    $problem = \OmegaUp\DAO\Problems::getByAlias(
        $r->ensureString('problem_alias')
    );
    if (is_null($problem) || empty($result['settings'])) {
        throw new \OmegaUp\Exceptions\NotFoundException('problemNotFound');
    }
} catch (\OmegaUp\Exceptions\ApiException $e) {
    header('HTTP/1.1 404 Not Found');
    die(file_get_contents('../404.html'));
}

$smarty->assign('source', $result['source'] ?? '');
$smarty->assign('problemsetter', $result['problemsetter'] ?? []);
$smarty->assign('title', $result['title'] ?? '');
$smarty->assign('points', $result['points'] ?? 0);
$smarty->assign(
    'time_limit',
    \OmegaUp\Controllers\Problem::parseDuration(
        $result['settings']['limits']['TimeLimit']
    )
);
$smarty->assign(
    'overall_wall_time_limit',
    \OmegaUp\Controllers\Problem::parseDuration(
        $result['settings']['limits']['OverallWallTimeLimit']
    )
);
$smarty->assign(
    'memory_limit',
    (\OmegaUp\Controllers\Problem::parseSize(
        $result['settings']['limits']['MemoryLimit']
    ) / 1024 / 1024) . ' MiB'
);

$smarty->assign('payload', $result);

$smarty->display('../../templates/arena.problem.print.tpl');
