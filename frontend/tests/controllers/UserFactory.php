<?php


/**
 * UserFactory
 * 
 * This class is a helper for creating users as needed in other places
 * 
 * @author joemmanuel
 */
class UserFactory {
    
   /**
    * Creates a native user in Omegaup and returns the DAO populated
    * 
    * @param string $username optional
    * @param string $password optional
    * @param string $email optional
    * @return user (DAO)
    */
    public static function createUser($username = null, $password = null, $email = null) {
        
		// If data is not provided, generate it randomly
        if (is_null($username)) {
            $username = Utils::CreateRandomString();
        }
        
        if (is_null($password)) {
            $password = Utils::CreateRandomString();
        }            
        
        if (is_null($email)) {
            $email = Utils::CreateRandomString()."@mail.com";
        }
        
		// Populate a new Request to pass to the API
		$r = new Request(array(
				"username" => $username,
				"password" => $password,
				"email" => $email)
				);
		
		// Call the API
		$response = UserController::apiCreate($r);
		
		// If status is not OK
		if (strcasecmp($response["status"], "ok") !== 0) {
			throw new Exception ("createUser failed");
		}
                
		// Get user from db
		$user = UsersDAO::FindByUsername($username);
		
        // Set password in plaintext
        $user->setPassword($password);
        return $user;
    }                        
}


