<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

require_once '../ShowContests.php';
require_once '../NewContest.php';
require_once 'Utils.php';


class NewContestsTest extends PHPUnit_Framework_TestCase
{
        
    public static function CreateContest($title, $public, $key_to_unset = NULL)
    {        
        $auth_token = Utils::LoginAsJudge();
                
        $_POST["title"] = $title;
        $_POST["description"] = "description";
        $_POST["start_time"] = Utils::GetTimeFromUnixTimestam(Utils::GetDBUnixTimestamp() - 60*60);
        $_POST["finish_time"] = Utils::GetTimeFromUnixTimestam(Utils::GetDBUnixTimestamp() + 60*60);
        $_POST["window_length"] = null;
        $_POST["public"] = $public;
        $_POST["token"] = "loltoken";
        $_POST["points_decay_factor"] = ".02";
        $_POST["partial_score"] = "0";
        $_POST["submissions_gap"] = "10";
        $_POST["feedback"] = "yes";
        $_POST["penalty"] = 100;
        $_POST["scoreboard"] = 100;
        $_POST["penalty_time_start"] = "contest";
        $_POST["penalty_calc_policy"] = "sum";
        
        // If contest is private, an array of users should be provided, in this case we add the judge
        if($public === 0)
        {
            $_POST["private_users"] = json_encode(array(Utils::GetJudgeUserId()));
        }
        
        // If a key to unset is provided, unset it
        if(!is_null($key_to_unset))            
        {                        
            unset($_POST[$key_to_unset]);
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
        //Connect to DB
        Utils::ConnectToDB();
        
        // Insert new contest
        $random_title = Utils::CreateRandomString();        
        self::CreateContest($random_title, $public);
        
        // Validate that data was written to DB by iterating through all contests
        $contest = new Contests();
        $contest->setTitle($random_title);
        $contests = ContestsDAO::search($contest);
        $contest = $contests[0];
        
        // Assert that we found our contest       
        $this->assertNotNull($contest);
        $this->assertNotNull($contest->getContestId());
        
        // Assert data was correctly saved
        $this->assertEquals($_POST["description"], $contest->getDescription());
        $this->assertEquals($_POST["start_time"], $contest->getStartTime());
        $this->assertEquals($_POST["finish_time"], $contest->getFinishTime());
        $this->assertEquals($_POST["window_length"], $contest->getWindowLength());
        $this->assertEquals($_POST["public"], $contest->getPublic());
        $this->assertEquals($_POST["token"], $contest->getToken());
        $this->assertEquals($_POST["points_decay_factor"], $contest->getPointsDecayFactor());
        $this->assertEquals($_POST["partial_score"], $contest->getPartialScore());
        $this->assertEquals($_POST["submissions_gap"], $contest->getSubmissionsGap());
        $this->assertEquals($_POST["feedback"], $contest->getFeedback());
        $this->assertEquals($_POST["penalty"], $contest->getPenalty());
        $this->assertEquals($_POST["scoreboard"], $contest->getScoreboard());
        $this->assertEquals($_POST["penalty_time_start"], $contest->getTimeStart());
        $this->assertEquals($_POST["penalty_calc_policy"], $contest->getPenaltyCalcPolicy());
        
        // Return contest ID
        return $contest->getContestId();
    }
    
    public function testMissingParameters()
    {
        //Connect to DB
        Utils::ConnectToDB();
        
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
                $this->assertArrayHasKey('error', $exception_array);    

                if ($key !== "start_time" )
                {
                    $this->assertEquals("Required parameter ". $key ." is missing.", $exception_array["error"]);
                }

                return;
            }

            $this->fail("Exception was expected. Parameter: ". $key);            
        }        
    }
    
    public function testCreateContestAsUser()
    {
        //Connect to DB
        Utils::ConnectToDB();
        
        // Login as contestant
        $auth_token = Utils::LoginAsContestant();
        
        // Set context
        $_POST["title"] = Utils::CreateRandomString();
        $_POST["description"] = "description";
        $_POST["start_time"] = "02/02/2011";
        $_POST["finish_time"] = "03/03/2011";
        $_POST["window_length"] = "20";
        $_POST["public"] = "1";
        $_POST["token"] = "loltoken";
        $_POST["points_decay_factor"] = ".02";
        $_POST["partial_score"] = "0";
        $_POST["submissions_gap"] = "10";
        $_POST["feedback"] = "yes";
        $_POST["penalty"] = 100;
        $_POST["scoreboard"] = 100;
        $_POST["penalty_time_start"] = "problem";
        $_POST["penalty_calc_policy"] = "sum";
        
        // Execute API
        $newContest = new NewContest();
        Utils::SetAuthToken($auth_token);
        
        try 
        {
            $return_array = $newContest->ExecuteApi();
        }
        catch (ApiException $e)
        {
            // Validate error
            $exception_message = $e->getArrayMessage();            
            $this->assertEquals("User is not allowed to view this content.", $exception_message["error"]);
            $this->assertEquals("error", $exception_message["status"]);
            $this->assertEquals("HTTP/1.1 403 FORBIDDEN", $exception_message["header"]);
            
            return;
        }        
        $this->fail("Contestant was able to insert contest.");        
    }         
    
}