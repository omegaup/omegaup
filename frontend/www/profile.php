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

	require_once( "../server/inc/bootstrap.php" );


	$page = new OmegaupComponentPage();

	//id argument does not exist
	if(isset($_GET["id"])){
		$this_user = UsersDAO::getByPK( $_GET["id"] );	
		
	}else{

		$this_user = LoginController::getCurrentUser();

	}

	
	
	//user does not exist
	if(is_null($this_user)){
		$page->addComponent( new TitleComponent("Whoops, este usuario no exite !") );
		$page->render();
		exit;
	}

	//go ahead
	$profileCmp = new UserProfileComponent( $this_user );
	
	$luser = LoginController::getCurrentUser();
	if( !is_null($luser) && ($luser->getUserId() == $this_user->getUserId() ) ){
		$profileCmp->setEditable(true);
	}
	$page->addComponent( $profileCmp );

	$runs = new RunsListComponent();
	$runs->setUser( $this_user->getUserId() );
	$page->addComponent( $runs );
	

	$page->render();