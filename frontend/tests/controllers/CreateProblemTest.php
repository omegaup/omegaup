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
        
        $author = UsersFactory::createUser();
        
        RequestContext::set("title", Utils::CreateRandomString());
        RequestContext::set("alias", substr(Utils::CreateRandomString(), 0, 10));
        RequestContext::set("author_username", $author->getUsername());
        RequestContext::set("validator", "token");
        RequestContext::set("time_limit", 5000);
        RequestContext::set("memory_limit", 32000);                
        RequestContext::set("source", "ACM");
        RequestContext::set("order", "normal");
        RequestContext::set("public", "1");
        RequestContext::set("points", 1);

        // Set file upload context
        $_FILES['problem_contents']['tmp_name'] = $zipName;               
        
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
}
