<?php

require_once('../server/bootstrap_smarty.php');

try {
    $payload = \OmegaUp\Controllers\Group::apiMyList(new \OmegaUp\Request([]));
    $smarty->assign('payload', $payload);
    $smarty->display('../templates/group.list.tpl');
} catch (\OmegaUp\Exceptions\ApiException $e) {
    Logger::getLogger('grouplist')->error('APIException ' . $e);
    header('HTTP/1.1 404 Not Found');
    die(file_get_contents('404.html'));
}
