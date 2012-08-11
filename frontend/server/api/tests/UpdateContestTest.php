<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

require_once '../UpdateContest.php';

require_once 'NewContestTest.php';

require_once 'Utils.php';


class UpdateContestTest extends PHPUnit_Framework_TestCase
{
    
    public function testBasicUpdateContest()
    {
        // Create a contest to edit
        $contestFactory = new NewContestTest();
        $contest_id = $contestFactory->testCreateValidContest(0); // private contest
        $contest = ContestsDAO::getByPK($contest_id);        
                
        // Clean up context
        $_REQUEST = array();
        
        // Login as contest director
        $auth_token = Utils::LoginAsContestDirector();
        
        // Set the contest to edit
        RequestContext::set("contest_alias", $contest->getAlias());
        
        // Edit title
        RequestContext::set("title", "New title");
        RequestContext::set("description", "new description");
        RequestContext::set("start_time", Utils::GetPhpUnixTimestamp() - 60*60); // same
        $newFinishTime = Utils::GetPhpUnixTimestamp() + 60*60*2;
        RequestContext::set("finish_time", $newFinishTime); // diferent
        RequestContext::set("window_length", null);
        RequestContext::set("public", 1);        
        RequestContext::set("points_decay_factor", ".02");
        RequestContext::set("partial_score", "0");
        RequestContext::set("submissions_gap", "10");
        RequestContext::set("feedback", "no");
        RequestContext::set("penalty", 30);
        RequestContext::set("scoreboard", 20);
        RequestContext::set("penalty_time_start", "contest");
        RequestContext::set("penalty_calc_policy", "sum");
        
        //Execute api
        Utils::SetAuthToken($auth_token);
        $updateContest = new UpdateContest();
        
        try
        {
            $returnArray = $updateContest->ExecuteApi();
        }
        catch(ApiException $e)
        {
            var_dump($e->getArrayMessage());            
            $this->fail("Unexpected exception");
        }
        
        // Validate return status
        $this->assertEquals("ok", $returnArray["status"]);
        
        ContestsDAO::$useDAOCache = false;
        $contest = ContestsDAO::getByPK($contest_id);
        $this->assertEquals("New title", $contest->getTitle());
        $this->assertEquals("new description", $contest->getDescription());        
        $this->assertEquals(1, $contest->getPublic());
        $this->assertEquals("10", $contest->getSubmissionsGap());
        $this->assertEquals("no", $contest->getFeedback());
    }
    
    public function testBasicUnauthorizedUpdateContest()
    {
        // Create a contest to edit
        $contestFactory = new NewContestTest();
        $contest_id = $contestFactory->testCreateValidContest(0); // private contest
        $contest = ContestsDAO::getByPK($contest_id);        
                
        // Clean up context
        $_REQUEST = array();
        
        // Login as contestant
        $auth_token = Utils::LoginAsContestant();
        
        // Set the contest to edit
        RequestContext::set("contest_alias", $contest->getAlias());
        
        // Edit title
        RequestContext::set("title", "New title");
        RequestContext::set("description", "new description");
        RequestContext::set("start_time", Utils::GetPhpUnixTimestamp() - 60*60); // same
        $newFinishTime = Utils::GetPhpUnixTimestamp() + 60*60*2;
        RequestContext::set("finish_time", $newFinishTime); // diferent
        RequestContext::set("window_length", null);
        RequestContext::set("public", 1);        
        RequestContext::set("points_decay_factor", ".02");
        RequestContext::set("partial_score", "0");
        RequestContext::set("submissions_gap", "10");
        RequestContext::set("feedback", "no");
        RequestContext::set("penalty", 30);
        RequestContext::set("scoreboard", 20);
        RequestContext::set("penalty_time_start", "contest");
        RequestContext::set("penalty_calc_policy", "sum");
        
        //Execute api
        Utils::SetAuthToken($auth_token);
        $updateContest = new UpdateContest();
        
        try
        {
            $returnArray = $updateContest->ExecuteApi();
        }
        catch(ApiException $e)
        {
            $errorArray = $e->getArrayMessage();
            
            $this->assertEquals("User is not allowed to view this content.", $errorArray["error"]);
            $this->assertEquals("HTTP/1.1 403 FORBIDDEN", $errorArray["header"]);
            
            // We are oK
            return;
        }
        
        $this->fail("Contestant was able to update contest.");             
    }
    
