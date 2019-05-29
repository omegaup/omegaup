<?php

require_once('../server/bootstrap_smarty.php');
require_once('api/ApiCaller.php');

$smarty->assign('IS_UPDATE', 1);
$smarty->assign('LOAD_MATHJAX', 1);
$smarty->assign('LOAD_PAGEDOWN', 1);

if (isset($_POST['request'])) {
    if ($_POST['request'] == 'submit') {
        $response = ProblemController::apiUpdate(new Request([
            'auth_token' => $smarty->getTemplateVars('CURRENT_USER_AUTH_TOKEN'),
            'problem_alias' => $_POST['problem_alias'],
            'title' => $_POST['title'],
            'message' => $_POST['message'],
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
        ]));
        if ($response['status'] == 'error') {
            onError($smarty, $response);
        }
    } elseif ($_POST['request'] == 'markdown') {
        $response = ProblemController::apiUpdateStatement([
            'auth_token' => $smarty->getTemplateVars('CURRENT_USER_AUTH_TOKEN'),
            'problem_alias' => $_POST['problem_alias'],
            'statement' => $_POST['wmd-input-statement'],
            'message' => $_POST['message'],
            'lang' => $_POST['statement-language'],
        ]);
        if ($response['status'] == 'error') {
            onError($smarty, $response);
        }
    }
    $smarty->assign('STATUS_SUCCESS', 'Problem updated succesfully!');
}

$smarty->display('../templates/problem.edit.tpl');

/**
 * Handle error (print msg, die)
 *
 * @param type $smarty
 * @param type $response
 */
function onError($smarty, $response) {
    $smarty->assign('STATUS_ERROR', $response['error']);
    $smarty->display('../templates/problem.edit.tpl');
    die();
}
