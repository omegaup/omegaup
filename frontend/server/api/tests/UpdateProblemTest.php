<?php


require_once '../UpdateProblem.php';

require_once 'NewProblemInContestTest.php';

require_once 'Utils.php';


class UpdateProblemTest extends PHPUnit_Framework_TestCase
{
    
    public function testBasicUpdateProblem()
    {
        // Create a clean contest and get the ID
        $contestCreator = new NewContestTest();
        $contest_id = $contestCreator->testCreateValidContest(1);
                        
        // Create a problem in given contest
        $problemCreator = new NewProblemInContestTest();
        $problem_id = $problemCreator->testCreateValidProblem($contest_id);
        $problem = ProblemsDAO::getByPK($problem_id);
        
        // Clean up context
        $_REQUEST = array();
        
        // Login as contest director and problem author
        $auth_token = Utils::LoginAsProblemAuthor();
        
        // Set contest to api
        RequestContext::set("problem_alias", $problem->getAlias());
        
        RequestContext::set("title", "New title");                
        RequestContext::set("validator", "token-numeric");
        RequestContext::set("time_limit", 6000);
        RequestContext::set("memory_limit", 64000);                
        RequestContext::set("source", "LOL");
        RequestContext::set("order", "normal");
        RequestContext::set("points", 100);
        
        //Execute api
        Utils::SetAuthToken($auth_token);
        $updateProblem = new UpdateProblem();
        
        try
        {
            $returnArray = $updateProblem->ExecuteApi();
        }
        catch(ApiException $e)
        {
            var_dump($e->getArrayMessage());            
            $this->fail("Unexpected exception");
        }
        
        // Validate return status
        $this->assertEquals("ok", $returnArray["status"]);
    }
        
}
?>
