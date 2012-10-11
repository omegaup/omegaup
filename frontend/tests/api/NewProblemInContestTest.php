<?php

require_once(SERVER_PATH . '/libs/FileUploader.php');

require_once '../NewProblemInContest.php';

require_once 'Utils.php';

class NewProblemInContestTest extends PHPUnit_Framework_TestCase
{
    
    public function setUp()
    {        
        Utils::ConnectToDB();
    }
    
    public function tearDown() 
    {
        Utils::cleanup();
    }
    
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
    
    private function setValidContext($contest_id = NULL, $zipName = 'testproblem.zip')
    {        
        // Set context
        if(is_null($contest_id))
        {
            $contest = ContestsDAO::getByPK(Utils::GetValidPublicContestId());
            RequestContext::set("contest_alias", $contest->getAlias());
        }
        else
        {
            $contest = ContestsDAO::getByPK($contest_id);
            RequestContext::set("contest_alias", $contest->getAlias());
        }
        RequestContext::set("title", Utils::CreateRandomString());
        RequestContext::set("alias", substr(Utils::CreateRandomString(), 0, 10));
        RequestContext::set("author_username", Utils::GetProblemAuthorUsername());
        RequestContext::set("validator", "token");
        RequestContext::set("time_limit", 5000);
        RequestContext::set("memory_limit", 32000);                
        RequestContext::set("source", "ACM");
        RequestContext::set("order", "normal");
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
    
    public function testCreateValidProblem($contest_id = NULL)
    {        
        
        // Set valid context for problem creation
        $contest_id = is_null($contest_id) ? Utils::GetValidPublicContestId() : $contest_id;
        $this->setValidContext($contest_id);
     
        // Login as judge
        $auth_token = Utils::LoginAsContestDirector();        
        
        // Execute API
        Utils::SetAuthToken($auth_token);
        $newProblemInContest = new NewProblemInContest($this->fileUploaderMock);
        
        try
        {
            $return_array = $newProblemInContest->ExecuteApi();
        }
        catch(ApiException $e)
        {
            var_dump($e->getArrayMessage());
            if(!is_null($e->getWrappedException()))
            {
                var_dump($e->getWrappedException()->getMessage());            
            }
            $this->fail("Unexpected exception");
        }        
        
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
        
        // Get problem-contest and verify it
        $contest_problems = ContestProblemsDAO::getByPK($contest_id, $problem->getProblemId());
        $this->assertNotNull($contest_problems);        
        $this->assertEquals(RequestContext::get("points"), $contest_problems->getPoints());        
        
        return (int)$problem->getProblemId();
    }
        
    
    public function testCreateProblemAsContestant()
    {        
        // Set context
        $this->setValidContext();
                
        // Login as contestant
        $auth_token = Utils::LoginAsContestant();
        Utils::SetAuthToken($auth_token);
        
        // Execute API
        $newProblemInContest = new NewProblemInContest($this->fileUploaderMock);                
                
        try
        {
            $return_array = $newProblemInContest->ExecuteApi();
        }
        catch(ApiException $e)
        {            
            // Validate error
            $exception_message = $e->getArrayMessage();            
            $this->assertEquals("User is not allowed to view this content.", $exception_message["error"]);
            $this->assertEquals("error", $exception_message["status"]);
            $this->assertEquals("HTTP/1.1 403 FORBIDDEN", $exception_message["header"]);
            
            // We're OK
            return;
        }
                
        var_dump($return_array);
        $this->fail("Contestant was able to create contest");                
    }
    
    
    public function testRequiredParameters()
    {               
        // Set valid context
        $this->setValidContext();
        
        // Array of valid keys
        $valid_keys = array(
            "title",            
            "validator",
            "time_limit",            
            "memory_limit",
            "source",
            "author_username",
            "alias"
        );
        
        foreach($valid_keys as $key)        
        {        
            
            $newProblem = new NewProblemInContest($this->fileUploaderMock);
            
            // Reset context            
            $this->setValidContext();
            
            // Login as contest director
            $auth_token = Utils::LoginAsContestDirector();
            
            // Set auth key
            Utils::SetAuthToken($auth_token);
            
            // Unset key
            unset($_REQUEST[$key]);
            
            try
            {
                // Execute API
                $newProblem->ExecuteApi();
                
            }
            catch(ApiException $e)
            {           
                // Exception is expected
                $exception_array = $e->getArrayMessage();            

                // Validate exception
                $this->assertNotNull($exception_array);
                $this->assertEquals("error", $exception_array["status"]);
                $this->assertEquals(100, $exception_array["errorcode"]);
                $this->assertEquals("HTTP/1.1 400 BAD REQUEST", $exception_array["header"]);
                $this->assertContains($key, $exception_array["error"]);
                                
                // We're OK
                continue;
            }
            
            $this->fail("Exception was expected. Parameter: ". $key);            
        }                   
    }
    
    public function testCreateValidProblemNoTestplan($contest_id = NULL)
    {        
        
        // Set valid context for problem creation
        $contest_id = is_null($contest_id) ? Utils::GetValidPublicContestId() : $contest_id;
        $this->setValidContext($contest_id, 'triangulos.zip');
     
        // Login as judge
        $auth_token = Utils::LoginAsContestDirector();        
        
        // Execute API
        Utils::SetAuthToken($auth_token);
        $newProblemInContest = new NewProblemInContest($this->fileUploaderMock);
        
        try
        {
            $return_array = $newProblemInContest->ExecuteApi();
        }
        catch(ApiException $e)
        {
            var_dump($e->getArrayMessage());
            if(!is_null($e->getWrappedException()))
            {
                var_dump($e->getWrappedException()->getMessage());            
            }
            $this->fail("Unexpected exception");
        }        
        
        // Verify response
        $this->assertEquals("ok", $return_array["status"]);        
        $this->assertEquals("cases/1.in", $return_array["uploaded_files"][0]);
        
        
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
        $this->assertFileExists($targetpath . "cases.zip");
        $this->assertFileExists($targetpath . "cases");
        $this->assertFileExists($targetpath . "inputname");
        $this->assertFileExists($targetpath . "statements". DIRECTORY_SEPARATOR . "es.html");
                
        // Default data
        $this->assertEquals(0, $problem->getVisits());
        $this->assertEquals(0, $problem->getSubmissions());
        $this->assertEquals(0, $problem->getAccepted());
        $this->assertEquals(0, $problem->getDifficulty());       
        
        // Get problem-contest and verify it
        $contest_problems = ContestProblemsDAO::getByPK($contest_id, $problem->getProblemId());
        $this->assertNotNull($contest_problems);        
        $this->assertEquals(RequestContext::get("points"), $contest_problems->getPoints());        
        
        return (int)$problem->getProblemId();
    }
    
    
    public function testCreateValidProblemNonUtf8Statement($contest_id = NULL)
    {        
        
        // Set valid context for problem creation
        $contest_id = is_null($contest_id) ? Utils::GetValidPublicContestId() : $contest_id;
        $this->setValidContext($contest_id, 'nonutf8stmt.zip');
     
        // Login as judge
        $auth_token = Utils::LoginAsContestDirector();        
        
        // Execute API
        Utils::SetAuthToken($auth_token);
        $newProblemInContest = new NewProblemInContest($this->fileUploaderMock);
        
        try
        {
            $return_array = $newProblemInContest->ExecuteApi();
        }
        catch(ApiException $e)
        {
            var_dump($e->getArrayMessage());
            if(!is_null($e->getWrappedException()))
            {
                var_dump($e->getWrappedException()->getMessage());            
            }
            $this->fail("Unexpected exception");
        }        
        
        // Verify response
        $this->assertEquals("ok", $return_array["status"]);                                        
        
        // Get problem info from DB
        $problem_mask = new Problems();
        $problem_mask->setTitle(RequestContext::get("title"));
        $problems = ProblemsDAO::search($problem_mask);                
        $this->assertEquals(1, count($problems));        
        $problem = $problems[0];
        
        // Verify problem contents.zip were copied
        $targetpath = PROBLEMS_PATH . DIRECTORY_SEPARATOR . $problem->getAlias() . DIRECTORY_SEPARATOR;                
        $this->assertFileExists($targetpath . "contents.zip");                        
        $this->assertFileExists($targetpath . "cases.zip");
        $this->assertFileExists($targetpath . "cases");
        $this->assertFileExists($targetpath . "inputname");
        $this->assertFileExists($targetpath . "statements". DIRECTORY_SEPARATOR . "es.html");
        $this->assertFileExists($targetpath . "statements". DIRECTORY_SEPARATOR . "es.markdown");
        
        // Verify we have the accents, lol
        $markdown_contents = file_get_contents($targetpath . "statements". DIRECTORY_SEPARATOR . "es.markdown");
        if (strpos($markdown_contents, "ó") === false)
        {
            $this->fail("ó not found when expected.");
        }          
        
        $html_contents = file_get_contents($targetpath . "statements". DIRECTORY_SEPARATOR . "es.html");
        if (strpos($html_contents, "ó") === false)
        {
            $this->fail("ó not found when expected.");
        }
    }        
}
?>
