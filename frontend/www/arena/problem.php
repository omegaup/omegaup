<?php
require_once('../../server/bootstrap_smarty.php');

try {
    $smartyProperties = ProblemController::getProblemDetailsForSmarty(
        new Request($_REQUEST)
    );
} catch (ApiException $e) {
    header('HTTP/1.1 404 Not Found');
    die(file_get_contents('../404.html'));
}
foreach ($smartyProperties as $key => $value) {
    $smarty->assign($key, $value);
}

$smarty->display('../../templates/arena.problem.tpl');
