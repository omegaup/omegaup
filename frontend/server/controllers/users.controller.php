<?php



class UsersController {

	public static function registerNewUser( $name, $email, $pwd = null, $fid = null ){
		
		Logger::log("Registering new user...");
		
		//user exists ?
		if(!is_null( $existing_user = UsersDAO::searchUserByEmail( $email ) ) )
		{
			//@todo throw correct Exception
			//throw new Exception("This email is already registered.");
			Logger::warn( "Email " . $email . " is already registered, merging accounts..." );
			
			if(!is_null($fid)){
				//if we got here, means the user has facebook_user_id = NULL
				//lets update that
				$existing_user->setFacebookUserId( $fid );				
			}

			try{
				UsersDAO::save($existing_user);
				
			}catch(Exception $e){
				throw new ApiException(ApiHttpErrors::invalidDatabaseOperation(), $e);
				
			}
			
			
			Logger::log("User was merged successfully.");
			return $existing_user;
			
		}

		
		DAO::transBegin();

		//register the user
		$new_user = new Users();
		$new_user->setUsername($email);
		
		if(!is_null($pwd))
			$new_user->setPassword(md5($pwd));
		
		if(!is_null($fid))
			$new_user->setFacebookUserId($fid);
		
		$new_user->setName($name);
		$new_user->setSolved(0);
		$new_user->setSubmissions(0);

		try{
			Logger::log("Saving new user record....");
			UsersDAO::save( $new_user );

		}catch(Exception $e){
			
			DAO::transRollback();
			throw new ApiException(ApiHttpErrors::invalidDatabaseOperation(), $e);

		}
		
		Logger::log("Saving new user's email...");
		//insert his email as primary
		$mail = new Emails(  );
		$mail->setEmail( $email );
		$mail->setUserId( $new_user->getUserId() );


		try{
			EmailsDAO::save( $mail );

		}catch(Exception $e){
			DAO::transRollback();
			throw new ApiException(ApiHttpErrors::invalidDatabaseOperation(), $e);

		}

		//insert the new email id into the user
		Logger::log("New users email id is " . $mail->getEmailId() . ", associating with user as main email..." );
		$new_user->setMainEmailId( $mail->getEmailId() );

		try{
			UsersDAO::save( $new_user );

		}catch(Exception $e){
			DAO::transRollback();
			throw new ApiException(ApiHttpErrors::invalidDatabaseOperation(), $e);

		}
		
		Logger::log("User successfully registered !");
		
		$id = $new_user->getUserId();
		
		DAO::transEnd();
		
		return $new_user;

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
