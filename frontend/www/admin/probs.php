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
    $page->addComponent( new TitleComponent("Problemas"));


	
	/**
	  * ZIP Handling
	  * 
	  **/
	if(isset($_POST["file_sent"])){
		
		ProblemsController::parseZip( $_FILES["file"]["tmp_name"] );
	}


	$page->addComponent( new TitleComponent("Nuevo problema (ZIP)", 3));
	
	$page->addComponent( new SubmitProblemComponent() );

    
    $page->render();