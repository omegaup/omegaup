<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

require_once '../AddUserToPrivateContest.php';

require_once 'NewContestTest.php';

require_once 'Utils.php';


class AddUserToPrivateContestTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {        
        Utils::ConnectToDB();
    }
    
    public function tearDown() 
    {
        Utils::cleanup();
    }
    
    public function testContestDirectorAddsUserToContest()
    {
        // Create our contest
        $contestCreator = new NewContestTest();
        $contest_id = $contestCreator->testCreateValidContest(1); 
        $contest = ContestsDAO::getByPK($contest_id);
        
        // Login as a director
        $auth_token = Utils::LoginAsContestDirector();
        Utils::SetAuthToken($auth_token);
        
        // Set the context
        RequestContext::set('contest_alias', $contest->getAlias());
        RequestContext::set('user_id', Utils::GetContestant2UserId());
            
        // Execute API
        $api = new AddUserToPrivateContest();
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
        
        // Check the DB
        $contest_user = ContestsUsersDAO::getByPK(RequestContext::get('user_id'), $contest_id);
        $this->assertEquals($contest_id, $contest_user->getContestId());
        $this->assertEquals(RequestContext::get('user_id'), $contest_user->getUserId());
        $this->assertEquals("0000-00-00 00:00:00", $contest_user->getAccessTime());               
    }
    
    public function testContestantNotAbleToAddUserToContest()
    {
        // Create our contest
        $contestCreator = new NewContestTest();
        $contest_id = $contestCreator->testCreateValidContest(1); 
        $contest = ContestsDAO::getByPK($contest_id);
        
        // Login as a director
        $auth_token = Utils::LoginAsContestant();
        Utils::SetAuthToken($auth_token);
        
        // Set the context
        RequestContext::set('contest_alias', $contest->getAlias());
        RequestContext::set('user_id', Utils::GetContestant2UserId());
            
        // Execute API
        $api = new AddUserToPrivateContest();
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
