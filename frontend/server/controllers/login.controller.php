<?php

require_once("dao/Users.dao.php");
require_once("dao/Emails.dao.php");

class LoginController{
	

	static function testUserCredentials(
		$email, 
		$pass
	){
		Logger::log("Testing user " . $email);
		$email_query = new Emails();
		$email_query->setEmail( $email );
		
		$result = EmailsDAO::search( $email_query );


		if( sizeof($result) == 0)
		{
			//email does not even exist
			return false;
		}


		$this_user 	= UsersDAO::getByPK( $result[0]->getUserId() );

		//test passwords
		return $this_user->getPassword() === md5( $pass ) ;
		
	}



	/**
	 * 
	 * 
	 * 
	 * */
	static function login(
		$email, 
		$google_token = null
	){
		Logger::log("Login");
		
		//google says valid user, look for it in email's table
		$email_query = new Emails();
		$email_query->setEmail( $email );
		
		$result = EmailsDAO::search( $email_query );
		$this_user = null;


		if( sizeof($result) == 0)
		{
			
		

			//create user
			$this_user 	= new Users();
			$this_user->setUsername( $email );
			$this_user->setSolved( 0 );			
			$this_user->setSubmissions( 0 );
			
			
			
			//save this user
			try{
				UsersDAO::save( $this_user );

			}catch(Exception $e){
				die($e);
				return false;

			}
			
			
			//create email
			$this_user_email = new Emails();
			$this_user_email ->setUserId( $this_user->getUserId() );
			$this_user_email ->setEmail( $email );
			
			//save this user
			try{
				EmailsDAO::save( $this_user_email );

			}catch(Exception $e){
				die($e);
				return false;

			}
			
			
			//$this_user->setEmailId( -1 );
						
		}else{

			// he's been here man !
			$this_user 	= UsersDAO::getByPK( $result[0]->getUserId() );
			
			//save user so  his
			//last_access gets updated
			try {
				UsersDAO::save( $this_user );
			} catch(Exception $e) {
				die($e);
				return false;
			}
		}

		$_SESSION["USER_ID"] 	= $this_user->getUserId();
		$_SESSION["EMAIL"] 		= $email;
		$_SESSION["LOGGED_IN"] 	= true;
		
		/**
		 * Ok, passwords match !
		 * Create the auth_token. Auth tokens will be valid for 24 hours.
		 * */
		 $auth_token = new AuthTokens();
		 $auth_token->setUserId( $this_user->getUserId() );

		 /**
		  * auth token consists of:
		  * current time: to validate obsolete tokens
		  * user who logged in:
		  * some salted md5 string: to validate that it was me who actually made this token
		  * 
		  * */
		 $time = time();
		 $auth_str = $time . "-" . $this_user->getUserId() . "-" . md5( OMEGAUP_MD5_SALT . $this_user->getUserId() . $time );
		 $auth_token->setToken($auth_str);

		 try
		 {
		    AuthTokensDAO::save( $auth_token );
		 }
		 catch(Exception $e)
		 {
		    throw new ApiException(ApiHttpErrors::invalidDatabaseOperation(), $e);    
		 }

		 setcookie('auth_token', $auth_str, time()+60*60*24, '/');
		 
		 return true;
	}
	
	
	

	
	/**
	 * 
	 * 
	 * 
	 * */
	static function isLoggedIn(
	){

		return isset($_SESSION["LOGGED_IN"]) && $_SESSION["LOGGED_IN"];
	}

	
	
	
	
	/**
	 * 
	 * 
	 * 
	 * */
	static function logout(
	){
		unset($_SESSION["USER_ID"]);
		unset($_SESSION["EMAIL"]);
		unset($_SESSION["LOGGED_IN"]);		

		setcookie('auth_token', 'deleted', 1, '/');
		
		return true;
	}
	
	
	static function hideEmail($email)
	{
		$s = explode("@", $email);
		return substr( $s[0], 0, strlen($s[0]) - 3 )  . "...@" . $s[1];
	}
	

	
	static function getCurrentUser(
	){
		if(self::isLoggedIn()){
			return UsersDAO::getByPK( $_SESSION["USER_ID"] );
		}else
			return null;
	}
	
	
	
}
