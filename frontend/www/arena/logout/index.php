<?php

/**
 * 
 * Please read full (and updated) documentation at: 
 * https://github.com/omegaup/omegaup/wiki/Arena 
 *
 *
 * 
 * POST /auth/logout
 * Se envía auth token para terminar la sesión.
 *
 * */

/**
 * Check for needed parameters
 * */
 if( 
		!isset($_GET["auth_token"])
	)
	{
		/**
		 * Dispatch missing parameters
		 * */
		header('HTTP/1.1 400 BAD REQUEST');
		
		die(json_encode(array(
			"status" => "error",
			"error"	 => "You are missing some parameters.",
			"errorcode" => 200
		)));
	}


/**
 * Save them 
 * */
define("AUTH_TOKEN", $_GET["auth_token"] );


/**
 * Ok, we are ready to roll. Bootstrap.
 * */
define("WHOAMI", "API");

require_once("../../../server/inc/bootstrap.php");




/**
 * Lets look for this auth token.
 * */
$token = AuthTokensDAO::getByPK( AUTH_TOKEN );

if($token === null){
	header('HTTP/1.1 401 AUTHENTICATION REQUIRED');
	
	die(json_encode(array(
		"status" => "error",
		"error"	 => "You must supply a valid auth token to access this section.",
		"errorcode" => 201
	)));
	
}



/*
 * Ok, they sent a valid auth, just erase it from the database.
 * */
try{
	AuthTokensDAO::delete( $token );	
	
}catch( Exception $e ){
	header('HTTP/1.1 500 INTERNAL SERVER ERROR');
	
	die(json_encode(array(
		"status" => "error",
		"error"	 => "Whops. Ive encoutered an error while writing your session to the database.",
		"errorcode" => 202
	)));
}


die(json_encode(array(
	"status" => "ok"
)));

