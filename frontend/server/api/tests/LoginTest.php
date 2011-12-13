<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

require_once '../Login.php';
require_once 'Utils.php';


class LoginTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {        
        Utils::ConnectToDB();
    }
    
    public function tearDown() 
    {
        Utils::cleanup();
    }    
    
    public function testValidLogin()
    {        
        RequestContext::set("username", Utils::GetContestantUsername());
        RequestContext::set("password", Utils::$contestant->getPassword());
        
        $loginApi = new Login();        
        try
        {
            $cleanValue = $loginApi->ExecuteApi();
        }
        catch( ApiException $e )
        {
            var_dump($e->getArrayMessage());            
            $this->fail("User should be able to login");
        }
        
        
        $this->assertNotNull($cleanValue);        
        $this->assertArrayHasKey('auth_token', $cleanValue);                
        
    }
    
    
    public function testInvalidPassword()
    {
        
        RequestContext::set("username", Utils::GetContestantUsername());
        RequestContext::set("password", "badpass");
        
        $loginApi = new Login();
        
        try
        {        
            $cleanValue = $loginApi->ExecuteApi();
        }
        catch(ApiException $e)
        {
            $arr = $e->getArrayMessage();
            
            $this->assertNotNull($arr);
            $this->assertArrayHasKey('error', $arr);   
            $this->assertEquals("Username or password is wrong. Please check your credentials", $arr["error"]);            
        
            // All fine :)
            return;
        }
               
        $this->fail('Unexpected exception thrown.');        
    }
    
    
    public function testInvalidUser()
    {                
        RequestContext::set("username", "baduser");
        RequestContext::set("password", Utils::$contestant->getPassword());
        
        $loginApi = new Login();
        
        try
        {        
            $cleanValue = $loginApi->ExecuteApi();
        }
        catch(ApiException $e)
        {
            $arr = $e->getArrayMessage();
            
            $this->assertNotNull($arr);
            $this->assertArrayHasKey('error', $arr);   
            $this->assertEquals("Username or password is wrong. Please check your credentials", $arr["error"]);            
        
            // All fine :)
            return;
        }
               
        $this->fail('Unexpected exception thrown.');        
    }
    
    public function testTwoValidLogins()
    {
        
        RequestContext::set("username", Utils::GetContestantUsername());
        RequestContext::set("password", Utils::$contestant->getPassword());        
        
        $loginApi = new Login();        
        try
        {        
            $cleanValue = $loginApi->ExecuteApi();
        }
        catch(ApiException $e)
        {
            $msg = $e->getArrayMessage();
            
            $this->fail('Unexpected exception thrown.'. $msg["error"] );        
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
