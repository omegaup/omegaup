<?php

	require_once("../server/bootstrap.php");

	// Retrive public contests for contest.list.tpl
	//$r = ContestController::apiList();
	//var_dump($r);
	$smarty->display('../templates/contest.tpl');
