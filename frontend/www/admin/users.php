<?php

	require_once( "../../server/bootstrap.php" );


	$u = UsersDAO::getAll( );

	

    $smarty->display( '../templates/admin_users.tpl' );
