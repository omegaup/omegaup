<?php

/**
 * Description of NewProblemTest
 *
 * @author joemmanuel
 */


require_once SERVER_PATH . 'controllers/problems.controller.php';

require_once 'Utils.php';
require_once 'UsersFactory.php';
require_once 'ContestsFactory.php';
require_once 'ProblemsFactory.php';

/*
 *  Tests de LoginController
 * 
 */
class CreateProbelmTest extends PHPUnit_Framework_TestCase
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
    
    private function setContext($zipName = 'testproblem.zip') {
        
        ProblemsFactory::setContext();               
        
        // Create fileUploader mock                        
        $this->fileUploaderMock = $this->getMock('FileUploader', array('IsUploadedFile', 'MoveUploadedFile'));
                        
        $this->fileUploaderMock->expects($this->any())
                ->method('IsUploadedFile')
                ->will($this->returnCallback(array($this, 'IsUploadedFile')));
        
        $this->fileUploaderMock->expects($this->any())
                ->method('MoveUploadedFile')
                ->will($this->returnCallback(array($this, 'MoveUploadedFile')));
    }
    
    public function testCreateValidProblem() {
         
        $this->setContext();
        
        $problemCreator = UsersFactory::createUser();
        
        $pc = new ProblemsController($this->fileUploaderMock);
        $pc->current_user_id = $problemCreator->getUserId();
        $pc->current_user_obj = $problemCreator;
                
        $return_array = $pc->create();                
        
        // Verify response
        $this->assertEquals("ok", $return_array["status"]);
        $this->assertEquals("testplan", $return_array["uploaded_files"][10]);
        
        
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
        $this->assertEquals(RequestContext::get("alias"), $problem->getAlias());
        $this->assertEquals(RequestContext::get("validator"), $problem->getValidator());
        $this->assertEquals(RequestContext::get("time_limit"), $problem->getTimeLimit());
        $this->assertEquals(RequestContext::get("memory_limit"), $problem->getMemoryLimit());                              
        $this->assertEquals(RequestContext::get("order"), $problem->getOrder());
        $this->assertEquals(RequestContext::get("source"), $problem->getSource());
        
        // Verify author username -> author id conversion
        $user = UsersDAO::getByPK($problem->getAuthorId());
        $this->assertEquals($user->getUsername(), RequestContext::get("author_username"));
        
        // Verify problem contents.zip were copied
        $targetpath = PROBLEMS_PATH . DIRECTORY_SEPARATOR . $problem->getAlias() . DIRECTORY_SEPARATOR;        
        
        $this->assertFileExists($targetpath . "contents.zip");                        
        $this->assertFileExists($targetpath . "testplan");
        $this->assertFileExists($targetpath . "cases");
        $this->assertFileExists($targetpath . "statements". DIRECTORY_SEPARATOR . "en.html");
                
        // Default data
        $this->assertEquals(0, $problem->getVisits());
        $this->assertEquals(0, $problem->getSubmissions());
        $this->assertEquals(0, $problem->getAccepted());
        $this->assertEquals(0, $problem->getDifficulty());                      
    }
    
    
    public function testAddProblemToContest() {
        
        $contestDirector = UsersFactory::createUser();
        
        $problemTitle = Utils::CreateRandomString();
        $problemContext = ProblemsFactory::createProblem($problemTitle);
        
        $contestTitle = Utils::CreateRandomString();
        $contestContext = ContestsFactory::createContest($contestTitle, 1, $contestDirector);
        
        $pc = new ProblemsController();
        $pc->current_user_id = $contestDirector->getUserId();
        $pc->current_user_obj = $contestDirector;
                
        RequestContext::set("contest_alias", $contestContext["context"]["alias"]);
        RequestContext::set("problem_alias", $problemContext["context"]["alias"]);
        RequestContext::set("points", 100);        
        
        try {
            $return_array = $pc->addToContest(); 
        }
        catch (Exception $e) {
            var_dump($e->getPrevious()->getLine());
            $this->fail("Unexpected exception.");
        }
        
        $this->assertEquals("ok", $return_array["status"]);
        
        // Get the problem & contest
        $problem = ProblemsDAO::getByAlias($problemContext["context"]["alias"]);
        $contest = ContestsDAO::getByAlias($contestContext["context"]["alias"]);
        $this->assertNotNull($problem);
        $this->assertNotNull($contest);
        
        // Get problem-contest and verify it
        $contest_problems = ContestProblemsDAO::getByPK($contest->getContestId(), $problem->getProblemId());
        $this->assertNotNull($contest_problems);        
        $this->assertEquals(RequestContext::get("points"), $contest_problems->getPoints()); 
    }
}
