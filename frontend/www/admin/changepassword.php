<?php

require_once('../../server/bootstrap.php');

UITools::redirectToLoginIfNotLoggedIn();
UITools::redirectIfNoAdmin();

$smarty->display('../../templates/admin.changepassword.tpl');
