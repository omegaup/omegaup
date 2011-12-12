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
		
		$page->addComponent( new TitleComponent($_FILES["file"]["name"], 3));
		$page->addComponent( new TitleComponent($_FILES["file"]["tmp_name"], 3));

		ProblemsController::parseZip( $_FILES["file"]["tmp_name"] );
	}

	$page->addComponent( new TitleComponent("Nuevo problema (ZIP)", 3));
	$page->addComponent( new SubmitFileComponent() );

	$page->addComponent( new TitleComponent("Nuevo problema", 3));
    $new_problem = new DAOFormComponent( new Problems() );
    $page->addComponent( $new_problem );
    
    $page->render();