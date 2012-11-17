<?php


/**
 * @author joemmanuel
 */

class UserFactory {
    
   /**
    * Crea un usuario
    * 
    */
    public static function createUser($username = null, $password = null, $email = null){
        
        if (is_null($username)){
            $username = Utils::CreateRandomString();
        }
        
        if (is_null($password)){
            $password = Utils::CreateRandomString();
        }            
        
        if (is_null($email)){
            $email = Utils::CreateRandomString()."@mail.com";
        }
        
		// Set context
		$_REQUEST["username"] = $username;
		$_REQUEST["password"] = $password;
		$_REQUEST["email"] = $email;
		
		// Call api
		$_SERVER["REQUEST_URI"] = "/api/user/create";		
		$response = json_decode(ApiCallerMock::httpEntryPoint(), true);	
                
		// Get user from db
		$user = UsersDAO::FindByUsername($username);
		
        // Set password in plaintext
        $user->setPassword($password);
        return $user;
    }                        
}


