<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

define('WHOAMI', 'API');
require_once '../../inc/bootstrap.php';
require_once '../ShowClarification.php';
require_once '../NewClarification.php';
require_once 'Utils.php';



class NewClarificationTest extends PHPUnit_Framework_TestCase
{
    
    public function testCreateValidClarificatoin()
    {
        //Connect to DB
        Utils::ConnectToDB();
        
        // Login as contestant
        $auth_token = Utils::LoginAsContestant();
        
        // Set request for valid clarification
        $_POST["contest_id"] = Utils::GetValidPublicContestId();
        $_POST["problem_id"] = Utils::GetValidProblemOfContest($_POST["contest_id"]);
        $_POST["message"] = Utils::RandomString();
        Utils::SetAuthToken($auth_token);
        
        // Execute API
        $newClarification = new NewClarification();
        try
        {
            $returnValue = $newClarification->ExecuteApi();
        }
        catch(ApiException $e)
        {
            var_dump( $e->getArrayMessage() );
            throw $e;
        }
        
        // Assert status of new contest
        self::assertEquals("ok", $returnValue["status"]);
        
        // Clean requests
        Utils::cleanup();
        Utils::Logout($auth_token);        
    }
    
    
    public function testInvalidContestId()
    {
        
      //Connect to DB
      Utils::ConnectToDB();  

      // Login as contestant
      $auth_token = Utils::LoginAsContestant();  

      // Set request for valid clarification
      $_POST["contest_id"] = 1213123;
      $_POST["problem_id"] = 1213123;
      $_POST["message"] = Utils::RandomString();
      Utils::SetAuthToken($auth_token);

      // Execute API
      $newClarification = new NewClarification();
      try
      {
          $returnValue = $newClarification->ExecuteApi();
      }
      catch(ApiException $e)            
      {
          // Validate error output
          $exception_array = $e->getArrayMessage();          
          $this->assertEquals("Validation failed for parameter contest_id: Validation failed.", $exception_array["error"]);
          
          // We failed, we're fine.
          return;
      }

      $this->fail("Exception was expected.");
      
      // Clean requests
      Utils::cleanup();
      Utils::Logout($auth_token);  

    }

}