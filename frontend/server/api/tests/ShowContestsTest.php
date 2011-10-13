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



class ShowContestsTest extends PHPUnit_Framework_TestCase
{
        
    public function CreateContest($title, $public)
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
        
        if($public === 0)
        {
            $_POST["private_users"] = json_encode(array(Utils::GetJudgeUserId()));
        }
        
        $newContest = new NewContest();
        Utils::SetAuthToken($auth_token);
        
        
        try
        {
            $cleanValue = $newContest->ExecuteApi();        
            
        }
        catch(ApiException $e)
        {
            $this->fail("Exception was unexpected: ". var_dump($e->getArrayMessage()));    
        }

        // Assert status of new contest
        $this->assertEquals("ok", $cleanValue["status"]);
        
        // Clean requests
        Utils::cleanup();
        Utils::Logout($auth_token);
        
    }        
    
    
    public function testLatestPublicContest()
    {        
                        
        // Insert new contest
        $random_title = Utils::RandomString();        
        $this->CreateContest($random_title, 1);
               
        
        
        // Login as contestant
        $auth_token = Utils::LoginAsContestant();
        
        // Get contests, contest just created should be the first in the list
        $showContest = new ShowContests();
        Utils::SetAuthToken($auth_token);
        
        try
        {
            $cleanValue = $showContest->ExecuteApi();
        }
        catch(ApiException $e)
        {
            $this->fail("Exception was unexpected: ". var_dump($e->getArrayMessage()));    
        }
        
        // Assert our contest is there
        $this->assertArrayHasKey("0", $cleanValue);        
        $this->assertEquals($random_title, $cleanValue[0]["title"]);
        
        
        Utils::Logout($auth_token);
    }
    
    
    public function testPrivateContestNotSeenByOthers()
    {
        
        //Connect to DB
        Utils::ConnectToDB();
        
        // Insert new contest
        $random_title = Utils::RandomString();        
        $this->CreateContest($random_title, 0);
        
        
        // Login as contestant, should not see the private contest created by judge
        $auth_token = Utils::LoginAsContestant();
        
        // Get contests, contest just created should be the first in the list
        $showContest = new ShowContests();
        Utils::SetAuthToken($auth_token);
        
        try
        {
            $cleanValue = $showContest->ExecuteApi();
        }
        catch(ApiException $e)
        {
            $this->fail("Exception was unexpected: ". var_dump($e->getArrayMessage()));    
        }
        
        // Assert our contest is there
        $this->assertArrayHasKey("0", $cleanValue);    
        $this->assertArrayHasKey("title", $cleanValue[0]);    
        $this->assertNotEquals($random_title, $cleanValue[0]["title"]);
        
        // Logout the contestant
        Utils::Logout($auth_token);                                               
        
        
    }
    
    public function testPrivateContestSeenByCreator()
    {
        //Connect to DB
        Utils::ConnectToDB();
        
        // Insert new contest
        $random_title = Utils::RandomString();        
        $this->CreateContest($random_title, 0);
        
        
        // Login as contestant, should not see the private contest created by judge
        $auth_token = Utils::LoginAsJudge();
        
        // Get contests, contest just created should be the first in the list
        $showContest = new ShowContests();
        Utils::SetAuthToken($auth_token);
        
        try
        {
            $cleanValue = $showContest->ExecuteApi();
        }
        catch(ApiException $e)
        {
            $this->fail("Exception was unexpected: ". var_dump($e->getArrayMessage()));    
        }
        
        // Assert our contest is there
        $this->assertArrayHasKey("0", $cleanValue);    
        $this->assertArrayHasKey("title", $cleanValue[0]);    
        $this->assertNotEquals($random_title, $cleanValue[0]["title"]);
        
        // Logout the contestant
        Utils::Logout($auth_token);  
        
    }
     
     
    
   
}

?>
