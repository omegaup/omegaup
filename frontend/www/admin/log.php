<?php

	require_once( "../../server/bootstrap.php" );

	UITools::redirectToLoginIfNotLoggedIn();
	UITools::redirectIfNoAdmin();

	$smarty->assign('logContents', '');

	$smarty->display( '../../templates/admin.log.tpl' );
