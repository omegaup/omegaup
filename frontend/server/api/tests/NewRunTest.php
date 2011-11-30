<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

require_once '../NewRun.php';

require_once 'NewContestsTest.php';
require_once 'NewProblemInContestTest.php';

require_once 'Utils.php';


class NewRunTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {        
        Utils::ConnectToDB();
    }
    
    public function tearDown() 
    {
        Utils::cleanup();
    }
    
    public function testNewValidRun($contest_id = null, $problem_id = null)
    {
        // Login 
        $auth_token = Utils::LoginAsContestant();
        
        // Set context
        if(is_null($contest_id))
        {
            $contestCreator = new NewContestsTest();
            $contest_id = $contestCreator->testCreateValidContest(1);
        }
        
        if(is_null($problem_id))
        {
            $problemCreator = new NewProblemInContestTest();
            $problem_id = $problemCreator->testCreateValidProblem($contest_id);
        }
        
        Utils::SetAuthToken($auth_token);
        $_POST["contest_id"] = $contest_id;
        $_POST["problem_id"] = $problem_id;        
        $languages = array ('c','cpp','java','py','rb','pl','cs','p');
        $_POST["language"] = $languages[array_rand($languages, 1)];
        $_POST["source"] = "#include <stdio.h> int main() { printf(\"100\"); }";
        
        // Execute API
        $newRun = new NewRun();
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
        
        // Get run from DB
        $runs = RunsDAO::search(new Runs(array('contest_id'=> $contest_id, "problem_id" => $problem_id)));
        $run = $runs[0];
        $this->assertNotNull($run);
        
        // Validate data        
        $this->assertEquals($_POST["language"], $run->getLanguage());
        $this->assertNotEmpty($run->getGuid());
        
        // Validate file created
        $filename = RUNS_PATH . $run->getGuid();
        $this->assertFileExists($filename);
        $fileContent = file_get_contents($filename);
        $this->assertEquals($_POST["source"], $fileContent);        
        
        // Validate defaults
        $this->assertEquals("new", $run->getStatus());
        $this->assertEquals(0, $run->getRuntime());
        $this->assertEquals(0, $run->getMemory());
        $this->assertEquals(0, $run->getScore());
        $this->assertEquals(0, $run->getContestScore());
        $this->assertEquals("no ip", $run->getIp());
        $this->assertEquals(0, $run->getSubmitDelay());
        $this->assertEquals("JE", $run->getVeredict());
        
    }
    
    public function testRunWhenContestExpired()
    {        
        
        // Login 
        $auth_token = Utils::LoginAsContestant();
        
        // Create public contest
        $contestCreator = new NewContestsTest();
        $contest_id = $contestCreator->testCreateValidContest(1);
        
        // Manually expire contest
        $contest = ContestsDAO::getByPK($contest_id);                
        $contest->setFinishTime(Utils::GetTimeFromUnixTimestam(Utils::GetDBUnixTimestamp() - 1));                        
        ContestsDAO::save($contest);
        
        // Create problem in contest        
        $problemCreator = new NewProblemInContestTest();
        $problem_id = $problemCreator->testCreateValidProblem($contest_id);
        
        // Set valid context
        Utils::SetAuthToken($auth_token);
        $_POST["contest_id"] = $contest_id;
        $_POST["problem_id"] = $problem_id;        
        $languages = array ('c','cpp','java','py','rb','pl','cs','p');
        $_POST["language"] = $languages[array_rand($languages, 1)];
        $_POST["source"] = "#include <stdio.h> int main() { printf(\"100\"); }";
        
        // Execute API
        $newRun = new NewRun();
        try
        {
            $return_array = $newRun->ExecuteApi();
        }
        catch(ApiException $e)
        {
            // Validate exception            
            $exception_message = $e->getArrayMessage();            
            $this->assertEquals("User is not allowed to view this content.", $exception_message["error"]);
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
        $contestCreator = new NewContestsTest();
        $contest_id = $contestCreator->testCreateValidContest(0);
        
        $problemCreator = new NewProblemInContestTest();
        $problem_id = $problemCreator->testCreateValidProblem($contest_id);        
        
        // Login 
        $auth_token = Utils::LoginAsJudge();        
        Utils::SetAuthToken($auth_token);
        
        $_POST["contest_id"] = $contest_id;
        $_POST["problem_id"] = $problem_id;        
        $languages = array ('c','cpp','java','py','rb','pl','cs','p');
        $_POST["language"] = $languages[array_rand($languages, 1)];
        $_POST["source"] = "#include <stdio.h> int main() { printf(\"100\"); }";
        
        // Execute API
        $newRun = new NewRun();
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
        
        // Get run from DB
        $runs = RunsDAO::search(new Runs(array('contest_id'=> $contest_id, "problem_id" => $problem_id)));
        $run = $runs[0];
        $this->assertNotNull($run);
        
        // Validate data        
        $this->assertEquals($_POST["language"], $run->getLanguage());
        $this->assertNotEmpty($run->getGuid());
        
        // Validate file created
        $filename = RUNS_PATH . $run->getGuid();
        $this->assertFileExists($filename);
        $fileContent = file_get_contents($filename);
        $this->assertEquals($_POST["source"], $fileContent);        
        
        // Validate defaults
        $this->assertEquals("new", $run->getStatus());
        $this->assertEquals(0, $run->getRuntime());
        $this->assertEquals(0, $run->getMemory());
        $this->assertEquals(0, $run->getScore());
        $this->assertEquals(0, $run->getContestScore());
        $this->assertEquals("no ip", $run->getIp());
        $this->assertEquals(0, $run->getSubmitDelay());
        $this->assertEquals("JE", $run->getVeredict());
    }
    
    public function testRunToInvalidPrivateContest()
    {                
        // Set context
        $contestCreator = new NewContestsTest();
        $contest_id = $contestCreator->testCreateValidContest(0);
        
        $problemCreator = new NewProblemInContestTest();
        $problem_id = $problemCreator->testCreateValidProblem($contest_id);        
        
        // Login 
        $auth_token = Utils::LoginAsContestant2();        
        Utils::SetAuthToken($auth_token);
        
        $_POST["contest_id"] = $contest_id;
        $_POST["problem_id"] = $problem_id;        
        $languages = array ('c','cpp','java','py','rb','pl','cs','p');
        $_POST["language"] = $languages[array_rand($languages, 1)];
        $_POST["source"] = "#include <stdio.h> int main() { printf(\"100\"); }";
        
        // Execute API
        $newRun = new NewRun();
        try
        {
            $return_array = $newRun->ExecuteApi();
        }
        catch(ApiException $e)
        {
            // Validate exception            
            $exception_message = $e->getArrayMessage();            
            $this->assertEquals("User is not allowed to view this content.", $exception_message["error"]);
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
        $contestCreator = new NewContestsTest();
        $contest_id = $contestCreator->testCreateValidContest(1);
        
        // Manually expire contest
        $contest = ContestsDAO::getByPK($contest_id);                
        $contest->setStartTime(Utils::GetTimeFromUnixTimestam(Utils::GetDBUnixTimestamp() + 1));                        
        ContestsDAO::save($contest);
        
        // Create problem in contest        
        $problemCreator = new NewProblemInContestTest();
        $problem_id = $problemCreator->testCreateValidProblem($contest_id);
        
        // Set valid context
        Utils::SetAuthToken($auth_token);
        $_POST["contest_id"] = $contest_id;
        $_POST["problem_id"] = $problem_id;        
        $languages = array ('c','cpp','java','py','rb','pl','cs','p');
        $_POST["language"] = $languages[array_rand($languages, 1)];
        $_POST["source"] = "#include <stdio.h> int main() { printf(\"100\"); }";
        
        // Execute API
        $newRun = new NewRun();
        try
        {
            $return_array = $newRun->ExecuteApi();
        }
        catch(ApiException $e)
        {
            // Validate exception            
            $exception_message = $e->getArrayMessage();            
            $this->assertEquals("User is not allowed to view this content.", $exception_message["error"]);
            $this->assertEquals("error", $exception_message["status"]);
            $this->assertEquals("HTTP/1.1 403 FORBIDDEN", $exception_message["header"]);                         
            
            // We're OK
            return;            
        }
        
        var_dump($contest);
        var_dump($return_array);
        $this->fail("Contestant was able to submit run in an not yet started contest.");
        
    }
        
    // submissions gap test
    // run invalid combination prob id contest id
    // run missing parameters
    // window length?
    
}