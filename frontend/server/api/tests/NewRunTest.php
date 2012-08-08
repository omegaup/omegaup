<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

require_once '../NewRun.php';
require_once '../ShowContest.php';

require_once 'NewContestTest.php';
require_once 'NewProblemInContestTest.php';

require_once 'Utils.php';


class NewRunTest extends PHPUnit_Framework_TestCase
{
    private $graderMock;
    
    public function setUp()
    {        
        Utils::ConnectToDB();
    }
    
    public function tearDown() 
    {
        Utils::cleanup();
    }
    
    public function openContestBeforeSubmit($contest_id)
    {
        // Set context
        $contest = ContestsDAO::getByPK($contest_id);
        RequestContext::set("alias", $contest->getAlias());                
        
        // Execute API
        $showContest = new ShowContest();
        
        try
        {
            $return_array = $showContest->ExecuteApi();
        }
        catch(ApiException $e)
        {     
            // Intentionally eat exceptions.           
        }
        
        unset($_REQUEST["contest_id"]);
    }
    
    private function setValidContext($contest_id, $problem_id)
    {
        // User should visit contest prior submit a solution        
        $this->openContestBeforeSubmit($contest_id);
        
        // Get contest & problem object from DB
        $contest = ContestsDAO::getByPK($contest_id);
        $problem = ProblemsDAO::getByPK($problem_id);
        
        // Set context
        RequestContext::set("contest_alias", $contest->getAlias());
        RequestContext::set("problem_alias", $problem->getAlias());                
        
        // Pick a language
        RequestContext::set("language", 'c');
        RequestContext::set("source", "#include <stdio.h>\nint main() { printf(\"3\"); return 0; }");
        
        // PhpUnit doesn't set a REMOTE_ADDR, doing it manually
        $_SERVER['REMOTE_ADDR'] = "127.0.0.1"; 
        
        // Create the Grader mock
        $this->graderMock = $this->getMock('Grader', array('Grade'));
        
        // Set expectations
        $this->graderMock->expects($this->any())
                ->method('Grade')
                ->will($this->returnValue(true));
    }
    
    public function testNewValidRun($contest_id = null, $problem_id = null, $auth_token = null)
    {
        // Login 
        if(is_null($auth_token))
        {
            $auth_token = Utils::LoginAsContestant();
        }
        
        // Set context
        if(is_null($contest_id))
        {            
            $contestCreator = new NewContestTest();
            $contest_id = $contestCreator->testCreateValidContest(1);                        
        }
        $contest = ContestsDAO::getByPK($contest_id);
        
        if(is_null($problem_id))
        {
            $problemCreator = new NewProblemInContestTest();
            $problem_id = $problemCreator->testCreateValidProblem($contest_id);
        }
        
        Utils::SetAuthToken($auth_token);
        $this->setValidContext($contest_id, $problem_id);
        
        // Execute API
        $newRun = new NewRun($this->graderMock);
        try
        {
            $return_array = $newRun->ExecuteApi();
        }
        catch(ApiException $e)
        {
            var_dump($e->getArrayMessage());            
            $this->fail("Unexpected exception: " . print_r($e->getArrayMessage(), true));
        }
        
        // Validate output
        $this->assertEquals("ok", $return_array["status"]);
        $this->assertArrayHasKey("guid", $return_array);
        
        // Get run from DB
        $run = RunsDAO::getByAlias($return_array["guid"]);        
        $this->assertNotNull($run);
        
        // Validate data        
        $this->assertEquals(RequestContext::get("language"), $run->getLanguage());
        $this->assertNotEmpty($run->getGuid());
        
        // Validate file created
        $filename = RUNS_PATH . DIRECTORY_SEPARATOR . $run->getGuid();
        $this->assertFileExists($filename);
        $fileContent = file_get_contents($filename);
        $this->assertEquals(RequestContext::get("source"), $fileContent);        
        
        // Validate defaults
        $this->assertEquals("new", $run->getStatus());
        $this->assertEquals(0, $run->getRuntime());
        $this->assertEquals(0, $run->getMemory());
        $this->assertEquals(0, $run->getScore());
        $this->assertEquals(0, $run->getContestScore());
        $this->assertEquals("127.0.0.1", $run->getIp());
        $this->assertEquals((time() - strtotime($contest->getStartTime()))/60, $run->getSubmitDelay(), '', 0.5);
        $this->assertEquals("JE", $run->getVeredict());
                
        
        return (int)$run->getRunId();
    }
    
