<?php
    require_once( "../server/bootstrap.php" );
	
	// Get rank
	UITools::setRankByProblemsSolved($smarty, 0, 5);

    $smarty->display( '../templates/index.tpl' );
