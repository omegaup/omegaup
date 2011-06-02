<?php


require_once( "dao/Users.dao.php" );	
require_once( "dao/Users_Badges.dao.php");
require_once( "dao/User_Roles.dao.php");
require_once( "dao/Permissions.dao.php");

class UsersController {

	public static function registerNewUser($name, $email, $pwd, $print_response = null){
		//user exists ?
		if(UsersController::searchUserByEmail($email) != null){
			//it exists !
			if($print_response == JSON){
				echo '{ "success" : false, "reason" : "This email is already registered" }';
			}
			
			return false;
		}
		
		//register the user
		$new_user = new Users();
		$new_user->setUsername($name);
		$new_user->setPassword($pwd);
		$new_user->setEmail($email);
		$new_user->setName($name);
		$new_user->setSolved(0);
		$new_user->setSubmissions(0);
		try{
			UsersDAO::save( $new_user );
		}catch(Exception $e){
			
			if($print_response == JSON){
				echo '{ "success" : false, "reason" : 500, "e" : "'. $e .'" }';
			}
			
			return false;			
		}
		
		
		//now that he has been registered, lets log him in
		
									
		if($print_response == JSON){
			echo '{ "success" : true}';
		}
		
		return true;

	}


	public static function registerNewUserWithFacebook(){
		
	}
	
	
	public static function searchUserByEmail($email){
		$u = new Users();
		$u->setEmail($email);
		$res = UsersDAO::search($u);
		
		if(sizeof($res) == 0){
			return null;
		}else{
			return $res[0];
		}
		
	}
	
	public static function getUserList(){
		
	}

	public static function getRankList(){
		
	}
	
  /**
    *
    */
  public static function addEmail() {
  }
  /**
    *
    */
  public static function setPrimaryEmail() {
  }
  /**
    *
    */
  public static function addEmail() {
  }
  /**
    *
    */
  public static function requestPasswordReset() {
  }
  /**
    * Change a user's password. In order to change it, the user must
    * provide his/her current password should be given or a password
    * reset token.
    */
  public static function setPassword(
      $user_id
    , $current_password // or reset token
    , $new_password
  ) {
  }
}
