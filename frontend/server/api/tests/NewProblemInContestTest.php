<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

require_once '../NewProblemInContest.php';

require_once 'Utils.php';

class NewProblemInContestTest extends PHPUnit_Framework_TestCase
{
    
    public function setUp()
    {        
        Utils::ConnectToDB();
    }
    
    public function tearDown() 
    {
        Utils::cleanup();
    }
    
    private static function setValidContext($contest_id = NULL)
    {        
        // Set context
        if(is_null($contest_id))
        {
            $contest = ContestsDAO::getByPK(Utils::GetValidPublicContestId());
            RequestContext::set("contest_alias", $contest->getAlias());
        }
        else
        {
            $contest = ContestsDAO::getByPK($contest_id);
            RequestContext::set("contest_alias", $contest->getAlias());
        }
        RequestContext::set("title", Utils::CreateRandomString());
        RequestContext::set("alias", substr(Utils::CreateRandomString(), 0, 10));
        RequestContext::set("author_id", Utils::GetProblemAuthorUserId());
        RequestContext::set("validator", "token");
        RequestContext::set("time_limit", 5000);
        RequestContext::set("memory_limit", 32000);        
        RequestContext::set("source", "<p>redacción</p>");
        RequestContext::set("order", "normal");
        RequestContext::set("points", 1);
    }
    
    public function testCreateValidProblem($contest_id = NULL)
    {        
        
        // Set valid context for problem creation
        $contest_id = is_null($contest_id) ? Utils::GetValidPublicContestId() : $contest_id;
        self::setValidContext($contest_id);
     
        // Login as judge
        $auth_token = Utils::LoginAsContestDirector();        
        
        // Execute API
        Utils::SetAuthToken($auth_token);
        $newProblemInContest = new NewProblemInContest();
        
        try
        {
            $return_array = $newProblemInContest->ExecuteApi();
        }
        catch(ApiException $e)
        {
            var_dump($e->getArrayMessage());
            $this->fail("Unexpected exception");
        }
        
        // Verify status
        $this->assertEquals("ok", $return_array["status"]);
        
        // Verify data in DB
        $problem_mask = new Problems();
        $problem_mask->setTitle(RequestContext::get("title"));
        $problems = ProblemsDAO::search($problem_mask);
        
        // Check that we only retreived 1 element
        $this->assertEquals(1, count($problems));        
        $problem = $problems[0];
        
        // Verify contest was found
        $this->assertNotNull($problem);
        $this->assertNotNull($problem->getProblemId());
        
        // Verify DB data
        $this->assertEquals(RequestContext::get("title"), $problem->getTitle());
        $this->assertEquals(RequestContext::get("alias"), $problem->getAlias());
        $this->assertEquals(RequestContext::get("validator"), $problem->getValidator());
        $this->assertEquals(RequestContext::get("time_limit"), $problem->getTimeLimit());
        $this->assertEquals(RequestContext::get("memory_limit"), $problem->getMemoryLimit());                      
        $this->assertEquals(RequestContext::get("author_id"), $problem->getAuthorId());
        $this->assertEquals(RequestContext::get("order"), $problem->getOrder());
        
        // Verify problem statement
        $filename = PROBLEMS_PATH . DIRECTORY_SEPARATOR . $problem->getSource();
        $this->assertFileExists($filename);                        
        
        $fileContent = file_get_contents($filename);
        $this->assertEquals($fileContent, RequestContext::get("source"));
        
        // Default data
        $this->assertEquals(0, $problem->getVisits());
        $this->assertEquals(0, $problem->getSubmissions());
        $this->assertEquals(0, $problem->getAccepted());
        $this->assertEquals(0, $problem->getDifficulty());       
        
        // Get problem-contest and verify it
        $contest_problems = ContestProblemsDAO::getByPK($contest_id, $problem->getProblemId());
        $this->assertNotNull($contest_problems);        
        $this->assertEquals(RequestContext::get("points"), $contest_problems->getPoints());        
        
        return (int)$problem->getProblemId();
    }
        
    
    public function testCreateProblemAsContestant()
    {        
        // Set context
        self::setValidContext();
                
        // Login as contestant
        $auth_token = Utils::LoginAsContestant();
        Utils::SetAuthToken($auth_token);
        
        // Execute API
        $newProblemInContest = new NewProblemInContest();                
                
        try
        {
            $return_array = $newProblemInContest->ExecuteApi();
        }
        catch(ApiException $e)
        {            
            // Validate error
            $exception_message = $e->getArrayMessage();            
            $this->assertEquals("User is not allowed to view this content.", $exception_message["error"]);
            $this->assertEquals("error", $exception_message["status"]);
            $this->assertEquals("HTTP/1.1 403 FORBIDDEN", $exception_message["header"]);
            
            // We're OK
            return;
        }
                
        var_dump($return_array);
        $this->fail("Contestant was able to create contest");                
    }
    
    
    public function testRequiredParameters()
    {
        // Login as judge
        $auth_token = Utils::LoginAsContestDirector();
        
        // Set valid context
        self::setValidContext();
        
        // Array of valid keys
        $valid_keys = array(
            "title",            
            "validator",
            "time_limit",            
            "memory_limit",
            "source",
            "author_id",            
        );
        
        foreach($valid_keys as $key)        
        {        
            // Set auth key
            Utils::SetAuthToken($auth_token);
            $newProblem = new NewProblemInContest();
            
            // Reset context            
            self::setValidContext();
            
            // Unset key
            unset($_REQUEST[$key]);
            
            try
            {
                // Execute API
                $newProblem->ExecuteApi();
                
            }
            catch(ApiException $e)
            {
                // Exception is expected
                $exception_array = $e->getArrayMessage();            

                // Validate exception
                $this->assertNotNull($exception_array);
                $this->assertEquals("error", $exception_array["status"]);
                $this->assertEquals(100, $exception_array["errorcode"]);
                $this->assertEquals("HTTP/1.1 400 BAD REQUEST", $exception_array["header"]);
                $this->assertContains($key, $exception_array["error"]);
                                
                // We're OK
                continue;
            }
            
            $this->fail("Exception was expected. Parameter: ". $key);            
        }                   
    }
}
?>
