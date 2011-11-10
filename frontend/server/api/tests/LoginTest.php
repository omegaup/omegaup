<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

require_once '../Login.php';
require_once 'Utils.php';


class LoginTest extends PHPUnit_Framework_TestCase
{
    
    
    
    public function testValidLogin()
    {
        // Sanity cleanup
        Utils::cleanup();
        
        //Connect to DB
        Utils::ConnectToDB();
        
        $_POST["username"] = "user";
        $_POST["password"] = "password";
        
        $loginApi = new Login();        
        $cleanValue = $loginApi->ExecuteApi();
        
        
        $this->assertNotNull($cleanValue);        
        $this->assertArrayHasKey('auth_token', $cleanValue);                
        
    }
    
    
    public function testInvalidPassword()
    {
        // Sanity cleanup
        Utils::cleanup();
        
        //Connect to DB
        Utils::ConnectToDB();
        
        $_POST["username"] = "user";
        $_POST["password"] = "badpass";
        
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
        // Sanity cleanup
        Utils::cleanup();
        
        //Connect to DB
        Utils::ConnectToDB();
        
        $_POST["username"] = "baduser";
        $_POST["password"] = "pass";
        
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
        
        // Sanity cleanup
        Utils::cleanup();
        
        //Connect to DB
        Utils::ConnectToDB();
        
        $_POST["username"] = "user";
        $_POST["password"] = "password";
        
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
