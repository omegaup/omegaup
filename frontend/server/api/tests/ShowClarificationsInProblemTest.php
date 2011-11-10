<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


require_once '../ShowClarificationsInProblem.php';

require_once 'NewClarificationTest.php';

require_once 'Utils.php';


class ShowClarificationsInProblemTest extends PHPUnit_Framework_TestCase
{        
    
    
    public function testClarificationsAreShownForSameUser()
    {        
       
        // Clean clarifications from test problem
        Utils::DeleteClarificationsFromProblem(Utils::GetValidProblemOfContest(Utils::GetValidPublicContestId()));
        
        // As prerequisite, create a new clarification as contestant to guarantee at least one
        $newClarificationTest = new NewClarificationTest();
        $clarification_id_1 = $newClarificationTest->testCreateValidClarification();
        $clarification_id_2 = $newClarificationTest->testCreateValidClarification();
        
        //Connect to DB
        Utils::ConnectToDB(); 
                
        // Login as contestant
        $auth_token = Utils::LoginAsContestant();
        
        // Set the context        
        $_GET["problem_id"] = Utils::GetValidProblemOfContest(Utils::GetValidPublicContestId());
                
        // Execute API
        Utils::SetAuthToken($auth_token);
        $showClarificationsInProblem = new ShowClarificationsInProblem();
        
        try
        {
            $returnArray = $showClarificationsInProblem->ExecuteApi();            
        }
        catch (ApiException $e)
        {
            var_dump($e->getArrayMessage());            
            $this->fail("Unexpected exception");
        }
        
        // Check that we have 2 clarifications        
        $this->assertEquals(2, count($returnArray));
        
        // Check clarification #2, should appear first since it earlier
        $clarification_2 = ClarificationsDAO::getByPK($clarification_id_2);
        
        // Assert status of clarification
        $this->assertEquals($clarification_2->getMessage(), $returnArray[0]["message"]);
        $this->assertEquals($clarification_2->getAnswer(), $returnArray[0]["answer"]);
        $this->assertEquals($clarification_2->getTime(), $returnArray[0]["time"]);           
                
        // Check clarification #1
        $clarification_1 = ClarificationsDAO::getByPK($clarification_id_1);
        
        // Assert status of clarification
        $this->assertEquals($clarification_1->getMessage(), $returnArray[1]["message"]);
        $this->assertEquals($clarification_1->getAnswer(), $returnArray[1]["answer"]);
        $this->assertEquals($clarification_1->getTime(), $returnArray[1]["time"]);

    }
    
    
    public function testPrivateClarificationsNotSharedBetweeenContestants()
    {        
        // Clean clarifications from test problem
        Utils::DeleteClarificationsFromProblem(Utils::GetValidProblemOfContest(Utils::GetValidPublicContestId()));
        
        // As prerequisite, create a new clarification as contestant 1
        $newClarificationTest = new NewClarificationTest();
        $clarification_id_1 = $newClarificationTest->testCreateValidClarification();
        $clarification_id_2 = $newClarificationTest->testCreateValidClarification();
        
        // Hack clarificatoin 2, change created user to 2
        $clarification_2 = ClarificationsDAO::getByPK($clarification_id_2);
        $clarification_2->setAuthorId(Utils::GetContestant2UserId());
        ClarificationsDAO::save($clarification_2);
        
        //Connect to DB
        Utils::ConnectToDB(); 
                
        // Login as contestant
        $auth_token = Utils::LoginAsContestant2();
        
        // Set the context        
        $_GET["problem_id"] = Utils::GetValidProblemOfContest(Utils::GetValidPublicContestId());
                
        // Execute API
        Utils::SetAuthToken($auth_token);
        $showClarificationsInProblem = new ShowClarificationsInProblem();
        
        try
        {
            $returnArray = $showClarificationsInProblem->ExecuteApi();            
        }
        catch (ApiException $e)
        {
            var_dump($e->getArrayMessage());            
            $this->fail("Unexpected exception");
        }
        
        // Check that we have 2 clarifications                
        $this->assertEquals(1, count($returnArray));
        
        // Check clarification #2
        $clarification_2 = ClarificationsDAO::getByPK($clarification_id_2);
                                       
        // Assert status of clarification
        $this->assertEquals($clarification_2->getMessage(), $returnArray[0]["message"]);
        $this->assertEquals($clarification_2->getAnswer(), $returnArray[0]["answer"]);
        $this->assertEquals($clarification_2->getTime(), $returnArray[0]["time"]);                
    }
    
