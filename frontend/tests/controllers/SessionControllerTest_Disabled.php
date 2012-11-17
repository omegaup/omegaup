<?php

require_once SERVER_PATH . 'controllers/users.controller.php';
require_once SERVER_PATH . 'controllers/sesion.controller.php';

require_once 'Utils.php';
require_once 'UsersFactory.php';

/*
 *  Tests de LoginController
 * 
 */
class SessionControllerTest extends PHPUnit_Framework_TestCase
{    
    
    /*
     * Revisar que el login haya dejado correctamente el estado
     * 
     */
    private function assertLogin(Users $user, $auth_token = null){
        // Checar auth_tokens
        $authTokenKey = new AuthTokens(array(
           "user_id" => $user->getUserId() 
        ));
        $auth_token_bd = AuthTokensDAO::search($authTokenKey);       
        
        // Checar que tenemos al menos 1 token
        $this->assertEquals(1, count($auth_token_bd));        
        
        if (!is_null($auth_token)){
            $this->assertEquals($auth_token, $auth_token_bd[0]->getToken());
        }
        
        
        // @todo check last access time
    }
    
    /*
     * Scenario: Login nativo positivo por usuario
     * Expected: Login no avienta excepción, el authtoken se crea
     */
    public function testLoginNativeByUser(){        
        // Crear el usuario        
        $user = UsersFactory::createUser();    
        
        $sc = new SesionController();
        
        $auth_token = $sc->NativeLogin($user->getUsername(), $user->getPassword(), true);        
        
        $this->assertLogin($user, $auth_token);
    }
    
    
    /*
     * Scenario: Login nativo por email
     * Expected: Login debe aventar excepción
     */
    public function testLoginNativeByEmail(){
        // Crear el usuario
        $user = UsersFactory::createUser();        
        
        // Obtener el email del usuario
        $email = EmailsDAO::getByPK($user->getMainEmailId());
        
        $sc = new SesionController();
        
        // Login por email
        $auth_token = $sc->NativeLogin($user->getUsername(), $user->getPassword(), true);        
        
        $this->assertLogin($user, $auth_token);
    }        
    
    /*
     * Scenario: Dos logins consecutivos del mismo usuario rápidamente
     * Expected: Los authtokens deben ser diferentes
     */
    public function testLoginNative2TimesQuickly(){
        // Crear el usuario
        $user = UsersFactory::createUser();
        
        $sc = new SesionController();       
        
        // Login
        $auth_token_1 = $sc->NativeLogin($user->getUsername(), $user->getPassword(), true);     
        $auth_token_2 = $sc->NativeLogin($user->getUsername(), $user->getPassword(), true);        
        
        $this->assertLogin($user, $auth_token);
        $this->assertNotEquals($auth_token_1, $auth_token_2);
    }
    
     /*
     * Scenario: Dos logins consecutivos del mismo usuario rápidamente
     * Expected: Los authtokens deben ser diferentes
     */
    public function testLoginNative2Times(){
        // Crear el usuario
        $user = UsersFactory::createUser();
        
        $sc = new SesionController();       
        
        // Login
        $auth_token_1 = $sc->NativeLogin($user->getUsername(), $user->getPassword(), true);   
        sleep(1);
        $auth_token_2 = $sc->NativeLogin($user->getUsername(), $user->getPassword(), true);        
        
        $this->assertLogin($user, $auth_token);        
    }
    
    
    /*
     * Scenario: Login con username incorrecto
     * Expected: login debe fallar (tirar una excepción)
     */
    public function testLoginBadUsername(){        
        
        $user = UsersFactory::createUser();
        $sc = new SesionController();
        
        try{        
            $auth_token = $sc->NativeLogin("badusername", $user->getPassword(), true);                        
        }catch (ForbiddenAccessException $e){
            return;
        }
        
        $this->fail("Usuario con user inexistente pudo logearse.");
    }
    
    /*
     * Scenario: Login con password incorrecto
     * Expected: login debe fallar (tirar una excepción)
     */
    public function testLoginBadPassword(){        
        
        $user = UsersFactory::createUser();
        $sc = new SesionController();
        
        try{        
            $auth_token = $sc->NativeLogin($user->getUsername(), "badpassword", true);                        
        }catch (ForbiddenAccessException $e){
            return;
        }
        
        $this->fail("Usuario con user inexistente pudo logearse.");
    }
    
    
}

