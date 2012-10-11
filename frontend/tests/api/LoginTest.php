<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

require_once '../Login.php';
require_once 'Utils.php';

require_once(SERVER_PATH . '/libs/SessionManager.php');

class LoginTest extends PHPUnit_Framework_TestCase
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
    
    public function testValidLogin()
    {        
        RequestContext::set("username", Utils::GetContestantUsername());
        RequestContext::set("password", Utils::$contestant->getPassword());
        
        $loginApi = new Login($this->sessionManagerMock);        
        try
        {
            $cleanValue = $loginApi->ExecuteApi();
        }
        catch( ApiException $e )
        {            
            var_dump($e->getArrayMessage());            
            if(!is_null($e->getWrappedException()))
            {
                var_dump($e->getWrappedException()->getMessage());
            }
            $this->fail("User should be able to login");
        }
        
        
        $this->assertNotNull($cleanValue);        
        $this->assertArrayHasKey('auth_token', $cleanValue);                
        
    }
    
    
    public function testInvalidPassword()
    {
        
        RequestContext::set("username", Utils::GetContestantUsername());
        RequestContext::set("password", "badpass");
        
        $loginApi = new Login($this->sessionManagerMock);
        
        try
        {        
            $cleanValue = $loginApi->ExecuteApi();
        }
        catch(ApiException $e)
        {
            $arr = $e->getArrayMessage();
            
            $this->assertNotNull($arr);
            $this->assertArrayHasKey('error', $arr);   
            $this->assertEquals("Username or password is wrong. Please check your credentials.", $arr["error"]);            
        
            // All fine :)
            return;
        }
               
        $this->fail('Unexpected exception thrown.');        
    }
    
    
    public function testInvalidUser()
    {                
        RequestContext::set("username", "baduser");
        RequestContext::set("password", Utils::$contestant->getPassword());
        
        $loginApi = new Login($this->sessionManagerMock);
        
        try
        {        
            $cleanValue = $loginApi->ExecuteApi();
        }
        catch(ApiException $e)
        {
            $arr = $e->getArrayMessage();
            
            $this->assertNotNull($arr);
            $this->assertArrayHasKey('error', $arr);   
            $this->assertEquals("Username or password is wrong. Please check your credentials.", $arr["error"]);            
        
            // All fine :)
            return;
        }
               
        $this->fail('Unexpected exception thrown.');        
    }
    
    public function testTwoValidLogins()
    {
        
        RequestContext::set("username", Utils::GetContestantUsername());
        RequestContext::set("password", Utils::$contestant->getPassword());        
        
        $loginApi = new Login($this->sessionManagerMock);        
        try
        {        
            $cleanValue = $loginApi->ExecuteApi();
        }
        catch(ApiException $e)
        {
            $msg = $e->getArrayMessage();            
            $this->fail('Unexpected exception thrown.'. $msg["error"] );              
            var_dump($e->getWrappedException()->getMessage());            
        }
                
        try
        {        
            $cleanValue = $loginApi->ExecuteApi();
        }
        catch(ApiException $e)
        {
            $msg = $e->getArrayMessage();
            
            $this->fail('Second login failed, it should be bypassed. ' . $msg["error"]);                                
        }
               
        return;        
    }
}

?>
