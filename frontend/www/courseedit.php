<?php
require_once('../server/bootstrap.php');
if (!$experiments->isEnabled(Experiments::SCHOOLS)) {
    header('HTTP/1.1 404 Not Found');
    die();
}

$smarty->assign('IS_UPDATE', 1);
$smarty->display('../templates/course.edit.tpl');
