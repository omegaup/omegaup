<?php

require_once(SERVER_PATH . '/libs/FileUploader.php');
require_once '../UpdateProblem.php';

require_once 'NewProblemInContestTest.php';

require_once 'Utils.php';


class UpdateProblemTest extends PHPUnit_Framework_TestCase
{
    private $fileUploaderMock;
    
    public function IsUploadedFile($filename)
    {        
        return file_exists($filename);
    }
    
    public function MoveUploadedFile()
    {
        $filename = func_get_arg(0);
        $targetpath = func_get_arg(1);
                        
        return copy($filename, $targetpath);
    }
    
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
        $_FILES = array();
        
        // Login as contest director and problem author
        $auth_token = Utils::LoginAsProblemAuthor();
        
        // Set contest to api
        RequestContext::set("problem_alias", $problem->getAlias());
        
        RequestContext::set("title", Utils::CreateRandomString());                
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
        
        // Verify data in DB
        $problem_mask = new Problems();
        $problem_mask->setTitle(RequestContext::get("title"));
        $problems = ProblemsDAO::search($problem_mask);
        
        // Check that we only retreived 1 element
        $this->assertEquals(1, count($problems));        
        $problem = $problems[0];
        
        // Verify contest was found
        $this->assertNotNull($problem);
        $this->assertNotNull($problem->getProblemId());
        
        // Verify DB data
        $this->assertEquals(RequestContext::get("title"), $problem->getTitle());        
        $this->assertEquals(RequestContext::get("validator"), $problem->getValidator());
        $this->assertEquals(RequestContext::get("time_limit"), $problem->getTimeLimit());
        $this->assertEquals(RequestContext::get("memory_limit"), $problem->getMemoryLimit());                              
        $this->assertEquals(RequestContext::get("order"), $problem->getOrder());
        
    }
    
    public function setProblemContext()
    {
        // Set file upload context
        $_FILES['problem_contents']['tmp_name'] = 'updatedproblem.zip';               
        
        // Create fileUploader mock                        
        $this->fileUploaderMock = $this->getMock('FileUploader', array('IsUploadedFile', 'MoveUploadedFile'));
                        
        $this->fileUploaderMock->expects($this->any())
                ->method('IsUploadedFile')
                ->will($this->returnCallback(array($this, 'IsUploadedFile')));
        
        $this->fileUploaderMock->expects($this->any())
                ->method('MoveUploadedFile')
                ->will($this->returnCallback(array($this, 'MoveUploadedFile')));      
    }
    
    public function testUpdateProblemWithZip()
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
        $_FILES = array();
        
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
        $this->setProblemContext();
        
        //Execute api
        Utils::SetAuthToken($auth_token);
        $updateProblem = new UpdateProblem($this->fileUploaderMock);
        
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
        
        // Verify problem contents.zip were copied
        $targetpath = PROBLEMS_PATH . DIRECTORY_SEPARATOR . $problem->getAlias() . DIRECTORY_SEPARATOR;        
        
        $this->assertFileExists($targetpath . "contents.zip");                        
        $this->assertFileExists($targetpath . "testplan");
        $this->assertFileExists($targetpath . "cases");
        $this->assertFileExists($targetpath . "cases.zip");
        $this->assertFileExists($targetpath . "statements". DIRECTORY_SEPARATOR . "es.html");
                
    }
        
}
?>
