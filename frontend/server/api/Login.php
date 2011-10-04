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


    protected function CheckAuthorization() {
       
        // Bypass authorization
        return true;
    }

    protected function DeclareAllowedRoles() 
    {
        return BYPASS;
    }



    protected function GetRequest() {
                

        $this->request = array(
          new ApiExposedProperty("username", true, POST, array(
              new StringValidator()
          )),
          new ApiExposedProperty("password", true, POST, array(
              new StringValidator()
          ))  
        );
               
    }

    protected function GenerateResponse() {
        
        $username = $_POST["username"];
        $password = $_POST["password"];
                
        
        /**
         * Lets look for this user in the user table.
         * */
        $user_query = new Users();
        $user_query->setUsername( $username );
        
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
                $email_query->setEmail( $username );

                $results = EmailsDAO::search( $email_query );

                if(sizeof($results) == 1){
                        /**
                         * Found his email address. Now lets look for the user
                         * whose email is this.
                         * */
                        $actual_user = UsersDAO::getByPK( $results[0]->getUserId() );


                }else{

                        /**
                         * He is not in the users, nor the emails
                         * */
                        
                       throw new ApiException($this->error_dispatcher->invalidCredentials());
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
               throw new ApiException($this->error_dispatcher->registeredViaThirdPartyNotSupported());
        }
        
        /**
         * Ok, go ahead and check the password. For now its only md5, *with out* salt.
         * */        
        if( 
                $actual_user->getPassword() !== md5($password)
         ){

                /**
                 * Passwords did not match !
                 * */                
               throw new ApiException($this->error_dispatcher->invalidCredentials());
        }
        
        /**
         * 
         * Erase any past auth token from this user.
         * */
        $query_auths = new AuthTokens();
        $query_auths->setUserId( $actual_user->getUserId() );

        $results = AuthTokensDAO::search( $query_auths );

        foreach( $results as $old_token ){
                try{
                    AuthTokensDAO::delete( $old_token );

                }catch(Exception $e){
                   throw new ApiException( $this->error_dispatcher->invalidDatabaseOperation() );    
                }
        }
                
        
        /**
         * Ok, passwords match !
         * Create the auth_token. Auth tokens will be valid for 24 hours.
         * */
         $this->auth_token = new AuthTokens();
         $this->auth_token->setUserId( $actual_user->getUserId() );

         /**
          * auth token consists of:
          * current time: to validate obsolete tokens
          * user who logged in:
          * some salted md5 string: to validate that it was me who actually made this token
          * 
          * */
         $auth_str = time() . "-" . $actual_user->getUserId() . "-" . md5( OMEGAUP_MD5_SALT . $actual_user->getUserId() . time() );

         $this->auth_token->setToken($auth_str);



         try{
                AuthTokensDAO::save( $this->auth_token );

         }catch(Exception $e){

               throw new ApiException( $this->error_dispatcher->invalidDatabaseOperation() );    
         }
                 
         $this->response["auth_token"] = $this->auth_token->getToken();
     
    }

}

?>
