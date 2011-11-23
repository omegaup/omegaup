<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

require_once '../ShowProblemInContest.php';
require_once '../NewProblemInContest.php';

require_once 'NewContestsTest.php';
require_once 'NewProblemInContestTest.php';

require_once 'Utils.php';

class ShowProblemInContestTest extends PHPUnit_Framework_TestCase
{    
    public function setUp()
    {        
        Utils::ConnectToDB();
    }
    
    public function tearDown() 
    {
        Utils::cleanup();
    }
    
    public function testShowValidProblem()
    {
        
        // Create a clean contest and get the ID
        $contestCreator = new NewContestsTest();
        $contest_id = $contestCreator->testCreateValidContest(1);
                        
        // Create a problem in given contest
        $problemCreator = new NewProblemInContestTest();
        $problem_id = $problemCreator->testCreateValidProblem($contest_id);
        
        // Login as contestant
        $auth_token = Utils::LoginAsContestant();
        
        // Set Context
        $_GET["problem_id"] = $problem_id;
        $_GET["contest_id"] = $contest_id;
        
        //Get API
        $showProblemInContest = new ShowProblemInContest();
        Utils::SetAuthToken($auth_token);
        
        try
        {            
            $return_array = $showProblemInContest->ExecuteApi();            
        }
        catch (ApiException $e)
        {
            $var_dump($e->getArrayMessage());
            $this->fail("Unexpected exception.");
        }
        
        // Get problem id from DB to compare it
        $problem = ProblemsDAO::getByPK($problem_id);
        
        // Assert data
        $this->assertEquals($return_array["title"], $problem->getTitle());
        $this->assertEquals($return_array["alias"], $problem->getAlias());
        $this->assertEquals($return_array["validator"], $problem->getValidator());
        $this->assertEquals($return_array["time_limit"], $problem->getTimeLimit());
        $this->assertEquals($return_array["memory_limit"], $problem->getMemoryLimit());                      
        $this->assertEquals($return_array["author_id"], $problem->getAuthorId());        
        $this->assertEquals($return_array["source"], "<p>redacción</p>");
        $this->assertEquals($return_array["order"], $problem->getOrder());
        
        // Default data
        $this->assertEquals(0, $problem->getVisits());
        $this->assertEquals(0, $problem->getSubmissions());
        $this->assertEquals(0, $problem->getAccepted());
        $this->assertEquals(0, $problem->getDifficulty());
        
        // Verify that we have an empty array of runs
        $this->assertEmpty($return_array["runs"]);
        
        // Verify that problem was marked as Opened
        $problem_opened = ContestProblemOpenedDAO::getByPK($contest_id, $problem_id, Utils::GetContestantUserId());
        $this->assertNotNull($problem_opened);        

        // Verify open time 
        $this->assertEquals(Utils::GetDBUnixTimestamp(), Utils::GetDBUnixTimestamp($problem_opened->getOpenTime()));
        
    }
    
    public function testShowValidProblemTwoTimesDontOverwriteOpenTime()
    {
        
        // Create a clean contest and get the ID
        $contestCreator = new NewContestsTest();
        $contest_id = $contestCreator->testCreateValidContest(1);
                        
        // Create a problem in given contest
        $problemCreator = new NewProblemInContestTest();
        $problem_id = $problemCreator->testCreateValidProblem($contest_id);
        
        // Login as contestant
        $auth_token = Utils::LoginAsContestant();
        
        // Set Context
        $_GET["problem_id"] = $problem_id;
        $_GET["contest_id"] = $contest_id;
        
        //Get API
        $showProblemInContest = new ShowProblemInContest();
        Utils::SetAuthToken($auth_token);
        
        try
        {            
            $return_array = $showProblemInContest->ExecuteApi();            
        }
        catch (ApiException $e)
        {
            $var_dump($e->getArrayMessage());
            $this->fail("Unexpected exception.");
        }
        
        // Cleanup and reset context        
        $_GET["problem_id"] = $problem_id;
        $_GET["contest_id"] = $contest_id;
        Utils::SetAuthToken($auth_token);
        
        // Sleep 1 sec to differentiate open times
        sleep(1);        
        try
        {            
            $return_array = $showProblemInContest->ExecuteApi();                        
        }
        catch (ApiException $e)
        {
            $var_dump($e->getArrayMessage());
            $this->fail("Unexpected exception.");
        }
        
        // Get problem id from DB to compare it
        $problem = ProblemsDAO::getByPK($problem_id);
        
        // Assert data
        $this->assertEquals($return_array["title"], $problem->getTitle());
        $this->assertEquals($return_array["alias"], $problem->getAlias());
        $this->assertEquals($return_array["validator"], $problem->getValidator());
        $this->assertEquals($return_array["time_limit"], $problem->getTimeLimit());
        $this->assertEquals($return_array["memory_limit"], $problem->getMemoryLimit());                      
        $this->assertEquals($return_array["author_id"], $problem->getAuthorId());        
        $this->assertEquals($return_array["source"], "<p>redacción</p>");
        $this->assertEquals($return_array["order"], $problem->getOrder());
        
        // Default data
        $this->assertEquals(0, $problem->getVisits());
        $this->assertEquals(0, $problem->getSubmissions());
        $this->assertEquals(0, $problem->getAccepted());
        $this->assertEquals(0, $problem->getDifficulty());
        
        // Verify that we have an empty array of runs
        $this->assertEmpty($return_array["runs"]);
        
        // Verify that problem was marked as Opened
        $problem_opened = ContestProblemOpenedDAO::getByPK($contest_id, $problem_id, Utils::GetContestantUserId());
        $this->assertNotNull($problem_opened);        

        // Verify open time 
        $this->assertNotEquals(Utils::GetDBUnixTimestamp(), Utils::GetDBUnixTimestamp($problem_opened->getOpenTime()));        
        
    }
                 
    // Problem from private contest
    public function testDontShowProblemFromPrivateContest()
    {
        // Create a clean PRIVATE contest only with judge allowed to see it and get the ID
        $contestCreator = new NewContestsTest();
        $contest_id = $contestCreator->testCreateValidContest(0);
                        
        // Create a problem in given contest
        $problemCreator = new NewProblemInContestTest();
        $problem_id = $problemCreator->testCreateValidProblem($contest_id);
        
        // Login as contestant
        $auth_token = Utils::LoginAsContestant();
        
        // Set Context
        $_GET["problem_id"] = $problem_id;
        $_GET["contest_id"] = $contest_id;
        
        // Execute API
        $showProblemInContest = new ShowProblemInContest();
        Utils::SetAuthToken($auth_token);
        
        try
        {            
            $return_array = $showProblemInContest->ExecuteApi();                        
        }
        catch (ApiException $e)
        {            
            // Assert exception
            $exception_message = $e->getArrayMessage();
            $this->assertEquals("User is not allowed to view this content.", $exception_message["error"]);
            $this->assertEquals("error", $exception_message["status"]);
            $this->assertEquals(106, $exception_message["errorcode"]);
            $this->assertEquals("HTTP/1.1 403 FORBIDDEN", $exception_message["header"]);
            
            // We're ok
            return;
        }   
        
        var_dump($return_array);
        $this->fail("User was able to see problem in private contest not invited.");
    }
    
    // @TODO Assert problem with runs
    
    // Improve search with DAO search by object    
}

?>
