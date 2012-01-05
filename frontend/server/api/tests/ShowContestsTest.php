<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

require_once '../ShowContests.php';
require_once '../NewContest.php';

require_once 'NewContestTest.php';
require_once 'Utils.php';



class ShowContestsTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {        
        Utils::ConnectToDB();
    }
    
    public function tearDown() 
    {
        Utils::cleanup();
    }                       
    
    public function testLatestPublicContest()
    {             
                        
        // Insert new contest
        $random_title = Utils::CreateRandomString();        
        NewContestTest::CreateContest($random_title, 1);
                       
        
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
        $this->assertArrayHasKey("0", $cleanValue['contests']);        
        $this->assertEquals($random_title, $cleanValue['contests'][0]["title"]);
        
        
        Utils::Logout($auth_token);
    }
    
    
    public function testPrivateContestNotSeenByOthers()
    {
        
        // Insert new contest
        $random_title = Utils::CreateRandomString();        
        NewContestTest::CreateContest($random_title, 0);
        
        
        // Login as contestant, should not see the private contest created by judge
        $auth_token = Utils::LoginAsContestant();
        
        // Get contests
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
        
        // Assert our contest is NOT there
        $this->assertArrayHasKey("0", $cleanValue['contests']);    
        $this->assertArrayHasKey("title", $cleanValue['contests'][0]);    
        $this->assertNotEquals($random_title, $cleanValue['contests'][0]["title"]);
        
        // Logout the contestant
        Utils::Logout($auth_token);                                               
        
        
    }
    
    public function testPrivateContestSeenByCreator()
    {
        
        // Insert new contest
        $random_title = Utils::CreateRandomString();        
        NewContestTest::CreateContest($random_title, 0);
        
        
        // Login as contests director, shoud see content
        $auth_token = Utils::LoginAsContestDirector();
        
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
        $this->assertArrayHasKey("0", $cleanValue['contests']);    
        $this->assertArrayHasKey("title", $cleanValue['contests'][0]);    
        $this->assertEquals($random_title, $cleanValue['contests'][0]["title"]);
        
        // Logout the contestant
        Utils::Logout($auth_token);  
        
    }
     
    
    
   
}

?>