    public function testPublicClarificatoinsAreShared()
    {
        // Clean clarifications from test problem
        Utils::DeleteClarificationsFromProblem(Utils::GetValidProblemOfContest(Utils::GetValidPublicContestId()));
        
        // As prerequisite, create a new clarification as contestant 1
        $newClarificationTest = new NewClarificationTest();
        $clarification_id_1 = $newClarificationTest->testCreateValidClarification();
        $clarification_id_2 = $newClarificationTest->testCreateValidClarification();
         
        // Hack clarificatoin 2, change created user to 2 and make it public
        $clarification_2 = ClarificationsDAO::getByPK($clarification_id_2);
        $clarification_2->setAuthorId(Utils::GetContestant2UserId());
        $clarification_2->setPublic('1');
        ClarificationsDAO::save($clarification_2);
        
        //Connect to DB
        Utils::ConnectToDB(); 
                
        // Login as contestant
        $auth_token = Utils::LoginAsContestant();
        
        // Set the context        
        $_GET["problem_id"] = Utils::GetValidProblemOfContest(Utils::GetValidPublicContestId());
                
        // Execute API
        Utils::SetAuthToken($auth_token);
        $showClarificationsInProblem = new ShowClarificationsInProblem();
        
        try
        {
            $returnArray = $showClarificationsInProblem->ExecuteApi();            
        }
        catch (ApiException $e)
        {
            var_dump($e->getArrayMessage());            
            $this->fail("Unexpected exception");
        }
        
        // Check that we have 2 clarifications                
        $this->assertEquals(2, count($returnArray));
        
        // Check clarification #2 (TIMESTAMP has changed)
        $clarification_2 = ClarificationsDAO::getByPK($clarification_id_2);
                                       
        // Assert status of clarification #2
        $this->assertEquals($clarification_2->getMessage(), $returnArray[0]["message"]);
        $this->assertEquals($clarification_2->getAnswer(), $returnArray[0]["answer"]);
        $this->assertEquals($clarification_2->getTime(), $returnArray[0]["time"]);                   
        
        
        // Check clarification #1
        $clarification_1 = ClarificationsDAO::getByPK($clarification_id_1);
        
        // Assert status of clarification
        $this->assertEquals($clarification_1->getMessage(), $returnArray[1]["message"]);
        $this->assertEquals($clarification_1->getAnswer(), $returnArray[1]["answer"]);
        $this->assertEquals($clarification_1->getTime(), $returnArray[1]["time"]);                                
        
    }
    
    public function testJudgeCanSeeAllClarifications()
    {
        // Clean clarifications from test problem
        Utils::DeleteClarificationsFromProblem(Utils::GetValidProblemOfContest(Utils::GetValidPublicContestId()));
        
        // As prerequisite, create a new clarification as contestant 1
        $newClarificationTest = new NewClarificationTest();
        $clarification_id_1 = $newClarificationTest->testCreateValidClarification();        
        $clarification_id_2 = $newClarificationTest->testCreateValidClarification();
        $clarification_id_3 = $newClarificationTest->testCreateValidClarification();
         
        // Hack clarificatoin 2, change created user to 2 and make it public
        $clarification_2 = ClarificationsDAO::getByPK($clarification_id_2);
        $clarification_2->setAuthorId(Utils::GetContestant2UserId());
        $clarification_2->setPublic('1');        
        ClarificationsDAO::save($clarification_2);
        
        // Login as contestant
        $auth_token = Utils::LoginAsJudge();
        
        // Set the context        
        $_GET["problem_id"] = Utils::GetValidProblemOfContest(Utils::GetValidPublicContestId());
                
        // Execute API
        Utils::SetAuthToken($auth_token);
        $showClarificationsInProblem = new ShowClarificationsInProblem();
        
        try
        {
            $returnArray = $showClarificationsInProblem->ExecuteApi();            
        }
        catch (ApiException $e)
        {
            var_dump($e->getArrayMessage());            
            $this->fail("Unexpected exception");
        }
        
        // Check that we have 2 clarifications                      
        $this->assertEquals(3, count($returnArray));                                             
        
        // Check clarification #2
        $clarification_2 = ClarificationsDAO::getByPK($clarification_id_2);
        
        // Assert status of clarification #2 (TIMESTAM has changed)
        $this->assertEquals($clarification_2->getMessage(), $returnArray[0]["message"]);
        $this->assertEquals($clarification_2->getAnswer(), $returnArray[0]["answer"]);
        $this->assertEquals($clarification_2->getTime(), $returnArray[0]["time"]);                   
        
        // Check clarification #3
        $clarification_3 = ClarificationsDAO::getByPK($clarification_id_3);
        
        // Assert status of clarification
        $this->assertEquals($clarification_3->getMessage(), $returnArray[1]["message"]);
        $this->assertEquals($clarification_3->getAnswer(), $returnArray[1]["answer"]);
        $this->assertEquals($clarification_3->getTime(), $returnArray[1]["time"]);                        
        
        
        // Check clarification #1
        $clarification_1 = ClarificationsDAO::getByPK($clarification_id_1);
        
        // Assert status of clarification
        $this->assertEquals($clarification_1->getMessage(), $returnArray[2]["message"]);
        $this->assertEquals($clarification_1->getAnswer(), $returnArray[2]["answer"]);
        $this->assertEquals($clarification_1->getTime(), $returnArray[2]["time"]);                                                
        
    }
    
