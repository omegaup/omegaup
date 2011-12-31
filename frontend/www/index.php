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
    $page->addComponent( new TitleComponent("Bienvenido a Omegaup !"));


	
	//announcements
	$page->addComponent( new TitleComponent("announcements", 2));
	$announcements = AnnouncementDAO::getAll();
	
	
	//coder of the month
	$page->addComponent( new TitleComponent("coder of the month", 2));	
	$coder = CoderOfTheMonthDAO::getAll();
	
	
	//current contests
	$page->addComponent( new TitleComponent("active contests", 2));	
	$contest_query = new Contests();
	$contest_query->setFinishTime( date("Y-m-d H:i:s", time()) );
	$contest_query->setPublic(true);
	
	$contests_list = ContestsDAO::search( $contest_query );
	
	
	

    $page->render();