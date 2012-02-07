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
	
	require_once( "api/ShowProblemInContest.php");

    $page = new OmegaupComponentPage();

    $page->addComponent( new TitleComponent("Concurso !"));


	if(!isset($_GET["alias"])){
    	$page->addComponent( new TitleComponent("Este concurso no existe", 2) );
		$page->render();
	}

	$this_contests = ContestsDAO::search( new Contests( array( "alias" => $_GET["alias"] ) ) );

	if( count($this_contests) == 0){
    	$page->addComponent( new TitleComponent("Este concurso no existe", 2) );
		$page->render();		
	}
	
	$this_contest = $this_contests[0];
	
	if( is_null($this_contest) ){
    	$page->addComponent( new TitleComponent("Este concurso no existe", 2) );
		$page->render();
	}

	$this_user = LoginController::getCurrentUser();

	//is it public?
	if( ! ($this_contest->getPublic() ) ){
		
		//its not, can the current user see it?
		if( is_null( $this_user )){
			//no one is even logged, in, reject this
			$page->addComponent( new TitleComponent("Este concurso es privado", 2) );
			$page->addComponent( new TitleComponent("Inicia sesion para continuar.", 3) );
			$page->render();
		}
		
		//ok we got a user
		$relation = ContestsUsersDAO::getByPK( $this_user->getUserId(), $this_contest->getContestid() );
		
		if(is_null($relation)){
			//nope, user cannot see it
			$page->addComponent( new TitleComponent("Este concurso es privado", 2) );
			$page->addComponent( new TitleComponent("No puedes ver este concurso.", 3) );
			$page->render();			
		}

		//go ahead...
	}


	$params = array( "contest_alias" => "prueba",
					 "problem_alias" => "sumas" );

	RequestContext::$params = $params;

	//bring the problem set
	try{
	    $contestApi = new ShowProblemInContest(    );
		$results = $contestApi->ExecuteApi( );
			
	}catch(Exception $e){
		$page->addComponent( new TitleComponent("<a href='https://omegaup.com/arena/{$_GET['alias']}/'>Ir al concurso</a>", 3) );
		$page->render();
	}

	
    $page->render();
