<?php

require_once(SERVER_PATH . '/libs/Grader.php');

require_once 'NewRunTest.php';
require_once 'NewContestTest.php';
require_once 'NewProblemInContestTest.php';

require_once 'Utils.php';

class GraderTest extends PHPUnit_Framework_TestCase
{ 
    public function setUp()
    {        
        Utils::ConnectToDB();
    }
    
    public function tearDown() 
    {
        Utils::cleanup();
    }
    
    public function testGraderTest()
    {
        // Create new run
        $runCreator = new NewRunTest();
        $run = RunsDAO::getByPK($runCreator->testNewValidRun());
        
        // Call grader
        $grader = new Grader();
        $grader->Grade($run->getRunId());
        
        // Check that grader received run
        sleep(10);
	RunsDAO::unsetCache();
        $run = RunsDAO::getByPK($run->getRunId());
        
        $this->assertNotEquals("new", $run->getStatus());
    }
}

?>
