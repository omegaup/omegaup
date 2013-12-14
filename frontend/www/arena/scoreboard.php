<?php
require_once('../../server/bootstrap.php');
UITools::redirectToLoginIfNotLoggedIn();
$smarty->display('../../templates/arena.scoreboard.tpl');
