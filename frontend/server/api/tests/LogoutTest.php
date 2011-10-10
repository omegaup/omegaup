<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

define('WHOAMI', 'API');
require_once '../../inc/bootstrap.php';
require_once '../Login.php';
require_once '../Logout.php';



class LogoutTest extends PHPUnit_Framework_TestCase
{
        
    
    public function testValidLogout()
    {
        
        // Valid login
        $_POST["username"] = "user";
        $_POST["password"] = "password";
        
        $loginApi = new Login();        
        $cleanValue = $loginApi->ExecuteApi();
        
        $this->assertNotNull($cleanValue);        
        $this->assertArrayHasKey('auth_token', $cleanValue);                
        
        $auth_token = $cleanValue["auth_token"];
        
        
        // Valid logout    
        unset($_POST);
        $_POST["auth_token"] = $auth_token;
        
        $logoutApi = new Logout();
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
        $_POST["auth_token"] = "InvalidtokenD:";
        
        $logoutApi = new Logout();
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
