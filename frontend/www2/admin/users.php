<?php

	/*
	 * LEVEL_NEEDED defines the users who can see this page.
	 * Anyone without permission to see this page, will	
	 * be redirected to a page saying so.
	 * This variable *must* be set in order to bootstrap
	 * to continue. This is by design, in order to prevent
	 * leaving open holes for new pages.
	 * 
	 * */
	define( "LEVEL_NEEDED", true );

	require_once( "../../server/inc/bootstrap.php" );


    $page = new OmegaupAdminComponentPage();
    $page->addComponent( new TitleComponent("Usuarios de omegaup"));

	
	
	$header = array(
			"user_id" 	=> "Id",
			"username" 	=> "Username",
			"name" 		=> "name",
			"last_access" => "last_access"
		);
	
	$users = UsersDAO::getAll();

	$users_table = new TableComponent( $header, $users );
    
    $page->addComponent( $users_table );
    
    $page->render();