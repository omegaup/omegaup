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
	require_once( "api/NewProblemInContest.php");

    $page = new OmegaupAdminComponentPage();
    $page->addComponent( new TitleComponent("Problemas"));


	
	/**
	  * ZIP Handling
	  * 
	  **/
	$apiExceptionToShow = null;
	if(isset($_POST["file_sent"])){
		//var_dump($_REQUEST);
		//var_dump($_FILES["file"]["tmp_name"]);
		$_REQUEST["public"] = "0";	
		$api = new NewProblemInContest();
		
		try{
			$api->ExecuteApi();	
					
		}catch(ApiException $apiException){
			$apiExceptionToShow = $apiException;

		}

		

	}

	if(!is_null($apiExceptionToShow)){
		$errArr = $apiExceptionToShow->getArrayMessage();
		$page->addComponent( new FreeHtmlComponent( "<div style='background: #FFEBE8;
		border: 1px solid #DD3C10;
		line-height: 15px;
		margin: 10px 0 0 0;
		padding: 3px;
		text-align: center;'>" . $errArr["error"] . "</div>" ) );
	}
	
	$page->addComponent( new TitleComponent("Nuevo problema (ZIP)", 3));
	
	$page->addComponent( new SubmitProblemComponent() );

    
    $page->render();