    public function testAdminCanSeeAllClarifications()
    {
        // Clean clarifications from test problem
        Utils::DeleteClarificationsFromProblem(Utils::GetValidProblemOfContest(Utils::GetValidPublicContestId()));
        
        // As prerequisite, create a new clarification as contestant 1
        $newClarificationTest = new NewClarificationTest();
        $clarification_id_1 = $newClarificationTest->testCreateValidClarification();        
        $clarification_id_2 = $newClarificationTest->testCreateValidClarification();
        $clarification_id_3 = $newClarificationTest->testCreateValidClarification();
         
        // Hack clarificatoin 2, change created user to 2 and make it public
        $clarification_2 = ClarificationsDAO::getByPK($clarification_id_2);
        $clarification_2->setAuthorId(Utils::GetContestant2UserId());
        $clarification_2->setPublic('1');        
        ClarificationsDAO::save($clarification_2);
        
        // Login as contestant
        $auth_token = Utils::LoginAsAdmin();
        
        // Set the context        
        $_GET["problem_id"] = Utils::GetValidProblemOfContest(Utils::GetValidPublicContestId());
                
        // Execute API
        Utils::SetAuthToken($auth_token);
        $showClarificationsInProblem = new ShowClarificationsInProblem();
        
        try
        {
            $returnArray = $showClarificationsInProblem->ExecuteApi();            
        }
        catch (ApiException $e)
        {
            var_dump($e->getArrayMessage());            
            $this->fail("Unexpected exception");
        }
        
        // Check that we have 2 clarifications                      
        $this->assertEquals(3, count($returnArray));                                             
        
        // Check clarification #2
        $clarification_2 = ClarificationsDAO::getByPK($clarification_id_2);
        
        // Assert status of clarification #2 (TIMESTAM has changed)
        $this->assertEquals($clarification_2->getMessage(), $returnArray[0]["message"]);
        $this->assertEquals($clarification_2->getAnswer(), $returnArray[0]["answer"]);
        $this->assertEquals($clarification_2->getTime(), $returnArray[0]["time"]);                   
        
        // Check clarification #3
        $clarification_3 = ClarificationsDAO::getByPK($clarification_id_3);
        
        // Assert status of clarification
        $this->assertEquals($clarification_3->getMessage(), $returnArray[1]["message"]);
        $this->assertEquals($clarification_3->getAnswer(), $returnArray[1]["answer"]);
        $this->assertEquals($clarification_3->getTime(), $returnArray[1]["time"]);                        
        
        
        // Check clarification #1
        $clarification_1 = ClarificationsDAO::getByPK($clarification_id_1);
        
        // Assert status of clarification
        $this->assertEquals($clarification_1->getMessage(), $returnArray[2]["message"]);
        $this->assertEquals($clarification_1->getAnswer(), $returnArray[2]["answer"]);
        $this->assertEquals($clarification_1->getTime(), $returnArray[2]["time"]);                                                
        
    }
}
?>
