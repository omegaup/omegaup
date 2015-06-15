<?php
require_once('../../server/bootstrap.php');

$smarty->assign('admin', false);
$smarty->assign('practice', false);

$r = new Request(array(
	"auth_token" => array_key_exists("ouat", $_REQUEST) ? $_REQUEST["ouat"] : null,
	"contest_alias" => $_REQUEST["contest_alias"],
));

if (ContestController::showContestIntro($r)) {
	$smarty->display('../../templates/arena.contest.intro.tpl');
} else  {
	$smarty->assign('jsfile', '/ux/contest.js');
	$smarty->display('../../templates/arena.contest.tpl');
}
