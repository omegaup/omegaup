<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

require_once '../UpdateClarification.php';

require_once 'NewClarificationTest.php';

require_once 'Utils.php';


class UpdateClarificationTest extends PHPUnit_Framework_TestCase
{        
    public function setUp()
    {        
        Utils::ConnectToDB();
    }
    
    public function tearDown() 
    {
        Utils::cleanup();
    }
    
    public function testUpdateAnswer()
    {               
        // As prerequisite, create a new clarification as contestant to guarantee at least one
        $newClarificationTest = new NewClarificationTest();
        $clarification_id_1 = $newClarificationTest->testCreateValidClarification();
        
        // Save original message
        $clarification = ClarificationsDAO::getByPK($clarification_id_1);
        $originalMessage = $clarification->getMessage();
                                
        // Login as contestant
        $auth_token = Utils::LoginAsContestDirector();
        
        // Set the context
        $_GET["clarification_id"] = $clarification_id_1;
        $_POST["answer"] = "this is my answer";
        $_POST["public"] = '1';
        
        // Execute API
        Utils::SetAuthToken($auth_token);
        $updateClarification = new UpdateClarification();
        
        try
        {
            $returnArray = $updateClarification->ExecuteApi();            
        }
        catch (ApiException $e)
        {
            var_dump($e->getArrayMessage());            
            $this->fail("Unexpected exception");
        }
        
        // Validate return status
        $this->assertEquals("ok", $returnArray["status"]);
        
        // Validate update in DB
        $clarification = ClarificationsDAO::getByPK($clarification_id_1);
        
        // Assert status of clarification
        $this->assertEquals($clarification->getMessage(), $originalMessage);
        $this->assertEquals($clarification->getAnswer(), "this is my answer");        
    }
    
    public function testUpdateMessage()
    {                 
        // As prerequisite, create a new clarification as contestant to guarantee at least one
        $newClarificationTest = new NewClarificationTest();
        $clarification_id_1 = $newClarificationTest->testCreateValidClarification();                        
                
        // Login as contestant
        $auth_token = Utils::LoginAsContestDirector();
        
        // Set the context
        $_GET["clarification_id"] = $clarification_id_1;
        $_POST["answer"] = "this is my answer";
        $_POST["message"] = "this is my new message";
        $_POST["public"] = '1';
        
        // Execute API
        Utils::SetAuthToken($auth_token);
        $updateClarification = new UpdateClarification();
        
        try
        {
            $returnArray = $updateClarification->ExecuteApi();            
        }
        catch (ApiException $e)
        {
            var_dump($e->getArrayMessage());            
            $this->fail("Unexpected exception");
        }
        
        // Validate return status
        $this->assertEquals("ok", $returnArray["status"]);
        
        // Validate update in DB
        $clarification = ClarificationsDAO::getByPK($clarification_id_1);
        
        // Assert status of clarification
        $this->assertEquals($clarification->getMessage(), "this is my new message");
        $this->assertEquals($clarification->getAnswer(), "this is my answer");        
        
    }
    
    public function testProblemAuthorCanUpdateClarifications()
    {                
        // As prerequisite, create a new clarification as contestant to guarantee at least one
        $newClarificationTest = new NewClarificationTest();
        $clarification_id_1 = $newClarificationTest->testCreateValidClarification();
        
        // Verify that the original message was not corrupted
        $clarification = ClarificationsDAO::getByPK($clarification_id_1);
        $originalMessage = $clarification->getMessage();
                                
        // Login as contestant
        $auth_token = Utils::LoginAsProblemAuthor();
        
        // Set the context
        $_GET["clarification_id"] = $clarification_id_1;
        $_POST["answer"] = "this is my answer";
        $_POST["public"] = '1';
        
        // Execute API
        Utils::SetAuthToken($auth_token);
        $updateClarification = new UpdateClarification();
        
        try
        {
            $returnArray = $updateClarification->ExecuteApi();            
        }
        catch (ApiException $e)
        {
            var_dump($e->getArrayMessage());            
            $this->fail("Unexpected exception");
        }
        
        // Validate return status
        $this->assertEquals("ok", $returnArray["status"]);
        
        // Validate update in DB
        $clarification = ClarificationsDAO::getByPK($clarification_id_1);
        
        // Assert status of clarification
        $this->assertEquals($clarification->getMessage(), $originalMessage);
        $this->assertEquals($clarification->getAnswer(), "this is my answer");
    }
    
    public function testContestantCannotUpdateClarifications()
    {                 
        // As prerequisite, create a new clarification as contestant to guarantee at least one
        $newClarificationTest = new NewClarificationTest();
        $clarification_id_1 = $newClarificationTest->testCreateValidClarification();
                                     
        // Login as contestant
        $auth_token = Utils::LoginAsContestant();
        
        // Set the context
        $_GET["clarification_id"] = $clarification_id_1;
        $_POST["answer"] = "this is my answer";
        $_POST["message"] = "this is my new message";
        $_POST["public"] = '1';
        
        // Execute API
        Utils::SetAuthToken($auth_token);
        $updateClarification = new UpdateClarification();
        
        try
        {
            $returnArray = $updateClarification->ExecuteApi();            
        }
        catch (ApiException $e)
        {          
            $errorArray = $e->getArrayMessage();
            
            $this->assertEquals("User is not allowed to view this content.", $errorArray["error"]);
            $this->assertEquals("HTTP/1.1 403 FORBIDDEN", $errorArray["header"]);
            
            // We are oK
            return;
        }
        
        $this->fail("Contestant was able to update clarification");
    }
}
?>
