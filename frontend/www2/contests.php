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
	define( "LEVEL_NEEDED", false );

	require_once( "../server/inc/bootstrap.php" );
	require_once( "api/ShowContests.php");

	/*
	var_dump($_SESSION);
	if(isset($_SESSION["LOGGED_IN"]) && $_SESSION["LOGGED_IN"] )
	echo "Ok";
	else
	echo "notok";
	die("");
	*/

    $page = new OmegaupComponentPage();
    $page->addComponent( new TitleComponent("Concursos en Omegaup !"));

    //get the'm contests
    $contestApi = new ShowContests();
	$results = $contestApi->ExecuteApi( );



    $page->render();