    public function testRunWhenContestExpired()
    {        
        
        // Login 
        $auth_token = Utils::LoginAsContestant();
        
        // Create public contest
        $contestCreator = new NewContestTest();
        $contest_id = $contestCreator->testCreateValidContest(1);
        
        // Manually expire contest
        $contest = ContestsDAO::getByPK($contest_id);                
        $contest->setFinishTime(Utils::GetTimeFromUnixTimestam(Utils::GetPhpUnixTimestamp() - 1));                        
        ContestsDAO::save($contest);
        
        // Create problem in contest        
        $problemCreator = new NewProblemInContestTest();
        $problem_id = $problemCreator->testCreateValidProblem($contest_id);
        
        // Set valid context
        Utils::SetAuthToken($auth_token);
        $this->setValidContext($contest_id, $problem_id);
        
        // Execute API
        $newRun = new NewRun($this->graderMock);
        try
        {
            $return_array = $newRun->ExecuteApi();
        }
        catch(ApiException $e)
        {
            // Validate exception            
            $exception_message = $e->getArrayMessage();            
            $this->assertEquals("Unable to submit run: Contest time has expired or not started yet.", $exception_message["error"]);
            $this->assertEquals("error", $exception_message["status"]);
            $this->assertEquals("HTTP/1.1 403 FORBIDDEN", $exception_message["header"]);                         
            
            // We're OK
            return;            
        }
        
        var_dump($contest);
        var_dump($return_array);
        $this->fail("Contestant was able to submit run in an expired contest.");
        
    }
    
    public function testRunToValidPrivateContest()
    {                
        // Set context
        $contestCreator = new NewContestTest();
        $contest_id = $contestCreator->testCreateValidContest(0);
        $contest = ContestsDAO::getByPK($contest_id);
        
        $problemCreator = new NewProblemInContestTest();
        $problem_id = $problemCreator->testCreateValidProblem($contest_id);        
        
        // Login 
        $auth_token = Utils::LoginAsContestDirector();        
        Utils::SetAuthToken($auth_token);
        
        $this->setValidContext($contest_id, $problem_id);
        
        // Execute API
        $newRun = new NewRun($this->graderMock);
        try
        {
            $return_array = $newRun->ExecuteApi();
        }
        catch(ApiException $e)
        {
            var_dump($e->getArrayMessage());            
            $this->fail("Unexpected exception");
        }
        
        // Validate output
        $this->assertArrayHasKey("guid", $return_array);
        
        // Get run from DB
        $run = RunsDAO::getByAlias($return_array["guid"]);
        $this->assertNotNull($run);
        
        // Validate data        
        $this->assertEquals(RequestContext::get("language"), $run->getLanguage());
        $this->assertNotEmpty($run->getGuid());
        
        // Validate file created
        $filename = RUNS_PATH . DIRECTORY_SEPARATOR . $run->getGuid();
        $this->assertFileExists($filename);
        $fileContent = file_get_contents($filename);
        $this->assertEquals(RequestContext::get("source"), $fileContent);        
        
        // Validate defaults
        $this->assertEquals("new", $run->getStatus());
        $this->assertEquals(0, $run->getRuntime());
        $this->assertEquals(0, $run->getMemory());
        $this->assertEquals(0, $run->getScore());
        $this->assertEquals(0, $run->getContestScore());
        $this->assertEquals("127.0.0.1", $run->getIp());
        $this->assertEquals((time() - strtotime($contest->getStartTime()))/60, $run->getSubmitDelay(), '', 0.5);
        $this->assertEquals("JE", $run->getVeredict());
    }
    
