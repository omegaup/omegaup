<?php
require_once('../../server/bootstrap.php');

UITools::setProfile($smarty);

// Fetch contests
try {
    $keyword = '';
    if (!empty($_GET['query']) && strlen($_GET['query']) > 0) {
        $keyword = substr($_GET['query'], 0, 256);
        $r['query'] = $keyword;
    }
    $smarty->assign('KEYWORD', $keyword);
} catch (Exception $e) {
    // Oh, well...
}

$smarty->display('../../templates/arena.index.tpl');
