<?php
require_once('../server/bootstrap.php');
UITools::redirectToLoginIfNotLoggedIn();

$smarty->display('../templates/course.list.tpl');
