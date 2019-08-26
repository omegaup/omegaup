<?php

require_once('../server/bootstrap_smarty.php');
require_once('api/ApiCaller.php');

$smarty->assign('IS_UPDATE', 1);
$smarty->assign('LOAD_MATHJAX', 1);
$smarty->assign('LOAD_PAGEDOWN', 1);

try {
    if (isset($_POST['request'])) {
        if ($_POST['request'] == 'submit') {
            ProblemController::apiUpdate(new Request([
                'auth_token' => $smarty->getTemplateVars('CURRENT_USER_AUTH_TOKEN'),
                'problem_alias' => $_POST['problem_alias'] ?? null,
                'title' => $_POST['title'] ?? null,
                'message' => $_POST['message'] ?? null,
                'validator' => $_POST['validator'] ?? null,
                'time_limit' => $_POST['time_limit'] ?? null,
                'validator_time_limit' => $_POST['validator_time_limit'] ?? null,
                'overall_wall_time_limit' => $_POST['overall_wall_time_limit'] ?? null,
                'extra_wall_time' => $_POST['extra_wall_time'] ?? null,
                'memory_limit' => $_POST['memory_limit'] ?? null,
                'output_limit' => $_POST['output_limit'] ?? null,
                'input_limit' => $_POST['input_limit'] ?? null,
                'source' => $_POST['source'] ?? null,
                'visibility' => $_POST['visibility'] ?? null,
                'languages' => $_POST['languages'] ?? null,
                'email_clarifications' => $_POST['email_clarifications'] ?? null,
            ]));
        } elseif ($_POST['request'] == 'markdown') {
            ProblemController::apiUpdateStatement([
                'auth_token' => $smarty->getTemplateVars('CURRENT_USER_AUTH_TOKEN'),
                'problem_alias' => $_POST['problem_alias'] ?? null,
                'statement' => $_POST['wmd-input-statement'] ?? null,
                'message' => $_POST['message'] ?? null,
                'lang' => $_POST['statement-language'] ?? null,
            ]);
        }
        $smarty->assign('STATUS_SUCCESS', 'Problem updated succesfully!');
    }
} catch (\OmegaUp\Exceptions\ApiException $e) {
    $smarty->assign('STATUS_ERROR', $e->getErrorMessage());
}

$smarty->display('../templates/problem.edit.tpl');
