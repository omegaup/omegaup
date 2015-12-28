<?php

require_once('../server/bootstrap.php');

$smarty->assign('IS_UPDATE', 1);
$smarty->display('../templates/group.edit.tpl');
