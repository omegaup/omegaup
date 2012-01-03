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

	if(!isset($_GET["pid"])){
		Logger::log("Intentando mostrar un problema que no existe");
		$page->addComponent(new TitleComponent("Este problema no existe") );
		$page->render();
		exit;
	}


	$este_problema = ProblemsDAO::getByPK( $_GET["pid"] );

	if(is_null( $este_problema )){
		Logger::log("Intentando mostrar un problema que no existe");
		$page->addComponent(new TitleComponent("Este problema no existe") );
		$page->render();
		exit;
	}


	//Revisar que sea publico y si no es publico que este usuario lo pueda ver
	if( !$este_problema->getPublic() ){
		//validar que este usuario lo pueda ver
		exit; // || break;
	}

	//ProblemViewerComponent
	$page->addComponent( new ProblemViewerComponent( $este_problema ) );


	$page->render();
	