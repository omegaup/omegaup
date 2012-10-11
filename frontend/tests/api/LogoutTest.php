<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

require_once '../Login.php';
require_once '../Logout.php';


class LogoutTest extends PHPUnit_Framework_TestCase
{
    private $sessionManagerMock;
    
    public function setUp()
    {        
        Utils::ConnectToDB();
        
        $this->sessionManagerMock = $this->getMock('SessionManager', array('SetCookie'));
        
        $this->sessionManagerMock->expects($this->any())
                ->method('SetCookie')
                ->will($this->returnValue(true));
    }
    
    public function tearDown() 
    {
        Utils::cleanup();
    }       
    
    public function testValidLogout()
    {                
        // Login
        $auth_token = Utils::LoginAsContestant();
        
        // Valid logout            
        Utils::SetAuthToken($auth_token);
        
        $logoutApi = new Logout($this->sessionManagerMock);
        try
        {        
            $cleanValue = $logoutApi->ExecuteApi();
        }
        catch(ApiException $e)
        {
            $this->fail(var_dump($e->getArrayMessage()));
            
        }
        
        $this->assertNotNull($cleanValue);
        $this->assertArrayHasKey('status', $cleanValue);                
        $this->assertEquals("ok", $cleanValue["status"]);
                
        //Validate that token isn´t there anymore        
        $resultsDB = AuthTokensDAO::search(new AuthTokens(array("auth_token" => $auth_token)));
        if(sizeof($resultsDB) !== 0)
        {
            $this->fail("Expecting not to see auth_token in DB: ". var_dump($resultsDB));
        }                
    }
    
    public function testInvalidAuthToken()
    {        
        // Invalid logout    
        unset($_POST);
        Utils::SetAuthToken("InvalidAuthToken");
        
        $logoutApi = new Logout($this->sessionManagerMock);
        try
        {        
            $cleanValue = $logoutApi->ExecuteApi();
        }
        catch(ApiException $e)
        {
            return;            
        }
        
        $this->fail("Exception was expected: ". var_dump($cleanValue));
        
    }
  
}

?>