    public function testRunToInvalidPrivateContest()
    {                
        // Set context
        $contestCreator = new NewContestTest();
        $contest_id = $contestCreator->testCreateValidContest(0);
        
        $problemCreator = new NewProblemInContestTest();
        $problem_id = $problemCreator->testCreateValidProblem($contest_id);        
        
        // Login 
        $auth_token = Utils::LoginAsContestant2();        
        Utils::SetAuthToken($auth_token);
        
        $this->setValidContext($contest_id, $problem_id);
        
        // Execute API
        $newRun = new NewRun($this->graderMock);
        try
        {
            $return_array = $newRun->ExecuteApi();
        }
        catch(ApiException $e)
        {
            // Validate exception            
            $exception_message = $e->getArrayMessage();            
            $this->assertEquals("Unable to submit run: You must open the problem before trying to submit a solution.", $exception_message["error"]);
            $this->assertEquals("error", $exception_message["status"]);
            $this->assertEquals("HTTP/1.1 403 FORBIDDEN", $exception_message["header"]);                         
            
            // We're OK
            return;            
        }
        
        var_dump($contest);
        var_dump($return_array);
        $this->fail("Contestant was able to submit run in a private contest and was not invited!.");
    }
    
    
    public function testRunWhenContestNotStarted()
    {        
        
        // Login 
        $auth_token = Utils::LoginAsContestant();
        
        // Create public contest
        $contestCreator = new NewContestTest();
        $contest_id = $contestCreator->testCreateValidContest(1);
        
        // Manually expire contest
        $contest = ContestsDAO::getByPK($contest_id);                
        $contest->setStartTime(Utils::GetTimeFromUnixTimestam(Utils::GetPhpUnixTimestamp() + 10));                        
        ContestsDAO::save($contest);
        
        // Create problem in contest        
        $problemCreator = new NewProblemInContestTest();
        $problem_id = $problemCreator->testCreateValidProblem($contest_id);
        
        // Set valid context
        Utils::SetAuthToken($auth_token);
        
        $this->setValidContext($contest_id, $problem_id);
        
        // Execute API
        $newRun = new NewRun($this->graderMock);
        try
        {
            $return_array = $newRun->ExecuteApi();
        }
        catch(ApiException $e)
        {
            // Validate exception            
            $exception_message = $e->getArrayMessage();            
            $this->assertEquals("Unable to submit run: Contest time has expired or not started yet.", $exception_message["error"]);
            $this->assertEquals("error", $exception_message["status"]);
            $this->assertEquals("HTTP/1.1 403 FORBIDDEN", $exception_message["header"]);                         
            
            // We're OK
            return;            
        }
        
        var_dump($contest);
        var_dump($return_array);
        $this->fail("Contestant was able to submit run in an not yet started contest.");
        
    }
    
    public function testInvalidRunInsideSubmissionsGap()
    {
        // Login 
        $auth_token = Utils::LoginAsContestant();
        
        // Create public contest
        $contestCreator = new NewContestTest();
        $contest_id = $contestCreator->testCreateValidContest(1);   
        
        // Set submissions gap of 20 seconds
        $contest = ContestsDAO::getByPK($contest_id);                
        $contest->setSubmissionsGap(20);
        ContestsDAO::save($contest);
        
        // Create problem in contest        
        $problemCreator = new NewProblemInContestTest();
        $problem_id = $problemCreator->testCreateValidProblem($contest_id);
        
        // Set valid context for Run 
        Utils::SetAuthToken($auth_token);
        $this->setValidContext($contest_id, $problem_id);
        
        $newRun = new NewRun($this->graderMock);        
                    
        try
        {
            $return_array = $newRun->ExecuteApi();
        }
        catch(ApiException $e)
        {                
            var_dump($e->getArrayMessage());            
            $this->fail("Unexpected exception");
        }

        // Validate output
        $this->assertEquals("ok", $return_array["status"]);

        try
        {
            $return_array = $newRun->ExecuteApi();
        }
        catch(ApiException $e)
        {
            // Validate exception            
            $exception_message = $e->getArrayMessage();            
            $this->assertEquals("Unable to submit run: You have to wait 20 seconds between consecutive submissions.", $exception_message["error"]);
            $this->assertEquals("error", $exception_message["status"]);
            $this->assertEquals("HTTP/1.1 401 FORBIDDEN", $exception_message["header"]);                                         

            // We're OK
            return;
        }
        var_dump($contest);
        var_dump($return_array);
        $this->fail("Contestant was able to submit run inside the submission gap.");
    }
            
