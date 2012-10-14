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

	require_once( "../server/bootstrap.php" );


    $page = new OmegaupComponentPage();



	$page->addComponent( new TitleComponent("Elevando el nivel de nuestros desarrolladores", 3));	
	$page->addComponent( new FreeHtmlComponent("<img style='width: 200px; padding:0px; margin:0px; -webkit-box-shadow:0px 0px;' src='media/omegaup_curves.png'>"));



	

    $page->render();