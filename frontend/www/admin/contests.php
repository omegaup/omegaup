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
    $page->addComponent( new TitleComponent("Concursos"));


	$page->addComponent( new TitleComponent("Concursos activos", 3));	
	
	$header = array(
			"contest_id" 	=> "Id",
			"title" 	=> "title"
		);
	
	$contests = ContestsDAO::getAll();

	$users_table = new TableComponent( $header, $contests );
    
   	$page->addComponent( $users_table );



   	$page->addComponent( new TitleComponent("Nuevo concurso", 3));	

   	$new_contest = new DAOFormComponent( new Contests() );

   	$new_contest->addSubmit("Agendar concurso");
   	
   	$page->addComponent( $new_contest );
    
    $page->render();