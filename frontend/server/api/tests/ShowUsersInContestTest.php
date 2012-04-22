<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

require_once '../ShowUsersInContest.php';

require_once 'AddUserToPrivateContestTest.php';

require_once 'Utils.php';


class ShowUsersInContestTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {        
        Utils::ConnectToDB();
    }
    
    public function tearDown() 
    {
        Utils::cleanup();
    }
    
    public function testContestDirectorCanSeeUsersInContest()
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
            
        // Execute API
        $api = new ShowUsersInContest();
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
        $this->assertEquals(Utils::GetContestant2UserId(), $return_array["users"][0]["user_id"]);
        $this->assertEquals(Utils::GetContestant2Username(), $return_array["users"][0]["username"]);
        
    }
}