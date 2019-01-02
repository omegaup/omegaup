<?php
require_once('../../server/bootstrap_smarty.php');
if (!$experiments->isEnabled(Experiments::VIRTUAL)) {
    header('HTTP/1.1 404 Not Found');
    die;
}

$smarty->display('../templates/arena.contest.virtual.tpl');
