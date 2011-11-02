<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

define('WHOAMI', 'API');
require_once '../../inc/bootstrap.php';
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
        $_POST["start_time"] = "02/02/2011";
        $_POST["finish_time"] = "03/03/2011";
        $_POST["window_length"] = "20";
        $_POST["public"] = $public;
        $_POST["token"] = "loltoken";
        $_POST["points_decay_factor"] = ".02";
        $_POST["partial_score"] = "0";
        $_POST["submissions_gap"] = "10";
        $_POST["feedback"] = "yes";
        $_POST["penalty"] = 100;
        $_POST["scoreboard"] = 100;
        $_POST["penalty_time_start"] = "problem";
        $_POST["penalty_calc_policy"] = "sum";
        
        // If contest is private, an array of users should be provided, in this case we add the judge
        if($public === 0)
        {
            $_POST["private_users"] = json_encode(array(Utils::GetJudgeUserId()));
        }
        
        if(!is_null($key_to_unset))            
        {                        
            unset($_POST[$key_to_unset]);
        }
        
        $newContest = new NewContest();
        Utils::SetAuthToken($auth_token);
        
        
        try
        {
            $cleanValue = $newContest->ExecuteApi();        
            
        }
        catch(ApiException $e)
        {
            // Propagate exception            
            throw $e;
            
        }

        // Assert status of new contest
        self::assertEquals("ok", $cleanValue["status"]);
        
        // Clean requests
        Utils::cleanup();
        Utils::Logout($auth_token);
        
    }  
    
    public function testCreateValidContest()
    {
        //Connect to DB
        Utils::ConnectToDB();
        
        // Insert new contest
        $random_title = Utils::RandomString();        
        self::CreateContest($random_title, 0);
    }
    
    public function testInvalidTitle()
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
        
        // Pick one to be unset
        $rand_key = array_rand($valid_keys, 1);
        
        try
        {
            // Insert new contest
            $random_title = Utils::RandomString();        
            $clean_value = self::CreateContest($random_title, 0, $valid_keys[$rand_key]);
        }
        catch(ApiException $e)
        {
                        
            // Exception is expected
            $exception_array = $e->getArrayMessage();            
            
            // Validate exception
            $this->assertNotNull($exception_array);
            $this->assertArrayHasKey('error', $exception_array);    
            
            if ($valid_keys[$rand_key] !== "start_time" )
            {
                $this->assertEquals("Required parameter ". $valid_keys[$rand_key] ." is missing.", $exception_array["error"]);
            }
            
            return;
        }
        
        $this->fail("Exception was expected. Parameter: ". $valid_keys[$rand_key]);            
        
    }
    
}