<?php

/**
 * Problem: PHPUnit does not support is_uploaded_file and move_uploaded_file 
 * native functions of PHP to move files around needed for store zip contents
 * in the required places.
 * 
 * Solution: We abstracted those PHP native functions in an object FileUploader.
 * We need to create a new FileUploader object that uses our own implementations.
 * 
 */
class FileUploaderMock extends FileUploader {
    
    public function IsUploadedFile($filename) {        
        return file_exists($filename);
    }
    
    public function MoveUploadedFile($filename, $targetPath) {
        $filename = func_get_arg(0);
        $targetpath = func_get_arg(1);
                        
        return copy($filename, $targetpath);
    }
}



/**
 * Description of ProblemsFactory
 *
 * @author joemmanuel
 */
class ProblemsFactory {
	/**
	 * Returns a Request object with valid info to create a problem and the 
	 * author of the problem
	 * 
	 * @param string $title
	 * @param string $zipName
	 * @return Array
	 */
	public static function getRequest($zipName = null, $title = null, $public = 1, Users $author = null, $languages = null) {
		if (is_null($author)) {
			$author = UserFactory::createUser();
		}

		if (is_null($title)){
			$title = Utils::CreateRandomString();       
		}

		if (is_null($zipName)) {
			$zipName = OMEGAUP_RESOURCES_ROOT.'testproblem.zip';
		}

		$r = new Request();
		$r["title"] = $title;
		$r['alias'] = substr(preg_replace('/[^a-zA-Z0-9_-]/', '', str_replace(' ', '-', $r['title'])), 0, 32);
		$r["author_username"] = $author->getUsername();
		$r["validator"] = "token";
		$r["time_limit"] = 5000;
		$r["overall_wall_time_limit"] = 60000;
		$r["validator_time_limit"] = 30000;
		$r["extra_wall_time"] = 0;
		$r["memory_limit"] = 32000;                
		$r["source"] = "yo";
		$r["order"] = "normal";
		$r["public"] = $public;        	
		$r["output_limit"] = 10240;        	
		if ($languages == null) {
			$r["languages"] = 'c,cpp,py';
		} else {
			$r["languages"] = $languages;
		}
		$r["stack_limit"] = 10000;

		// Set file upload context
		$_FILES['problem_contents']['tmp_name'] = $zipName; 

		return array (
				"request" => $r,
				"author" => $author,
				"zip_path" => $zipName);
    }
    
	public static function createProblemWithAuthor(Users $author) {
		return self::createProblem(null, null, 1, $author);
	}
	
    /**
     * 
     */
    public static function createProblem($zipName = null, $title = null, $public = 1, Users $author = null, $languages = null) {
        
		if (is_null($zipName)) {
			$zipName = OMEGAUP_RESOURCES_ROOT.'testproblem.zip';
		}
		
		// Get a user
        $problemData = self::getRequest($zipName, $title, $public, $author, $languages);
		$r = $problemData["request"];
		$problemAuthor = $problemData["author"];				
		
		// Login user
		$r["auth_token"] = OmegaupTestCase::login($problemAuthor);

		// Get File Uploader Mock and tell Omegaup API to use it
		FileHandler::SetFileUploader(new FileUploaderMock());

		// Call the API				
		ProblemController::apiCreate($r);						
        		
		// Clean up our mess
        unset($_REQUEST);
		
        return array (
            "request" => $r, 
            "author" => $problemAuthor,
            );
    }
}

