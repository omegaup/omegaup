<?php

require_once('../server/bootstrap_smarty.php');

$smarty->assign('TITLE', '');
$smarty->assign('ALIAS', '');
$smarty->assign('VALIDATOR', 'token-caseless');
$smarty->assign('TIME_LIMIT', '1000');
$smarty->assign('VALIDATOR_TIME_LIMIT', '1000');
$smarty->assign('OVERALL_WALL_TIME_LIMIT', '60000');
$smarty->assign('EXTRA_WALL_TIME', '0');
$smarty->assign('OUTPUT_LIMIT', '10240');
$smarty->assign('INPUT_LIMIT', '10240');
$smarty->assign('MEMORY_LIMIT', '32768');
$smarty->assign('EMAIL_CLARIFICATIONS', '0');
$smarty->assign('SOURCE', '');
$smarty->assign('VISIBILITY', '0');
$smarty->assign('LANGUAGES', 'c,cpp,cpp11,cs,hs,java,lua,pas,py,rb');
$smarty->assign('SELECTED_TAGS', '');

[
    'auth_token' => $authToken,
] = \OmegaUp\Controllers\Session::getCurrentSession();

if (isset($_POST['request']) && ($_POST['request'] == 'submit')) {
    try {
        \OmegaUp\Controllers\Problem::apiCreate(new \OmegaUp\Request([
            'auth_token' => $authToken,
            'title' => $_POST['title'],
            'problem_alias' => $_POST['alias'],
            'validator' => $_POST['validator'],
            'time_limit' => $_POST['time_limit'],
            'validator_time_limit' => $_POST['validator_time_limit'],
            'overall_wall_time_limit' => $_POST['overall_wall_time_limit'],
            'extra_wall_time' => $_POST['extra_wall_time'],
            'memory_limit' => $_POST['memory_limit'],
            'output_limit' => $_POST['output_limit'],
            'input_limit' => $_POST['input_limit'],
            'source' => $_POST['source'],
            'visibility' => $_POST['visibility'],
            'languages' => $_POST['languages'],
            'email_clarifications' => $_POST['email_clarifications'],
            'selected_tags' => $_POST['selected_tags'],
        ]));
        header("Location: /problem/{$_POST['alias']}/edit/");
        die();
    } catch (\OmegaUp\Exceptions\ApiException $e) {
        $response = $e->asResponseArray();
        if (empty($response['error'])) {
            $smarty->assign('STATUS_ERROR', '{error}');
        } else {
            $smarty->assign('STATUS_ERROR', $response['error']);
        }
        $smarty->assign('TITLE', $_POST['title']);
        $smarty->assign('ALIAS', $_POST['alias']);
        $smarty->assign('VALIDATOR', $_POST['validator']);
        $smarty->assign('VALIDATOR_TIME_LIMIT', $_POST['validator_time_limit']);
        $smarty->assign('TIME_LIMIT', $_POST['time_limit']);
        $smarty->assign(
            'OVERALL_WALL_TIME_LIMIT',
            $_POST['overall_wall_time_limit']
        );
        $smarty->assign('EXTRA_WALL_TIME', $_POST['extra_wall_time']);
        $smarty->assign('MEMORY_LIMIT', $_POST['memory_limit']);
        $smarty->assign('OUTPUT_LIMIT', $_POST['output_limit']);
        $smarty->assign('INPUT_LIMIT', $_POST['input_limit']);
        $smarty->assign('SOURCE', $_POST['source']);
        $smarty->assign('LANGUAGES', $_POST['languages']);
        $smarty->assign('EMAIL_CLARIFICATIONS', $_POST['email_clarifications']);
        $smarty->assign('VISIBILITY', $_POST['visibility']);
        $smarty->assign('SELECTED_TAGS', $_POST['selected_tags']);
    }
}

$smarty->display('../templates/problem.new.tpl');
