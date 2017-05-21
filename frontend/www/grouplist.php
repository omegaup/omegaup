<?php

require_once('../server/bootstrap.php');

try {
    $payload = GroupController::apiMyList(new Request([]));
    $smarty->assign('payload', $payload);
    $smarty->display('../templates/group.list.tpl');
} catch (APIException $e) {
    Logger::getLogger('grouplist')->error('APIException ' . $e);
    header('HTTP/1.1 404 Not Found');
    die();
}
