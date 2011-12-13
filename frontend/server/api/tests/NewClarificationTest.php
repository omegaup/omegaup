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
    
    public function setUp()
    {        
        Utils::ConnectToDB();
    }
    
    public function tearDown() 
    {
        Utils::cleanup();
    }  
    
    public function testCreateValidClarification($contest_id = null, $problem_id = null)
    {                
        // Login as contestant
        $auth_token = Utils::LoginAsContestant();
        
        // Set request for valid clarification
        if(is_null($contest_id) && is_null($problem_id))
        {
            RequestContext::set("contest_id", Utils::GetValidPublicContestId());
            RequestContext::set("problem_id", Utils::GetValidProblemOfContest(
                 RequestContext::get("contest_id")));
        }
        else
        {
            RequestContext::set("contest_id", $contest_id);
            RequestContext::set("problem_id", $problem_id);
        }        
        RequestContext::set("message", Utils::CreateRandomString());
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
        $this->assertEquals(RequestContext::get("message"), $clarification->getMessage());
        $this->assertEquals(RequestContext::get("contest_id"), $clarification->getContestId());
        $this->assertEquals(RequestContext::get("problem_id"), $clarification->getProblemId());                

        // Clean requests
        Utils::cleanup();        
        
        // Differentiate two consecutive clarifications by time
        sleep(1);
        
        return $returnValue["clarification_id"];
        
    }
    
    
    public function testInvalidContestId()
    {                

        // Login as contestant
        $auth_token = Utils::LoginAsContestant();  

        // Set request for valid clarification
        RequestContext::set("contest_id", 1213123);
        RequestContext::set("problem_id", 1213123);
        RequestContext::set("message", Utils::CreateRandomString());
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
                             
          $this->assertEquals("HTTP/1.1 400 BAD REQUEST", $exception_array["header"]);
          $this->assertEquals("Parameter contest_id is invalid: Contest requested is invalid.", $exception_array["error"]);

          // We failed, we're fine.
          return;
        }

        $this->fail("Exception was expected.");        

    }

}