    public function testSubmissionGapIsPerProblem()
    {
        // Login 
        $auth_token = Utils::LoginAsContestant();
        
        // Create public contest
        $contestCreator = new NewContestTest();
        $contest_id = $contestCreator->testCreateValidContest(1);   
        
        // Set submissions gap of 2 seconds
        $contest = ContestsDAO::getByPK($contest_id);                
        $contest->setSubmissionsGap(2);
        ContestsDAO::save($contest);
        
        // Create 3 problems in contest        
        $problemCreator = new NewProblemInContestTest();
        $problem_id[0] = $problemCreator->testCreateValidProblem($contest_id);
        $problem_id[1] = $problemCreator->testCreateValidProblem($contest_id);
        $problem_id[2] = $problemCreator->testCreateValidProblem($contest_id);
        
        // Set valid context for Run 
        Utils::SetAuthToken($auth_token);
        $this->setValidContext($contest_id, $problem_id[0]);
        
        $newRun = new NewRun($this->graderMock);        
        
        // Send problems
        for($i = 0; $i < 3; $i++)
        {
            // Try different problem id
            $problem = ProblemsDAO::getByPK($problem_id[$i]);
            RequestContext::set("problem_alias", $problem->getAlias());        
            
            try
            {
                $return_array = $newRun->ExecuteApi();
            }
            catch(ApiException $e)
            {                
                var_dump($e->getArrayMessage());            
                $this->fail("Unexpected exception");
            }

            // Validate output
            $this->assertEquals("ok", $return_array["status"]);
        }
        
    }
    
    public function testInvalidContestProblemCombination()
    {        
        
        // Login 
        $auth_token = Utils::LoginAsContestant();                
        
        // Create public contest 1
        $contestCreator = new NewContestTest();
        $contest_id_1 = $contestCreator->testCreateValidContest(1);   
        
        // Create public contest 2
        $contestCreator = new NewContestTest();
        $contest_id_2 = $contestCreator->testCreateValidContest(1);   
        
        // Create problem in contest 2       
        $problemCreator = new NewProblemInContestTest();
        $problem_id = $problemCreator->testCreateValidProblem($contest_id_2);
        
        // Set invalid context
        Utils::SetAuthToken($auth_token);        
        $this->setValidContext($contest_id_1, $problem_id);        
        
        // Execute API
        $newRun = new NewRun($this->graderMock);
        try
        {
            $return_array = $newRun->ExecuteApi();
        }
        catch(ApiException $e)
        {
            // Validate exception            
            $exception_message = $e->getArrayMessage();            
            $this->assertEquals("problem_alias and contest_alias combination is invalid.", $exception_message["error"]);
            $this->assertEquals("error", $exception_message["status"]);
            $this->assertEquals("HTTP/1.1 400 BAD REQUEST", $exception_message["header"]);                         
            
            // We're OK
            return;            
        }
        
        var_dump($contest);
        var_dump($return_array);
        $this->fail("Contestant was able to submit run in an not yet started contest.");
        
    }
            
