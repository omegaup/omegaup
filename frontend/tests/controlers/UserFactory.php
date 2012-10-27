<?php


/*
 * @author joemmanuel
 */

class UserFactory {
    
   /*
    * Crea un usuario
    * @todo esto debería llamar a usercontroller
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
        
        $contestant = new Users();
        $contestant->setUsername($username);
        $contestant->setPassword(md5($password));
        $contestant->setSolved(0);
        $contestant->setSubmissions(0);
        UsersDAO::save($contestant);                
        
        // Crear el email
        $email = new Emails();
        $email->setEmail($email);
        $email->setUserId($contestant->getUserId());
        EmailsDAO::save($email);
        
        // Guardar el email en el usuario
        $contestant->setMainEmailId($email->getEmailId());
        UsersDAO::save($contestant);
        
        // Save localy clean password
        $contestant->setPassword($password);
        
        return $contestant;        
    }                        
}


