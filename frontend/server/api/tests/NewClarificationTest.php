<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

require_once '../ShowClarification.php';
require_once '../NewClarification.php';

require_once 'Utils.php';


class NewClarificationTest extends PHPUnit_Framework_TestCase
{
    
    public function testCreateValidClarification($contest_id = null, $problem_id = null)
    {
        //Connect to DB
        Utils::ConnectToDB();
        
        // Login as contestant
        $auth_token = Utils::LoginAsContestant();
        
        // Set request for valid clarification
        if(is_null($contest_id) && is_null($problem_id))
        {
            $_POST["contest_id"] = Utils::GetValidPublicContestId();
            $_POST["problem_id"] = Utils::GetValidProblemOfContest($_POST["contest_id"]);
        }
        else
        {
            $_POST["contest_id"] = $contest_id;
            $_POST["problem_id"] = $problem_id;
        }        
        $_POST["message"] = Utils::CreateRandomString();
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
        $this->assertArrayHasKey("clarification_id", $returnValue);
        
        // Verify that clarification was inserted in the database
        $clarification = ClarificationsDAO::getByPK($returnValue["clarification_id"]);
        
        // Verify our retreived clarificatoin
        $this->assertNotNull($clarification);
        $this->assertEquals($_POST["message"], $clarification->getMessage());
        $this->assertEquals($_POST["contest_id"], $clarification->getContestId());
        $this->assertEquals($_POST["problem_id"], $clarification->getProblemId());                

        // Clean requests
        Utils::cleanup();
        Utils::Logout($auth_token);        
        
        // Differentiate two consecutive clarifications by time
        sleep(1);
        
        return $returnValue["clarification_id"];
        
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
        $_POST["message"] = Utils::CreateRandomString();
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