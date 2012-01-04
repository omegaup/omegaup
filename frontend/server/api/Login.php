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

class Login extends ApiHandler {


    protected function CheckAuthToken() 
    {       
        // Bypass authorization
        return true;
    }

    protected function RegisterValidatorsToRequest() 
    {                                
        //ValidatorFactory::stringNotEmptyValidator()->validate(
        //        RequestContext::get("facebook_id"), "facebook_id");
        
        ValidatorFactory::stringNotEmptyValidator()->validate(
                RequestContext::get("username"), "username");

        ValidatorFactory::stringNotEmptyValidator()->validate(
                RequestContext::get("password"), "password");

    }

    protected function GenerateResponse() 
    {        
        $username = RequestContext::get("username");
        $password = RequestContext::get("password");
                        
        /**
         * Lets look for this user in the user table.
         * */
        $user_query = new Users();
        $user_query->setUsername( $username );        
        $results = UsersDAO::search( $user_query );

        if(sizeof($results) === 1)
        {
            /**
             * Found him !
             * */               
            $actual_user = $results[0];                

        }
        else
        {
            /**
             * He was not ther, maybe he sent his email instead.
             * */	
            $email_query = new Emails();
            $email_query->setEmail( $username );
            $results = EmailsDAO::search( $email_query );

            if(sizeof($results) == 1)
            {
                /**
                 * Found his email address. Now lets look for the user
                 * whose email is this.
                 * */
                $actual_user = UsersDAO::getByPK( $results[0]->getUserId() );
            }
            else
            {
                /**
                 * He is not in the users, nor the emails
                 * */
               throw new ApiException(ApiHttpErrors::invalidCredentials());
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
               throw new ApiException(ApiHttpErrors::registeredViaThirdPartyNotSupported());
        }
        
        /**
         * Ok, go ahead and check the password. For now its only md5, *with out* salt.
         * */        
        if( $actual_user->getPassword() !== md5($password) )
        {
            /**
             * Passwords did not match !
             * */                       
           throw new ApiException(ApiHttpErrors::invalidCredentials());
        }
        
        /**
         * 
         * Erase any past auth token from this user.
         * */
        $query_auths = new AuthTokens();
        $query_auths->setUserId( $actual_user->getUserId() );
        $results = AuthTokensDAO::search( $query_auths );

        foreach( $results as $old_token )
        {
            try
            {
                AuthTokensDAO::delete( $old_token );

            }
            catch(Exception $e)
            {
               throw new ApiException(ApiHttpErrors::invalidDatabaseOperation(), $e);    
            }
        }
                        
        /**
         * Ok, passwords match !
         * Create the auth_token. Auth tokens will be valid for 24 hours.
         * */
         $this->_auth_token = new AuthTokens();
         $this->_auth_token->setUserId( $actual_user->getUserId() );

         /**
          * auth token consists of:
          * current time: to validate obsolete tokens
          * user who logged in:
          * some salted md5 string: to validate that it was me who actually made this token
          * 
	  * */
	 $time = time();
         $auth_str = $time . "-" . $actual_user->getUserId() . "-" . md5( OMEGAUP_MD5_SALT . $actual_user->getUserId() . $time );
         $this->_auth_token->setToken($auth_str);

         try
         {
            AuthTokensDAO::save( $this->_auth_token );
         }
         catch(Exception $e)
         {
            throw new ApiException(ApiHttpErrors::invalidDatabaseOperation(), $e);    
	 }

	 setcookie('auth_token', $auth_str, time()+60*60*24, '/');
          
         // Add token to response
         $this->addResponse("auth_token", $this->_auth_token->getToken());         
    }
}

?>
