<?php

require_once SERVER_PATH . 'controllers/login.controller.php';

require_once 'Utils.php';
require_once 'UserFactory.php';

/*
 *  Tests de LoginController
 * 
 */
class LoginControllerTest extends PHPUnit_Framework_TestCase
{
    private $_sessionManagerMock;
    
    /*
     *  setUp
     *  Configuración del sessionManager para evitar la llamada a session_start
     * directamente
     */
    public function setUpSessionMocks()
    {                                
        // Capturar la llamada a sessionStart
        $this->_sessionManagerMock = $this->getMock('SessionManager', array('sessionStart', 'setCookie'));
        
        // Si se llama, que sea 1 vez por login
        $this->_sessionManagerMock->expects($this->once())
                ->method('sessionStart')
                ->will($this->returnValue(true));
        
        $this->_sessionManagerMock->expects($this->once())
                ->method('setCookie')
                ->will($this->returnValue(true));
        
        // Inyectar session manager
        LoginController::setSessionManager($this->_sessionManagerMock);
    }
    
    /*
     * Scenario: Login nativo positivo por usuario
     * Expected: Login no avienta excepción, el authtoken se crea
     */
    public function testLoginNativeByUser(){        
        // Crear el usuario        
        $user = UserFactory::createUser();        
        $this->setUpSessionMocks();
        
        LoginController::login($user->getUsername());
        
        // Checar auth_tokens
        $authTokenKey = new AuthTokens(array(
           "user_id" => $user->getUserId() 
        ));
        $authToken = AuthTokensDAO::search($authTokenKey);
        
        $this->assertEquals(1, count($authToken));
    }
    
    
    /*
     * Scenario: Login nativo por email
     * Expected: Login debe aventar excepción
     */
    public function testLoginNativeByEmail(){
        // Crear el usuario
        $user = UserFactory::createUser();
        $this->setUpSessionMocks();
        
        // Obtener el email del usuario
        $email = EmailsDAO::getByPK($user->getMainEmailId());
        
        // Login por email
        LoginController::login($email->getEmail());
        
        // Checar auth_tokens
        $authTokenKey = new AuthTokens(array(
           "user_id" => $user->getUserId() 
        ));
        $authToken = AuthTokensDAO::search($authTokenKey);
        
        $this->assertEquals(1, count($authToken));
    }        
    
    /*
     * Scenario: Dos logins consecutivos del mismo usuario 
     * Expected: Sólo dejan 1 authtoken
     */
    public function testLoginNative2Times(){
        // Crear el usuario
        $user = UserFactory::createUser();
        
        // Ajustar el session mock para que se pueda llamar 2 veces
        $doubleSessionManagerMock = $this->getMock('SessionManager', array('sessionStart', 'setCookie'));
        $doubleSessionManagerMock->expects($this->exactly(2))
                ->method('sessionStart')
                ->will($this->returnValue(true));
        
        $doubleSessionManagerMock->expects($this->exactly(2))
                ->method('setCookie')
                ->will($this->returnValue(true));
        LoginController::setSessionManager($doubleSessionManagerMock);
        
        // Login
        LoginController::login($user->getUsername());
        LoginController::login($user->getUsername());
        
        // Checar auth_tokens
        $authTokenKey = new AuthTokens(array(
           "user_id" => $user->getUserId() 
        ));
        $authToken = AuthTokensDAO::search($authTokenKey);
        
        $this->assertEquals(1, count($authToken));
    }
    
    
    /*
     * Scenario: Login con username incorrecto
     * Expected: login debe fallar (tirar una excepción)
     */
    public function testLoginBadUsername(){
        $this->setUpSessionMocks();
        
        try{        
            LoginController::login("esteusernoexiste");                
        }catch (ApiException $e)
        {
            
        }
        
        $this->fail("Usuario con user inexistente pudo logearse.");
    }
    
}

