<?php
    require_once( "../server/bootstrap.php" );
	
	// Get rank
	UITools::setRankByProblemsSolved($smarty, 0, 5);
	
	// Coder of the month
	$coderOfTheMonthResponse = UserController::apiCoderOfTheMonth(new Request());
	$smarty->assign('coderOfTheMonthData', $coderOfTheMonthResponse["userinfo"]);

    $smarty->display( '../templates/index.tpl' );
