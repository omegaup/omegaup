<?php

/**
 * 
 * 
 * 
 *
 *
 * 
  *
 * */
require_once("ApiHandler.php");
require_once("controllers/users.controller.php");
require_once("controllers/login.controller.php");


class FbLogin extends ApiHandler {


    protected function CheckAuthToken() 
    {       
        // Bypass authorization
        return true;
    }

    protected function RegisterValidatorsToRequest() 
    {                                
        ValidatorFactory::stringNotEmptyValidator()->validate(
                RequestContext::get("facebook_id"), "facebook_id");
        
        ValidatorFactory::stringNotEmptyValidator()->validate(
                RequestContext::get("email"), "email");

        ValidatorFactory::stringNotEmptyValidator()->validate(
                RequestContext::get("name"), "name");
    }

    protected function GenerateResponse() 
    {        
      	$fid 	= RequestContext::get("facebook_id");
        $email 	= RequestContext::get("email");
	    $name 	= RequestContext::get("name");
	

		
		/**
         * Lets look for this user in the user table.
         * */
        $user_query = new Users();
        $user_query->setFacebookUserId( $fid );

        $results = UsersDAO::search( $user_query );
		
		 if(sizeof($results) === 1){
			//this user is already registered
			Logger::log("Found user");	
			$user = $results[0];

			$emailObj = EmailsDAO::getByPK($user->getMainEmailId());
			LoginController::login( $emailObj->getEmail() );
			
			
		}else{
			//user not found, lets insert him
			Logger::log("User does not exist");
			UsersController::registerNewUser($name, $email, NULL, $fid);
			LoginController::login($email);
			
		}
		

		
		$this->addResponse("auth_token", "asdf");  
    }
}
