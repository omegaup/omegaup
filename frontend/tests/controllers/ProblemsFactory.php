<?php

require_once SERVER_PATH . 'controllers/problems.controller.php';

/**
 * 
 */
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


/**
 * Description of ProblemsFactory
 *
 * @author joemmanuel
 */
class ProblemsFactory {
                   
    public static function setContext($title = null, $zipName = 'testproblem.zip') {
        
        $author = UsersFactory::createUser();
        
        if (is_null($title)){
            $title = Utils::CreateRandomString();       
        }
        $alias = substr(Utils::CreateRandomString(), 0, 10);
        
        RequestContext::set("title", $title);
        RequestContext::set("alias", $alias);
        RequestContext::set("author_username", $author->getUsername());
        RequestContext::set("validator", "token");
        RequestContext::set("time_limit", 5000);
        RequestContext::set("memory_limit", 32000);                
        RequestContext::set("source", "ACM");
        RequestContext::set("order", "normal");
        RequestContext::set("public", "1");        
        
        // Set file upload context
        $_FILES['problem_contents']['tmp_name'] = $zipName; 
        
        return array (
            "title" => $title,
            "author" => $author,
            "alias" => $alias,
            );
    }
    
    /**
     * 
     */
    public static function createProblem($title, $zipName = 'testproblem.zip') {
        
        $problemCreator = UsersFactory::createUser();
        $context = self::setContext();
        
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

