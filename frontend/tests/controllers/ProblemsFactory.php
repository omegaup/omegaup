<?php

/**
 * 
class FileUploaderMock extends FileUploader {
    
    public function IsUploadedFile($filename) {        
        return file_exists($filename);
    }
    
    public function MoveUploadedFile() {
        $filename = func_get_arg(0);
        $targetpath = func_get_arg(1);
                        
        return copy($filename, $targetpath);
    }
}
 * 
 */


/**
 * Description of ProblemsFactory
 *
 * @author joemmanuel
 */
class ProblemsFactory {
         
	/**
	 * Returns a Request object with valid info to create a problem
	 * 
	 * @param string $title
	 * @param string $zipName
	 * @return Request
	 */
    public static function getRequest($zipName = 'testproblem.zip', $title = null) {
        
        $author = UserFactory::createUser();
        
        if (is_null($title)){
            $title = Utils::CreateRandomString();       
        }
        $alias = substr(Utils::CreateRandomString(), 0, 10);
        
		$r = new Request();
        $r["title"] = $title;
        $r["alias"] = $alias;
        $r["author_username"] = $author->getUsername();
        $r["validator"] = "token";
        $r["time_limit"] = 5000;
        $r["memory_limit"] = 32000;                
        $r["source"] = "yo";
        $r["order"] = "normal";
        $r["public"] = "1";        
        
        // Set file upload context
        $_FILES['problem_contents']['tmp_name'] = $zipName; 
        
        return $r;
    }
    
    /**
     * 
     */
    public static function createProblem($title, $zipName = 'testproblem.zip') {
        
        $problemCreator = UsersFactory::createUser();
        $context = self::getRequest();
        
        $pc = new ProblemsController(new FileUploaderMock());
        $pc->current_user_id = $problemCreator->getUserId();
        $pc->current_user_obj = $problemCreator;
                
        $pc->create();        
        
        return array (
            "context" => $context, 
            "problemCreator" => $problemCreator,
            );
    }
}

