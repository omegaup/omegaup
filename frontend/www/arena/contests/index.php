<?php

/**
 * 
 * Please read full (and updated) documentation at: 
 * https://github.com/omegaup/omegaup/wiki/Arena 
 *
 *
 * 
 * GET /contests/
 * Lista (por default de los últimos 10 concursos) que el usuario "puede ver"
 *
 * */



/**
 * Ok, we are ready to roll. Bootstrap.
 * */
define("WHOAMI", "API");

require_once("../../../server/inc/bootstrap.php");



$user_id = null;

/**
 * Check if they sent me an auth token.
 * @todo auth_token should be get or post in this case ?
 * */
 if( 
		isset($_REQUEST["auth_token"])
	)
	{

		/**
		 * They sent me an auth token ! Lets look for it.
		 * */
		$token = AuthTokensDAO::getByPK( $_POST["auth_token"] );

		if($token !== null){
			/**
			  *
			  * Found it !
			  * */
			$user_id = $token->getUserId();
			
		}else{
			/**
			  *
			  * They have supplied an invalid token !
			  * */
			header('HTTP/1.1 400 BAD REQUEST');

			die(json_encode(array(
				"status" => "error",
				"error"	 => "You supplied an invalid auth token, or maybe it expired.",
				"errorcode" => 500
			)));
			
		}

	}



/**
 * Ok, now let get them' contests !
 * 
 * */
$contests = ContestsDAO::getAll( NULL, NULL, 'contest_id', "DESC" );

$contest_to_show = array();

/**
 * Ok, lets go 1 by 1, and if its public, show it,
 * if its not, check if the user has access to it.
 * */
foreach( $contests as $c ){

	if(sizeof($contest_to_show) == 10)
		break;

	if($c->getPublic()){
		array_push( $contest_to_show, $c->asArray() );
		continue;
	}
	
	/*
	 * Ok, its not public, lets se if we have a 
	 * valid user
	 * */
	if($user_id === null)
		continue;
	
	/**
	 * Ok, i have a user. Can he see this contest ?
	 * */
	$r = ContestsUsersDAO::getByPK( $user_id, $c->getContestId()  );
	
	if( $r === null ){
		/**
		 * Nope, he cant .
		 * */
		continue;
	}
	
	/**
	 * He can see it !
	 * 
	 * */
	array_push( $contest_to_show, $c->asArray() );
}



die(json_encode(array(
	"status" => "ok",
	"contests" => $contest_to_show
)));
