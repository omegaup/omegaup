<?php



class UsersController {

	public static function registerNewUser( $name, $email, $pwd ){
		//user exists ?
		if(!is_null( UsersDAO::searchUserByEmail( $email )))
		{
			throw new Exception("Este email ya ha sido registrado.");
		}


		

		
		DAO::transBegin();

		//register the user
		$new_user = new Users();
		$new_user->setUsername($email);
		$new_user->setPassword(md5($pwd));
		$new_user->setName($name);
		$new_user->setSolved(0);
		$new_user->setSubmissions(0);

		try{
			UsersDAO::save( $new_user );

		}catch(Exception $e){
			
			DAO::transRollback();
			Logger::error($e);
			throw new Exception("Error");

		}
		
		//insert his email as primary
		$mail = new Emails(  );
		$mail->setEmail( $email );
		$mail->setUserId( $new_user->getUserId() );


		try{
			EmailsDAO::save( $mail );

		}catch(Exception $e){
			DAO::transRollback();
			Logger::error($e);
			throw new Exception("Error");

		}

		//insert the new email id into the user
		$new_user->setMainEmailId( $mail->getEmailId() );

		try{
			UsersDAO::save( $new_user );

		}catch(Exception $e){
			
			DAO::transRollback();
			Logger::error($e);
			throw new Exception("Error");

		}
		DAO::transEnd();
		return $new_user->getUserId();

	}



	public static function registerNewUserWithFacebook(){
		
	}
	
	
	public static function searchUserByEmail( $email )
	{
		$email_query = new Emails();
		$new_user->setMainEmailId( $mail->getEmailId() );


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
