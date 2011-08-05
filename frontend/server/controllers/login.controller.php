<?php

require_once("dao/Users.dao.php");
require_once("dao/Emails.dao.php");

class LoginController{
	
	
	
	
	
	
	
	/**
	 * 
	 * 
	 * 
	 * */
	static function login(
		$email, 
		$google_token
	){
		
		//google says valid user, look for it in email's table
		$email_query = new Emails();
		$email_query->setEmail( $email );
		
		$result = EmailsDAO::search( $email_query );


		if( sizeof($result) == 0)
		{
			
			//first timer !

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
			try{
				UsersDAO::save( $this_user );

			}catch(Exception $e){
				
				die($e);
				return false;

			}
			
		}
		

		$_SESSION["USER_ID"] 	= $this_user->getUserId();
		$_SESSION["EMAIL"] 		= $email;
		$_SESSION["LOGGED_IN"] 	= true;

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
	}
	
	
	static function hideEmail($email)
	{
		$s = explode("@", $email);
		return substr( $s[0], 0, strlen($s[0]) - 3 )  . "...@" . $s[1];
	}
	

	
	static function getCurrentUser(
	){
		if(self::isLoggedIn()){
			return UsuarioDAO::getByPK( $_SESSION["USER_ID"] );
		}else
			return null;
	}
	
	
	
}