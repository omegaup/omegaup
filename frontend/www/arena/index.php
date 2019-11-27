<?php
require_once('../../server/bootstrap_smarty.php');

// Fetch contests
try {
    $query = '';
    if (!empty($_REQUEST['query'])) {
        /** @var array<string, mixed> $_REQUEST */
        $query = substr(strval($_REQUEST['query']), 0, 256);
    }
    $smarty->assign('query', $query);
} catch (Exception $e) {
    // Oh, well...
}

$smarty->display('../../templates/arena.index.tpl');
