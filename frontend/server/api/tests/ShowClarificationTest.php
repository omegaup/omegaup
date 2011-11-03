<?php


/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

define('WHOAMI', 'API');
require_once '../../inc/bootstrap.php';

require_once '../ShowClarification.php';
require_once '../NewClarification.php';

require_once 'NewClarificationTest.php';

require_once 'Utils.php';



class ShowClarificationTest extends PHPUnit_Framework_TestCase
{

    public function testShowClarificationAsJudge()
    {
                       
        //Connect to DB
        Utils::ConnectToDB();
        
        // Get a random clarification id
        $clarifications = ClarificationsDAO::getAll();        
        $clarification = $clarifications[array_rand($clarifications, 1)]; 
        $clarification_id = $clarification->getClarificationId(); 
        
        // Login as judge
        $auth_token = Utils::LoginAsJudge();
        
        // Set context
        $_GET["clarification_id"] = $clarification_id;
        
        // Execute API
        Utils::SetAuthToken($auth_token);
        $showClarification = new ShowClarification();
        try
        {
            $returnArray = $showClarification->ExecuteApi();
        }
        catch(ApiException $e)
        {            
            echo "Clarification: \n";
            var_dump($clarification);
            
            echo "Exception: \n";
            var_dump( $e->getArrayMessage() );
            
            throw $e;            
        }
                
        // Assert status of clarification
        $this->assertEquals($clarification->getMessage(), $returnArray["message"]);
        $this->assertEquals($clarification->getAnswer(), $returnArray["answer"]);
        $this->assertEquals($clarification->getTime(), $returnArray["time"]);
        $this->assertEquals($clarification->getProblemId(), $returnArray["problem_id"]);
        $this->assertEquals($clarification->getContestId(), $returnArray["contest_id"]);

        // Clean requests
        Utils::cleanup();
        Utils::Logout($auth_token); 
        
    }
    
    public function testShowClarificationAsContestant()
    {
        // As prerequisite, create a new clarification as contestant to guarantee at least one
        $newClarificationTest = new NewClarificationTest();
        $newClarificationTest->testCreateValidClarification();
        
        //Connect to DB
        Utils::ConnectToDB(); 
        
        // Get a clarification id created by the contestant
        $clarifications = ClarificationsDAO::getAll();        
        $clarification = null;
        foreach($clarifications as $c)
        {
            if($c->getAuthorId() == Utils::GetContestantUserId() && $c->getPublic() == '0')
            {
                $clarification = $c;
                break;
            }
        }
        
        if(is_null($clarification))
        {
            $this->fail("No clarification for user was found. Failing.");
        }
                
        // Login as contestant
        $auth_token = Utils::LoginAsContestant();
        
        // Set context
        $_GET["clarification_id"] = $clarification->getClarificationId();
        
        // Execute API
        Utils::SetAuthToken($auth_token);
        $showClarification = new ShowClarification();
        try
        {
            $returnArray = $showClarification->ExecuteApi();
        }
        catch(ApiException $e)
        {            
            echo "Clarification: \n";
            var_dump($clarification);
            
            echo "Exception: \n";
            var_dump( $e->getArrayMessage() );
            
            throw $e;            
        }
                
        // Assert status of clarification
        $this->assertEquals($clarification->getMessage(), $returnArray["message"]);
        $this->assertEquals($clarification->getAnswer(), $returnArray["answer"]);
        $this->assertEquals($clarification->getTime(), $returnArray["time"]);
        $this->assertEquals($clarification->getProblemId(), $returnArray["problem_id"]);
        $this->assertEquals($clarification->getContestId(), $returnArray["contest_id"]);

        // Clean requests
        Utils::cleanup();
        Utils::Logout($auth_token);                 
    }
    
    
    public function testClarificationsCreatedPrivateAsDefault()
    {        
        // As prerequisite, create a new clarification as contestant to guarantee at least one
        $newClarificationTest = new NewClarificationTest();
        $newClarificationTest->testCreateValidClarification();        
        
        //Connect to DB
        Utils::ConnectToDB(); 
        
        // Get a clarification id created by the contestant
        $clarifications = ClarificationsDAO::getAll();
        $clarification = null;
        foreach($clarifications as $c)
        {
            if($c->getAuthorId() == Utils::GetContestantUserId() && $c->getPublic() === '0')
            {
                $clarification = $c;
                break;
            }
        }
        
        if(is_null($clarification))
        {            
            $this->fail("No clarification for user was found. Failing.");
        }        
        
        // Login as a different contestant 
        $auth_token = Utils::LoginAsContestant2();
        
        // Set context
        $_GET["clarification_id"] = $clarification->getClarificationId();
        
        // Execute API
        Utils::SetAuthToken($auth_token);
        $showClarification = new ShowClarification();
        try
        {
            $returnArray = $showClarification->ExecuteApi();
        }
        catch(ApiException $e)
        {            
            
            $errorArray = $e->getArrayMessage();
            $this->assertArrayHasKey("error", $errorArray);
            $this->assertEquals("User is not allowed to view this content.", $errorArray["error"]);
            $this->assertEquals("HTTP/1.1 403 FORBIDDEN", $errorArray["header"]);
            
            // We're ok
            // Clean requests
            Utils::cleanup();
            Utils::Logout($auth_token);   
            
            return;
        }
                
        var_dump($clarification);
        $this->fail("User2 was able to see clarification. Failing");
                
    }
    
    public function testPublicClarificationsCanBeViewed()
    {  
        // As prerequisite, create a new clarification as contestant to guarantee at least one
        $newClarificationTest = new NewClarificationTest();
        $newClarificationTest->testCreateValidClarification();        
        
        //Connect to DB
        Utils::ConnectToDB(); 
        
        // Get a clarification id created by the contestant
        $clarifications = ClarificationsDAO::getAll();
        $clarification = null;
        foreach($clarifications as $c)
        {
            if($c->getAuthorId() == Utils::GetContestantUserId())
            {
                $clarification = $c;
                break;
            }
        }
        
        if(is_null($clarification))
        {            
            $this->fail("No clarification for user was found. Failing.");
        }        
        
        // Make this clarification public
        $clarification->setPublic('1');
        ClarificationsDAO::save($clarification);
        
        // Login as different user and retreive clarification        
        $auth_token = Utils::LoginAsContestant2();
        
        // Set context
        $_GET["clarification_id"] = $clarification->getClarificationId();
        
        // Execute API
        Utils::SetAuthToken($auth_token);
        $showClarification = new ShowClarification();
        try
        {
            $returnArray = $showClarification->ExecuteApi();
        }
        catch(ApiException $e)
        {            
            echo "Clarification: \n";
            var_dump($clarification);
            
            echo "Exception: \n";
            var_dump( $e->getArrayMessage() );
            
            throw $e;            
        }
                
        // Assert status of clarification
        $this->assertEquals($clarification->getMessage(), $returnArray["message"]);
        $this->assertEquals($clarification->getAnswer(), $returnArray["answer"]);
        $this->assertEquals($clarification->getTime(), $returnArray["time"]);
        $this->assertEquals($clarification->getProblemId(), $returnArray["problem_id"]);
        $this->assertEquals($clarification->getContestId(), $returnArray["contest_id"]);

        // Clean requests
        Utils::cleanup();
        Utils::Logout($auth_token);        
        
    }
    
}
?>
