<?php

require_once('../server/bootstrap.php');

$smarty->assign('IS_UPDATE', 1);
$smarty->assign('PAYLOAD', [
    'countries' => CountriesDAO::getAll(null, null, 'name'),
]);
$smarty->display('../templates/group.edit.tpl');
