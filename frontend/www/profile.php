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
	if(!isset($_GET["id"])){

		$page->addComponent( new TitleComponent("Whoops, este usuario no exite !") );
		$page->render();
		exit;
	}

	$this_user = UsersDAO::getByPK( $_GET["id"] );
	
	//user does not exist
	if(is_null($this_user)){

		$page->addComponent( new TitleComponent("Whoops, este usuario no exite !") );
		$page->render();
		exit;
	}

	//go ahead

	$page->addComponent( new TitleComponent($this_user->getName(), 2) );

	$html = '<img src="http://www.gravatar.com/avatar/'. md5($this_user->getUsername())  .'?s=128&amp;d=identicon&amp;r=PG"  >';
	
	$page->addComponent( new FreeHtmlComponent($html) );

	
	$page->addComponent( new FreeHtmlComponent("<hr>") );	

	

	$page->render();