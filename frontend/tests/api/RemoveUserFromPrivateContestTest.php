<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

require_once '../RemoveUserFromPrivateContest.php';

require_once 'AddUserToPrivateContestTest.php';

require_once 'Utils.php';


class RemoveUserToPrivateContestTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {        
        Utils::ConnectToDB();
    }
    
    public function tearDown() 
    {
        Utils::cleanup();
    }
    
    public function testContestDirectorRemovesUserFromContest()
    {
        // Call the test to add user
        $addUser = new AddUserToPrivateContestTest();
        $addUser->testContestDirectorAddsUserToContest();
        
        // Get the contest
        $contest = ContestsDAO::getByAlias(RequestContext::get('contest_alias'));
        
        // Login as a director
        $auth_token = Utils::LoginAsContestDirector();
        Utils::SetAuthToken($auth_token);
        
        // Set the context
        RequestContext::set('contest_alias', $contest->getAlias());
        RequestContext::set('user_id', Utils::GetContestant2UserId());
            
        // Execute API
        $api = new RemoveUserToPrivateContest();
        try 
        {
            $return_array = $api->ExecuteApi();
        }
        catch(ApiException $e)
        {
            var_dump($e->getArrayMessage());            
            $this->fail("Unexpected exception");
        }
        
        // Validate output
        $this->assertEquals("ok", $return_array["status"]);
        
        // Check DB
        $contest_users = new ContestsUsers();
        $contest_users->setUserId(RequestContext::get('user_id'));
        $contest_users->setContestId($contest->getContestId());
        $result = ContestsUsersDAO::search($contest_users);
        
        $this->assertEmpty($result);
    }
    
    public function testContestantNotAbleToRemoveUserFromContest()
    {
        // Call the test to add user
        $addUser = new AddUserToPrivateContestTest();
        $addUser->testContestDirectorAddsUserToContest();
        
        // Get the contest
        $contest = ContestsDAO::getByAlias(RequestContext::get('contest_alias'));
        
        // Login as a contestant
        $auth_token = Utils::LoginAsContestant();
        Utils::SetAuthToken($auth_token);
        
        // Set the context
        RequestContext::set('contest_alias', $contest->getAlias());
        RequestContext::set('user_id', Utils::GetContestant2UserId());
            
        // Execute API
        $api = new RemoveUserToPrivateContest();
        try 
        {
            $return_array = $api->ExecuteApi();
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
        
        $this->fail("Contestant was able to add a user to a contest.");                   
    }
}
?>