    public function testInvalidAlias()
    {
        // Create a contest to edit
        $contestFactory = new NewContestTest();
        $contest_id = $contestFactory->testCreateValidContest(0); // private contest
        $contest = ContestsDAO::getByPK($contest_id);        
                
        // Clean up context
        $_REQUEST = array();
        
        // Login as contest director
        $auth_token = Utils::LoginAsContestDirector();
        
        // Set the contest to edit
        RequestContext::set("contest_alias", "Invalid alias");
        
        // Edit title
        RequestContext::set("title", "New title");
        RequestContext::set("description", "new description");
        RequestContext::set("start_time", Utils::GetPhpUnixTimestamp() - 60*60); // same
        $newFinishTime = Utils::GetPhpUnixTimestamp() + 60*60*2;
        RequestContext::set("finish_time", $newFinishTime); // diferent
        RequestContext::set("window_length", null);
        RequestContext::set("public", 1);        
        RequestContext::set("points_decay_factor", ".02");
        RequestContext::set("partial_score", "0");
        RequestContext::set("submissions_gap", "10");
        RequestContext::set("feedback", "no");
        RequestContext::set("penalty", 30);
        RequestContext::set("scoreboard", 20);
        RequestContext::set("penalty_time_start", "contest");
        RequestContext::set("penalty_calc_policy", "sum");
        
        //Execute api
        Utils::SetAuthToken($auth_token);
        $updateContest = new UpdateContest();
        
        try
        {
            $returnArray = $updateContest->ExecuteApi();
        }
        catch(ApiException $e)
        {
            $errorArray = $e->getArrayMessage();
            
            $this->assertEquals("Contest alias specified does not exists.", $errorArray["error"]);
            $this->assertEquals("HTTP/1.1 400 BAD REQUEST", $errorArray["header"]);
            
            // We are oK
            return;
        }
        
        $this->fail("API was able to find an unexisting contest. LOL.");
    }
    
    public function testBasicUpdateWithOptionalsContest()
    {
        // Create a contest to edit
        $contestFactory = new NewContestTest();
        $contest_id = $contestFactory->testCreateValidContest(0); // private contest
        $contest = ContestsDAO::getByPK($contest_id);        
                
        $original_title = $contest->getTitle();
        
        // Clean up context
        $_REQUEST = array();
        
        // Login as contest director
        $auth_token = Utils::LoginAsContestDirector();
        
        // Set the contest to edit
        RequestContext::set("contest_alias", $contest->getAlias());
        
        // Avoid editing title        
        RequestContext::set("description", "new description");
        RequestContext::set("start_time", Utils::GetPhpUnixTimestamp() - 60*60); // same
        $newFinishTime = Utils::GetPhpUnixTimestamp() + 60*60*2;
        RequestContext::set("finish_time", $newFinishTime); // diferent
        RequestContext::set("window_length", null);
        RequestContext::set("public", 1);        
        RequestContext::set("points_decay_factor", ".02");
        RequestContext::set("partial_score", "0");
        RequestContext::set("submissions_gap", "10");
        RequestContext::set("feedback", "no");
        RequestContext::set("penalty", 30);
        RequestContext::set("scoreboard", 20);
        RequestContext::set("penalty_time_start", "contest");
        RequestContext::set("penalty_calc_policy", "sum");
        
        //Execute api
        Utils::SetAuthToken($auth_token);
        $updateContest = new UpdateContest();
        
        try
        {
            $returnArray = $updateContest->ExecuteApi();
        }
        catch(ApiException $e)
        {
            var_dump($e->getArrayMessage());            
            $this->fail("Unexpected exception");
        }
        
        // Validate return status
        $this->assertEquals("ok", $returnArray["status"]);
        
        ContestsDAO::$useDAOCache = false;
        $contest = ContestsDAO::getByPK($contest_id);
        $this->assertEquals($original_title, $contest->getTitle());
        $this->assertEquals("new description", $contest->getDescription());        
        $this->assertEquals(1, $contest->getPublic());
        $this->assertEquals("10", $contest->getSubmissionsGap());
        $this->assertEquals("no", $contest->getFeedback());
    }
}
?>
