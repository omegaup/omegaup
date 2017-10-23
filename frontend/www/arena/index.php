<?php
require_once('../../server/bootstrap.php');

UITools::setProfile($smarty);

// Fetch contests
try {
    $query = '';
    if (!empty($_REQUEST['query']) && strlen($_REQUEST['query']) > 0) {
        $query = substr($_REQUEST['query'], 0, 256);
        $r['query'] = $query;
    }
    $smarty->assign('query', $query);
} catch (Exception $e) {
    // Oh, well...
}

$smarty->display('../../templates/arena.index.tpl');
