<?php

/**
 * 
 * Please read full (and updated) documentation at: 
 * https://github.com/omegaup/omegaup/wiki/Arena 
 *
 *
 * 
 * POST /auth/login
 * Se envía user name y password, se recibe auth token
 *
 * */

/**
 * Check for needed parameters
 * */
 if( !(
		isset($_POST["username"])  
 	 && isset($_POST["password"]) )
	)
	{
		/**
		 * Dispatch missing parameters
		 * */
		header('HTTP/1.1 400 BAD REQUEST');
		
		die(json_encode(array(
			"status" => "error",
			"error"	 => "You are missing some parameters.",
			"errorcode" => 100
		)));
	}


/**
 * Save them 
 * */
define("USERNAME", $_POST["username"] );
define("PASSWORD", $_POST["password"] );



/**
 * Ok, we are ready to roll. Bootstrap.
 * */
define("WHOAMI", "API");

require_once("../../../server/inc/bootstrap.php");




/**
 * Lets look for this user in the user table.
 * */
$user_query = new Users();
$user_query->setUsername( USERNAME );

$results = UsersDAO::search( $user_query );

if(sizeof($results) == 1){
	/**
	 * Found him !
	 * */
	$actual_user = $results[0];
	
}else{
	/**
	 * He was not ther, maybe he sent his email instead.
	 * */	
	$email_query = new Emails();
	$email_query->setEmail( USERNAME );
	
	$results = EmailsDAO::search( $email_query );
	
	if(sizeof($results) == 1){
		/**
		 * Found his email address. Now lets look for the user
		 * whose email is this.
		 * */
		$actual_user = UsersDAO::getByPK( $results[0]->getUserId() );
		
		
	}else{
		
		/**
		 * He is not in the users, nor the emails list.
		 * Lets go ahead and tell him.
		 * */
	
		die(json_encode(array(
			"status" => "error",
			"error"	 => "User not found !",
			"errorcode" => 101
		)));
	}
}

/**
 * Ok, ive found the user, now lets see if 
 * the passwords are correct.
 * 
 * */

/**
 * Just one thing, if the actual user has a NULL password
 * it means the user has been registered via a third party
 * (Google, Facebook, etc). For now, ill tell him he needs
 * to register 'nativelly' to use the API, since checking
 * for the users valid password is impsible for me (for now).
 *
 * */
if($actual_user->getPassword() === NULL){
	die(json_encode(array(
		"status" => "error",
		"error"	 => "It seems you have registered via a third party (Google, Facebook, etc). To use this API you must first create an omegaup.com password.",
		"errorcode" => 102
	)));
}



/**
 * Ok, go ahead and check the password. For now its only md5, *with out* salt.
 * */
if( 
	$actual_user->getPassword() !== md5(PASSWORD)
 ){
	
	/**
	 * Passwords did not match !
	 * */
	die(json_encode(array(
		"status" => "error",
		"error"	 => "Wrong password !",
		"errorcode" => 103
	)));
}



/**
 * Ok, passwords match !
 * Create the auth_token. Auth tokens will be valid for 24 hours.
 * */
 $auth_token = new AuthTokens();
 $auth_token->setUserId( $actual_user->getUserId() );

 /**
  * auth token consists of:
  * current time: to validate obsolete tokens
  * user who logged in:
  * some salted md5 string: to validate that it was me who actually made this token
  * 
  * */
 $auth_str = time() . "-" . $actual_user->getUserId() . "-" . md5( OMEGAUP_MD5_SALT . $actual_user->getUserId() . time() );

 $auth_token->setToken($auth_str);



 try{
	AuthTokensDAO::save( $auth_token );
	
 }catch(Exception $e){
	
		header('HTTP/1.1 500 INTERNAL SERVER ERROR');
		
		die(json_encode(array(
			"status" => "error",
			"error"	 => "Whops. Ive encoutered an error while writing your session to the database.",
			"errorcode" => 104
		)));
 }



/**
 * Print output.
 * */
die(json_encode(array(
	"status" => "ok",
	"auth_token" =>  $auth_token->getToken( )
)));


