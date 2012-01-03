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


	$page = new OmegaupComponentPage();


	
	$lista = ProblemsController::getProblemList();

	$tabla = new TableComponent (
		array(
			"problem_id" 	=> "problem_id",
			"title" 		=> "title",
			"alias" 		=> "alias",
			"validator" 	=> "validator",
			"server" 		=> "server",
			"visits" 		=> "visits",
			"submissions" 	=> "submissions",
			"accepted" 		=> "accepted",
			"difficulty" 	=> "difficulty"
		),
		$lista
	);
	
	$page->addComponent( new FreeHtmlComponent( "<script>function ver_problema(id){ window.location='problema.php?pid='+id; }</script>" ) );
	
	$tabla->addOnClick("problem_id", "ver_problema");
	
	$page->addComponent( $tabla );

	$page->render();




