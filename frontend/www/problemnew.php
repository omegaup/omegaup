<?php

require_once('../server/bootstrap.php');
require_once('api/ApiCaller.php');

$smarty->assign('TITLE', '');
$smarty->assign('ALIAS', '');
$smarty->assign('VALIDATOR', 'token-caseless');
$smarty->assign('TIME_LIMIT', '1000');
$smarty->assign('VALIDATOR_TIME_LIMIT', '1000');
$smarty->assign('OVERALL_WALL_TIME_LIMIT', '60000');
$smarty->assign('EXTRA_WALL_TIME', '0');
$smarty->assign('OUTPUT_LIMIT', '10240');
$smarty->assign('MEMORY_LIMIT', '32768');
$smarty->assign('EMAIL_CLARIFICATIONS', '0');
$smarty->assign('SOURCE', '');
$smarty->assign('VISIBILITY', '0');
$smarty->assign('LANGUAGES', 'c,cpp,cpp11,cs,hs,java,lua,pas,py,rb');

if (isset($_POST['request']) && ($_POST['request'] == 'submit')) {
    $r = new Request([
                'auth_token' => $smarty->getTemplateVars('CURRENT_USER_AUTH_TOKEN'),
                'title' => $_POST['title'],
                'problem_alias' => $_POST['alias'],
                'validator' => $_POST['validator'],
                'time_limit' => $_POST['time_limit'],
                'validator_time_limit' => $_POST['validator_time_limit'],
                'overall_wall_time_limit' => $_POST['overall_wall_time_limit'],
                'extra_wall_time' => $_POST['extra_wall_time'],
                'memory_limit' => $_POST['memory_limit'],
                'output_limit' => $_POST['output_limit'],
                'source' => $_POST['source'],
                'visibility' => $_POST['visibility'],
                'languages' => $_POST['languages'],
                'email_clarifications' => $_POST['email_clarifications']
            ]);
    $r->method = 'ProblemController::apiCreate';

    $response = ApiCaller::call($r);

    if ($response['status'] == 'error') {
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
        $smarty->assign('OVERALL_WALL_TIME_LIMIT', $_POST['overall_wall_time_limit']);
        $smarty->assign('EXTRA_WALL_TIME', $_POST['extra_wall_time']);
        $smarty->assign('MEMORY_LIMIT', $_POST['memory_limit']);
        $smarty->assign('OUTPUT_LIMIT', $_POST['output_limit']);
        $smarty->assign('SOURCE', $_POST['source']);
        $smarty->assign('LANGUAGES', $_POST['languages']);
        $smarty->assign('EMAIL_CLARIFICATIONS', $_POST['email_clarifications']);
        $smarty->assign('VISIBILITY', $_POST['visibility']);
    } elseif ($response['status'] == 'ok') {
        header("Location: /problem/{$_POST['alias']}/edit/");
        die();
    }
}

$smarty->display('../templates/problem.new.tpl');
