<?php

require_once '../ShowProblemRuns.php';

require_once 'NewRunTest.php';
require_once 'NewContestTest.php';
require_once 'NewProblemInContestTest.php';

require_once 'Utils.php';

class ShowProblemRunsTest extends PHPUnit_Framework_TestCase
{
    
    private $inittime;
    private $counttime;
    
    private function getNextTime()
    {
        $this->counttime++;
        return Utils::GetTimeFromUnixTimestam($this->inittime + $this->counttime);
    }
    
    public function setUp()
    {                
        Utils::ConnectToDB();
        
        $this->inittime = Utils::GetPhpUnixTimestamp();
        $this->counttime = 0;
    }
    
    public function tearDown() 
    {
        Utils::cleanup();
    }
    
    
    public function testContestantWithNRuns($n = NULL, $contest_id = NULL, $problem_id = NULL, $auth_token = NULL)    
    {
        if(is_null($n))
        {
            $n = 3;
        }
        
        if(is_null($contest_id))
        {
            // Create clean contest and a problem inside it
            $contestCreator = new NewContestTest();
            $contest_id = $contestCreator->testCreateValidContest(1);                           
        }
        
        if(is_null($problem_id))
        {
            $problemCreator = new NewProblemInContestTest();
            $problem_id = $problemCreator->testCreateValidProblem($contest_id);
        }
        
        // Create 3 runs with Contestant user
        $runCreator = new NewRunTest();
        $run = array();        
        for($i = 0; $i < $n; $i++)
        {            
            $run[$i] = RunsDAO::getByPK($runCreator->testNewValidRun($contest_id, $problem_id));                        
            
            // Alter run timestamp            
            $run[$i]->setTime($this->getNextTime());            
            RunsDAO::save($run[$i]);            
        }
        
        // Login as contestant
        $auth_token = Utils::LoginAsContestant();
        
        // Set context
        Utils::SetAuthToken($auth_token);
        
        $problem = ProblemsDAO::getByPK($run[0]->getProblemId());        
        RequestContext::set("problem_alias", $problem->getAlias());
        
        // Execute API
        $showProblemRuns = new ShowProblemRuns();
        try
        {
            $return_array = $showProblemRuns->ExecuteApi();
        }
        catch(ApiException $e)
        {
            var_dump($e->getArrayMessage());            
            var_dump($e->getWrappedException()->getMessage());            
            $this->fail("Unexpected exception");
        }
        
        // Validate response
        $this->assertEquals("ok", $return_array["status"]);
        
        // Validate runs backwards
        $lower_limit = max(0, $n - 5);
        for($runs_i = $n - 1, $return_i = 0; $runs_i >= $lower_limit; $runs_i--, $return_i++)
        {
            $this->assertEquals($run[$runs_i]->getGuid(), $return_array[$return_i]["guid"]);
            $this->assertEquals($run[$runs_i]->getStatus(), "new");
        }
        
        return $run; 
    }
    
    public function testContestant6Runs()
    {
        $this->testContestantWithNRuns(6);
    }
    
    public function testRunsByDifferentContestantsNotMixed()
    {
        // Create problem and contest to be shared by 2 users
        $contestCreator = new NewContestTest();
        $contest_id = $contestCreator->testCreateValidContest(1);
        
        $problemCreator = new NewProblemInContestTest();
        $problem_id = $problemCreator->testCreateValidProblem($contest_id);
        
        // Login as contestant
        $auth_token_contestant = Utils::LoginAsContestant();
        
        // Login as contestant2
        $auth_token_contestant2 = Utils::LoginAsContestant2();
        
        // Create 2 runs with first contestant
        $this->testContestantWithNRuns(2, $contest_id, $problem_id, $auth_token_contestant);
        
        // Create 2 runs with second contestant
        $this->testContestantWithNRuns(2, $contest_id, $problem_id, $auth_token_contestant2);                
    }
        
    
    public function testContestDirectorSeeingAllRuns()
    {
        // Create problem and contest to be shared by 2 users
        $contestCreator = new NewContestTest();
        $contest_id = $contestCreator->testCreateValidContest(1);
        
        $problemCreator = new NewProblemInContestTest();
        $problem_id = $problemCreator->testCreateValidProblem($contest_id);
        
        // Login as contestant
        $auth_token_contestant = Utils::LoginAsContestant();
        
        // Login as contestant2
        $auth_token_contestant2 = Utils::LoginAsContestant2();
        
        // Create 2 runs with first contestant
        $runs[] = $this->testContestantWithNRuns(2, $contest_id, $problem_id, $auth_token_contestant);
        
        // Create 2 runs with second contestant
        $runs[] = $this->testContestantWithNRuns(2, $contest_id, $problem_id, $auth_token_contestant2);
        
        // Login as contest director
        $auth_token = Utils::LoginAsContestDirector();
        
        // Set context for our contest director
        Utils::SetAuthToken($auth_token);        
        $problem = ProblemsDAO::getByPK($problem_id);        
        RequestContext::set("problem_alias", $problem->getAlias());
        
        // Execute API
        $showProblemRuns = new ShowProblemRuns();
        try
        {
            $return_array = $showProblemRuns->ExecuteApi();
        }
        catch(ApiException $e)
        {
            var_dump($e->getArrayMessage());            
            var_dump($e->getWrappedException()->getMessage());            
            $this->fail("Unexpected exception");
        }
        
        // Validate response
        $this->assertEquals("ok", $return_array["status"]);
        $this->assertEquals(5, count($return_array));
        
        // Assert we have our runs
        $this->assertEquals($runs[1][1]->getGuid(), $return_array[0]["guid"]);
        $this->assertEquals($runs[1][0]->getGuid(), $return_array[1]["guid"]);
        $this->assertEquals($runs[0][1]->getGuid(), $return_array[2]["guid"]);
        $this->assertEquals($runs[0][0]->getGuid(), $return_array[3]["guid"]);                        
    }       
}

?>
