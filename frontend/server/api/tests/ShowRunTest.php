<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
require_once '../ShowRun.php';

require_once 'NewRunTest.php';

require_once 'Utils.php';


class ShowRunTest extends PHPUnit_Framework_TestCase
{
    
    public function setUp()
    {        
        Utils::ConnectToDB();
    }
    
    public function tearDown() 
    {
        Utils::cleanup();
    }
    
    public function testShowValidRun()
    {
        // Create run with Contestant user
        $runCreator = new NewRunTest();
        $run = RunsDAO::getByPK($runCreator->testNewValidRun());
        
        // Login as contestant
        $auth_token = Utils::LoginAsContestant();
        
        // Set context
        Utils::SetAuthToken($auth_token);
        RequestContext::set("run_alias", $run->getGuid());
        
        // Execute API
        $showRun = new ShowRun();
        try
        {
            $return_array = $showRun->ExecuteApi();
        }
        catch(ApiException $e)
        {
            var_dump($e->getArrayMessage());            
            $this->fail("Unexpected exception");
        }
                
        // Validate output
        $this->assertEquals($run->getGuid(), $return_array["guid"]);
        $this->assertEquals("JE", $return_array["veredict"]);
        $this->assertEquals("new", $return_array["status"]);
    }
    
    public function testAlwaysShowRunAsContestDirector()
    {
        // Create run with Contestant user
        $runCreator = new NewRunTest();
        $run = RunsDAO::getByPK($runCreator->testNewValidRun());
        
        // Login as contestant
        $auth_token = Utils::LoginAsContestDirector();
        
        // Set context
        Utils::SetAuthToken($auth_token);
        RequestContext::set("run_alias", $run->getGuid());
        
        // Execute API
        $showRun = new ShowRun();
        try
        {
            $return_array = $showRun->ExecuteApi();
        }
        catch(ApiException $e)
        {
            var_dump($e->getArrayMessage());            
            $this->fail("Unexpected exception");
        }
                
        // Validate output
        $this->assertEquals($run->getGuid(), $return_array["guid"]);
        $this->assertEquals("JE", $return_array["veredict"]);
        $this->assertEquals("new", $return_array["status"]);
    }
    
    public function testAlwaysShowRunAsProblemAuthor()
    {
        // Create run with Contestant user
        $runCreator = new NewRunTest();
        $run = RunsDAO::getByPK($runCreator->testNewValidRun());
        
        // Login as contestant
        $auth_token = Utils::LoginAsProblemAuthor();
        
        // Set context
        Utils::SetAuthToken($auth_token);
        RequestContext::set("run_alias", $run->getGuid());
        
        // Execute API
        $showRun = new ShowRun();
        try
        {
            $return_array = $showRun->ExecuteApi();
        }
        catch(ApiException $e)
        {
            var_dump($e->getArrayMessage());            
            $this->fail("Unexpected exception");
        }
                
        // Validate output
        $this->assertEquals($run->getGuid(), $return_array["guid"]);
        $this->assertEquals("JE", $return_array["veredict"]);
        $this->assertEquals("new", $return_array["status"]);
    }
    
    public function testRunNotShowToOtherContestants()
    {
        // Create run with Contestant user
        $runCreator = new NewRunTest();
        $run = RunsDAO::getByPK($runCreator->testNewValidRun());
        
        // Login as contestant
        $auth_token = Utils::LoginAsContestant2();
        
        // Set context
        Utils::SetAuthToken($auth_token);
        RequestContext::set("run_alias", $run->getGuid());
        
        // Execute API
        $showRun = new ShowRun();
        try
        {
            $return_array = $showRun->ExecuteApi();
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
    
        
    // create a run from user, show run from judge
    // not show others' runs
}

?>
