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

    $page = new OmegaupComponentPage();
    $page->addComponent( new TitleComponent("Concursos en Omegaup !"));

    //get the'm contests
    $contestApi = new ShowContests();
	$results = $contestApi->ExecuteApi( );


	$header = array(  
		      "title"		=>"title",
		      "description"	=>"description",
		      "start_time"	=>"start_time",
		      "finish_time"	=>"finish_time",
		      "public"		=>"public",
		      "alias"		=>"alias",
		      "director_id"	=>"director_id"
		 );

	$rows = $results["contests"];
	
	$table = new TableComponent( $header, $rows );
	
	$table->addOnClick( "alias", "(function(alias){window.location ='contest.php?alias='+alias;})" );
	
	$page->addComponent( $table );
	


    $page->render();