<?php

require_once SERVER_PATH . 'controllers/users.controller.php';

/**
 * @author joemmanuel
 */

class UsersFactory {
    
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
        
        
        $uc = new UserController();
        $user = new Users($uc->Create($email, $username, $password));                       
        
        // Set password in plaintext
        $user->setPassword($password);
        return $user;
    }                        
}


