<?php

require_once("../server/bootstrap.php");

UITools::redirectToLoginIfNotLoggedIn();
UITools::setProfile($smarty);

$smarty->display('../templates/user.email.edit.tpl');


?>
