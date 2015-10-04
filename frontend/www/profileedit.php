<?php

require_once("../server/bootstrap.php");

UITools::redirectToLoginIfNotLoggedIn();
UITools::setProfile($smarty);

$ses = SessionController::apiCurrentSession();

if (isset($ses["needs_basic_info"]) && $ses["needs_basic_info"]) {
	$smarty->display('../templates/user.basicedit.tpl');
} else {
	$smarty->display('../templates/user.edit.tpl');
}
