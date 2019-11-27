<?php
require_once('../../server/bootstrap_smarty.php');

try {
    $r = new \OmegaUp\Request([
            'contest_alias' => $_REQUEST['alias'],
            'auth_token' => array_key_exists(
                'ouat',
                $_REQUEST
            ) ? $_REQUEST['ouat'] : null,
        ]);

    // Open the contest for the current user
    $contest = \OmegaUp\Controllers\Contest::apiOpen($r);

    // with the contest opened, request the contest details
    $contest = \OmegaUp\Controllers\Contest::apiDetails($r);
} catch (Exception $e) {
    header('HTTP/1.1 404 Not Found');
    die(file_get_contents('../404.html'));
}

[
    'auth_token' => $authToken,
] = \OmegaUp\Controllers\Session::getCurrentSession();

$problems = $contest['problems'];
foreach ($problems as &$problem) {
    $problem['payload'] = \OmegaUp\Controllers\Problem::apiDetails(new \OmegaUp\Request([
        'contest_alias' => $_REQUEST['alias'],
        'problem_alias' => $problem['alias'],
        'auth_token' => $authToken,
    ]));
}

$smarty->assign('contestName', $contest['title']);
$smarty->assign('problems', $problems);
$smarty->display('../templates/contest.print.tpl');
