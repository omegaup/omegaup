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
            $contest = ContestsDAO::getByPK( Utils::GetValidPublicContestId());
            $problem = ProblemsDAO::getByPK(Utils::GetValidProblemOfContest(
                 $contest->getContestId()));
            
            RequestContext::set("contest_alias", $contest->getAlias());
            RequestContext::set("problem_alias", $problem->getAlias()); 
        }
        else
        {
            $contest = ContestsDAO::getByPK($contest_id);
            $problem = ProblemsDAO::getByPK($problem_id);
            
            RequestContext::set("contest_alias", $contest->getContestId());
            RequestContext::set("problem_alias", $problem->getProblemId());
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
        $this->assertEquals($contest->getContestId(), $clarification->getContestId());
        $this->assertEquals($problem->getProblemId(), $clarification->getProblemId());                

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
        RequestContext::set("contest_alias", "1213123");
        RequestContext::set("problem_alias", "1213123");
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
          $this->assertEquals("Parameter contest_alias is invalid: Contest is invalid.", $exception_array["error"]);

          // We failed, we're fine.
          return;
        }

        $this->fail("Exception was expected.");        

    }

}