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

	require_once( "api/ShowContests.php");


    $page = new OmegaupAdminComponentPage();

    $page->addComponent( new TitleComponent("Concursos"));

    
     //get the'm contests
	$header = array( 
		      "description"	=>"Descripcion",
		      "start_time"	=>"Tiempo de inicio",
		      "finish_time"	=>"Tiempo de fin"
		      //"public"		=>"public",
		      //"director_id"	=>"director_id"
		 );

	$rows = ContestsDAO::getAll();
	
	$table = new TableComponent( $header, $rows );
	

	

	function toDate( $unix_time ){
		if(strlen($unix_time) == 0) return "";
		return $unix_time;
		return date( "F jS h:i:s a", $unix_time);
	}
	
	function toBold($f, $row){
		
		return "<h3 style='margin:0px; padding:0px'>" . $row["title"] . "</h3>" ;
	}
	
	$table->addColRender( "start_time", 	"toDate" );
	$table->addColRender( "finish_time", 	"toDate" );
	$table->addColRender( "alias", 			"toBold");
	$table->addOnClick( "alias", "(function(alias){window.location ='contest.php?alias='+alias;})" );
	
	$page->addComponent( $table );
	

	$page->addComponent( new NewContestFormComponent() );

    $page->render();

