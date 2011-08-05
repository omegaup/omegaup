<?php


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
		
		//ok usuario valido segun google, vamos a buscar su correo electronico
		$u = new Users();
		$u->setEmail($email);
		
		$res = UsuarioDAO::search( $u );
		
		if(sizeof($res) == 0){
			//its his first time !
			
		}else{
			//coming back user !
			$u = $res[0];
			
			//@todo, this dont work right now
			//$u->setLastAccess( time() );
		}


		//save for new user so he can be in 
		//database, and for old user so his
		//last_access gets updated
		try{
			UsuarioDAO::save( $u );
			
		}catch(Exception $e){
			
			/* <REMOVE FROM PRODUCTION> */	echo $e; /* </REMOVE FROM PRODUCTION> */
			return false;
			
		}
		
		// BUG FIX para el BUG de webframework !!!
		$b_fix_foo = new Usuario();
		$b_fix_foo->setEmail($email);
		$r_fix_bar = UsuarioDAO::search( $b_fix_foo );
		$u = $r_fix_bar[0];
		// BUG FIX para el BUG de webframework !!!
		
		$_SESSION["USER_ID"] 	= $u->getUserId();
		$_SESSION["EMAIL"] 		= $email;
		$_SESSION["LOGGED_IN"] 	= true;
		
		return true;
	}
	
	
	
	
	static function isUserReadyToCommit(
		$user_id
	){
		$u = UsuarioDAO::getByPK($user_id);
		
		return $u->getSvnPass() !== NULL;
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