<?php
require_once('../server/bootstrap_smarty.php');
UITools::redirectToLoginIfNotLoggedIn();

$smarty->display('../templates/course.list.tpl');