    public function testMissingParameters()
    {
        // Set context
        $contestCreator = new NewContestTest();
        $contest_id = $contestCreator->testCreateValidContest(1);
        
        $problemCreator = new NewProblemInContestTest();
        $problem_id = $problemCreator->testCreateValidProblem($contest_id);        
        
        // Login 
        $auth_token = Utils::LoginAsContestDirector();        
        Utils::SetAuthToken($auth_token);
        
        $this->setValidContext($contest_id, $problem_id);
        
        $needed_keys = array(
            "problem_alias",
            "contest_alias",
            "language",
            "source"                        
        );
        
        foreach($needed_keys as $key)        
        {
            // Reset context
            Utils::SetAuthToken($auth_token);
            $this->setValidContext($contest_id, $problem_id);
            
            // Unset key
            unset($_REQUEST[$key]);
            
            // Execute API
            $newRun = new NewRun($this->graderMock);
            try
            {
                $return_array = $newRun->ExecuteApi();
            }
            catch(ApiException $e)
            {
                // Exception is expected
                $exception_array = $e->getArrayMessage();            

                // Validate exception
                $this->assertNotNull($exception_array);
                $this->assertArrayHasKey('error', $exception_array);                    
                
                // We're OK
                continue;
            }
            
            $this->fail("Exception was expected. Parameter: ". $key);            
        }
    }
    
    public function testNewRunInWindowLengthPublicContest()
    {
        // Login 
        $auth_token = Utils::LoginAsContestant();
        
        // Set context        
        $contestCreator = new NewContestTest();
        $contest_id = $contestCreator->testCreateValidContest(1);        
               
        $problemCreator = new NewProblemInContestTest();
        $problem_id = $problemCreator->testCreateValidProblem($contest_id);        
        
        // Alter Contest window length
        $contest = ContestsDAO::getByPK($contest_id);
        $contest->setWindowLength(20);
        ContestsDAO::save($contest);
        
        Utils::SetAuthToken($auth_token);
        $this->setValidContext($contest_id, $problem_id);
        
        // Execute API
        $newRun = new NewRun($this->graderMock);
        try
        {
            $return_array = $newRun->ExecuteApi();
        }
        catch(ApiException $e)
        {
            var_dump($e->getArrayMessage());            
            $this->fail("Unexpected exception");
        }
        
        // Validate output
        $this->assertEquals("ok", $return_array["status"]);
    }
    
    public function testNewRunOutWindowLengthPublicContest()
    {
        // Login 
        $auth_token = Utils::LoginAsContestant();
        
        // Set context        
        $contestCreator = new NewContestTest();
        $contest_id = $contestCreator->testCreateValidContest(1);        
               
        $problemCreator = new NewProblemInContestTest();
        $problem_id = $problemCreator->testCreateValidProblem($contest_id);        
        
        // Alter Contest window length
        $contest = ContestsDAO::getByPK($contest_id);
        $contest->setWindowLength(20);
        ContestsDAO::save($contest);                
        
        Utils::SetAuthToken($auth_token);
        $this->setValidContext($contest_id, $problem_id);
        
        // Alter first access time to make appear the run outside window length
        $contest_user = ContestsUsersDAO::getByPK(Utils::GetContestantUserId(), $contest_id);        
        $contest_user->setAccessTime(date("Y-m-d H:i:s", time() - 21 * 60)); //Window length is in minutes                
        ContestsUsersDAO::save($contest_user);
        
        // Execute API
        $newRun = new NewRun($this->graderMock);
        try
        {
            $return_array = $newRun->ExecuteApi();
        }
        catch(ApiException $e)
        {
            // Validate exception            
            $exception_message = $e->getArrayMessage();            
            $this->assertEquals("Unable to submit run: Contest time has expired or not started yet.", $exception_message["error"]);
            $this->assertEquals("error", $exception_message["status"]);
            $this->assertEquals("HTTP/1.1 403 FORBIDDEN", $exception_message["header"]);                         
            
            // We're OK
            return;            
        }
        
        var_dump($contest);
        var_dump($return_array);
        $this->fail("Contestant was able to submit run in an expired contest.");
    }               
}
