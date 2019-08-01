<?php
require_once('../server/bootstrap_smarty.php');

try {
    $session = SessionController::apiCurrentSession(
        new Request($_REQUEST)
    )['session'];
    $smartyProperties = UserController::getRankDetailsForSmarty(
        new Request($_REQUEST),
        $session,
        $smarty
    );
} catch (ApiException $e) {
    header('HTTP/1.1 404 Not Found');
    die(file_get_contents('../404.html'));
}

foreach ($smartyProperties as $key => $value) {
    $smarty->assign($key, $value);
}

$smarty->display('../templates/rank.tpl');
