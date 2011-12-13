<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

require_once '../ShowContests.php';
require_once '../NewContest.php';
require_once 'Utils.php';


class NewContestTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {        
        Utils::ConnectToDB();
    }
    
    public function tearDown() 
    {
        Utils::cleanup();
    }    
    
    public static function CreateContest($title, $public, $key_to_unset = NULL)
    {        
        $auth_token = Utils::LoginAsContestDirector();
                
        RequestContext::set("title", $title);
        RequestContext::set("description", "description");
        RequestContext::set("start_time", Utils::GetTimeFromUnixTimestam(Utils::GetPhpUnixTimestamp() - 60*60));
        RequestContext::set("finish_time", Utils::GetTimeFromUnixTimestam(Utils::GetPhpUnixTimestamp() + 60*60));
        RequestContext::set("window_length", null);
        RequestContext::set("public", $public);
        RequestContext::set("token", "loltoken");
        RequestContext::set("points_decay_factor", ".02");
        RequestContext::set("partial_score", "0");
        RequestContext::set("submissions_gap", "10");
        RequestContext::set("feedback", "yes");
        RequestContext::set("penalty", 100);
        RequestContext::set("scoreboard", 100);
        RequestContext::set("penalty_time_start", "contest");
        RequestContext::set("penalty_calc_policy", "sum");
        
        // If contest is private, an array of users should be provided, in this case we add the director
        if($public === 0)
        {
            RequestContext::set("private_users", json_encode(array(Utils::GetContestDirectorUserId())));
        }
        
        // If a key to unset is provided, unset it
        if(!is_null($key_to_unset))            
        {                          
            unset($_REQUEST[$key_to_unset]);                        
        }
        
        // Create new contest
        $newContest = new NewContest();
        Utils::SetAuthToken($auth_token);                
        try
        {
            $cleanValue = $newContest->ExecuteApi();                    
        }
        catch(ApiException $e)
        {                    
            throw $e;            
        }

        // Assert status of new contest
        self::assertEquals("ok", $cleanValue["status"]);
        
        // Clean requests        
        Utils::Logout($auth_token);
        
    }  
    
    public function testCreateValidContest($public = 1)
    {                
        // Insert new contest
        $random_title = Utils::CreateRandomString();        
        try
        {            
            self::CreateContest($random_title, $public);
        }
        catch(ApiException $e)
        {            
            var_dump($e->getArrayMessage());
            $this->fail("Unexpected exception.");
        }
        
        // Validate that data was written to DB by iterating through all contests
        $contest = new Contests();
        $contest->setTitle($random_title);
        $contests = ContestsDAO::search($contest);
        $contest = $contests[0];
        
        // Assert that we found our contest       
        $this->assertNotNull($contest);
        $this->assertNotNull($contest->getContestId());
        
        // Assert data was correctly saved
        $this->assertEquals(RequestContext::get("description"), $contest->getDescription());
        $this->assertEquals(RequestContext::get("start_time"), $contest->getStartTime());
        $this->assertEquals(RequestContext::get("finish_time"), $contest->getFinishTime());
        $this->assertEquals(RequestContext::get("window_length"), $contest->getWindowLength());
        $this->assertEquals(RequestContext::get("public"), $contest->getPublic());
        $this->assertEquals(RequestContext::get("token"), $contest->getToken());
        $this->assertEquals(RequestContext::get("points_decay_factor"), $contest->getPointsDecayFactor());
        $this->assertEquals(RequestContext::get("partial_score"), $contest->getPartialScore());
        $this->assertEquals(RequestContext::get("submissions_gap"), $contest->getSubmissionsGap());
        $this->assertEquals(RequestContext::get("feedback"), $contest->getFeedback());
        $this->assertEquals(RequestContext::get("penalty"), $contest->getPenalty());
        $this->assertEquals(RequestContext::get("scoreboard"), $contest->getScoreboard());
        $this->assertEquals(RequestContext::get("penalty_time_start"), $contest->getPenaltyTimeStart());
        $this->assertEquals(RequestContext::get("penalty_calc_policy"), $contest->getPenaltyCalcPolicy());
        
        // Return contest ID
        return (int)$contest->getContestId();
    }
    
    public function testMissingParameters()
    {                
        // Array of valid keys
        $valid_keys = array(
            "title",
            "description",
            "start_time",
            "finish_time",            
            "public",
            "token",
            "points_decay_factor",
            "partial_score",
            "submissions_gap",
            "feedback",
            "penalty",
            "scoreboard",
            "penalty_time_start",
            "penalty_calc_policy"            
        );
        
        foreach($valid_keys as $key)        
        {        
            try
            {
                // Insert new contest
                $random_title = Utils::CreateRandomString();        
                $clean_value = self::CreateContest($random_title, 1, $key);
            }
            catch(ApiException $e)
            {
                // Exception is expected
                $exception_array = $e->getArrayMessage();            

                // Validate exception
                $this->assertNotNull($exception_array);
                $this->assertEquals(100, $exception_array["errorcode"]);
                $this->assertEquals("HTTP/1.1 400 BAD REQUEST", $exception_array["header"]);                

                return;
            }

            $this->fail("Exception was expected. Parameter: ". $key);            
        }        
    }            
}