<?php

/**
 * 
 * Please read full (and updated) documentation at: 
 * https://github.com/omegaup/omegaup/wiki/Arena 
 *
 *
 * 
 * POST /contests/:id:/problem/new
 * Si el usuario tiene permisos de juez o admin, crea un nuevo problema para el concurso :id
 *
 * */

require_once("ApiHandler.php");

require_once(SERVER_PATH . '/libs/FileHandler.php');
require_once(SERVER_PATH . '/libs/FileUploader.php');
require_once(SERVER_PATH . '/libs/ZipHandler.php');
require_once(SERVER_PATH . '/libs/ProblemContentsZipValidator.php');
require_once(SERVER_PATH . '/libs/Markdown/markdown.php');

class NewProblemInContest extends ApiHandler
{            
    public function NewProblemInContest(FileUploader $fileUploader = NULL)
    {
        // Set file uploader to the file handler
        if(is_null($fileUploader))
        {
            $fileUploader = new FileUploader();            
        }
        
        FileHandler::SetFileUploader($fileUploader);
    }
    
    private $filesToUnzip;
    
    protected function RegisterValidatorsToRequest()
    {   
       ValidatorFactory::stringNotEmptyValidator()->addValidator(new CustomValidator(
                function ($value)
                {
                    // Check if the contest exists
                    return ContestsDAO::getByAlias($value);
                }, "Contest is invalid."))
            ->validate(RequestContext::get("contest_alias"), "contest_alias");

            
        // Only director is allowed to create problems in contest
        try
        {
            $contest = ContestsDAO::getByAlias(RequestContext::get("contest_alias"));
        }
        catch(Exception $e)
        {  
            // Operation failed in the data layer
           throw new ApiException( ApiHttpErrors::invalidDatabaseOperation(), $e );    
        }                
        
        if($contest->getDirectorId() !== $this->_user_id)
        {
            throw new ApiException(ApiHttpErrors::forbiddenSite());
        }
        
                
        ValidatorFactory::numericValidator()->addValidator(new CustomValidator(
                function ($value)
                {
                    // Check if the contest exists
                    return UsersDAO::getByPK($value);
                }, "author_id is invalid."))
            ->validate(RequestContext::get("author_id"), "author_id");
                
        ValidatorFactory::stringNotEmptyValidator()->validate(
                RequestContext::get("title"),
                "title");
        
        ValidatorFactory::stringNotEmptyValidator()->validate(
                RequestContext::get("source"),
                "source");
                
        ValidatorFactory::stringOfMaxLengthValidator(32)->validate(
                RequestContext::get("alias"),
                "alias");
        
        ValidatorFactory::enumValidator(array("remote", "literal", "token", "token-caseless", "token-numeric"))
                ->validate(RequestContext::get("validator"), "validator");
        
        ValidatorFactory::numericRangeValidator(0, INF)
                ->validate(RequestContext::get("time_limit"), "time_limit");
        
        ValidatorFactory::numericRangeValidator(0, INF)
                ->validate(RequestContext::get("memory_limit"), "memory_limit");                
                
        ValidatorFactory::enumValidator(array("normal", "inverse"))
                ->validate(RequestContext::get("order"), "order"); 
        
        ValidatorFactory::numericRangeValidator(0, INF)
                ->validate(RequestContext::get("points"), "points");
        
        if(!FileHandler::GetFileUploader()->IsUploadedFile($_FILES['problem_contents']['tmp_name']))
        {
            throw new ApiException(ApiHttpErrors::invalidParameter("problem_contents is missing."));
        }
        
        // Validate zip contents
        $zipValidator = new Validator();
        $zipValidator->addValidator(new ProblemContentsZipValidator)
                     ->validate($_FILES['problem_contents']['tmp_name'], 'problem_contents');                
        
        // Save files to unzip                
        $this->filesToUnzip = $zipValidator->getValidator(0)->filesToUnzip;        
    }       
    
    protected function GenerateResponse() 
    {
                
        // Populate a new Problem object
        $problem = new Problems();
        $problem->setPublic(false);
        $problem->setAuthorId(RequestContext::get("author_id"));
        $problem->setTitle(RequestContext::get("title"));
        $problem->setAlias(RequestContext::get("alias"));
        $problem->setValidator(RequestContext::get("validator"));
        $problem->setTimeLimit(RequestContext::get("time_limit"));
        $problem->setMemoryLimit(RequestContext::get("memory_limit"));
        $problem->setVisits(0);
        $problem->setSubmissions(0);
        $problem->setAccepted(0);
        $problem->setDifficulty(0);
        $problem->setSource(RequestContext::get("source"));
        $problem->setOrder(RequestContext::get("order"));                              
                
        // Insert new problem
        try
        {
            // Get contest 
            $contest = ContestsDAO::getByAlias(RequestContext::get("contest_alias"));
            
            //Begin transaction
            ProblemsDAO::transBegin();

            // Save the contest object with data sent by user to the database
            ProblemsDAO::save($problem);

            // Save relationship between problems and contest_id
            $relationship = new ContestProblems( array(
                "contest_id" => $contest->getContestId(),
                "problem_id" => $problem->getProblemId(),
                "points"     => RequestContext::get("points")));
            ContestProblemsDAO::save($relationship);
            
            // Create file after we know that alias is unique
            try 
            {
                // Create paths
                $dirpath = PROBLEMS_PATH . RequestContext::get("alias");
                $filepath = $dirpath . DIRECTORY_SEPARATOR . 'contents.zip';                
                
                // Drop contents into path required
                FileHandler::MakeDir($dirpath);                
                FileHandler::MoveFileFromRequestTo('problem_contents', $filepath);                                
                ZipHandler::DeflateZip($filepath, $dirpath, $this->filesToUnzip);
                
                // Transform statements from markdown to HTML
                $statements = preg_grep('/^statements\/[a-zA-Z]{2}\.markdown$/', $this->filesToUnzip);
                
                foreach($statements as $statement)
                {
                    $filepath = $dirpath . DIRECTORY_SEPARATOR . $statement;
                    $file_contents = FileHandler::ReadFile($filepath);
                    
                    // Markup
                    $file_contents = markdown($file_contents);
                    
                    // Overwrite file
                    $lang = basename($statement, ".markdown");
                    FileHandler::CreateFile($dirpath . DIRECTORY_SEPARATOR . "statements" . DIRECTORY_SEPARATOR . $lang . ".html", $file_contents);
                }
                
            }
            catch (Exception $e)
            {
                throw new ApiException( ApiHttpErrors::invalidFilesystemOperation("Unable to process problem_contents given. Please check the format. "), $e );
            }

            //End transaction
            ProblemsDAO::transEnd();
        }
        catch(ApiException $e)
        {
            // Operation failed in the data layer, rollback transaction 
            ProblemsDAO::transRollback();
            
            throw $e;
        }
        catch(Exception $e)
        {  
           // Operation failed in the data layer, rollback transaction 
            ProblemsDAO::transRollback();
            
            // Alias may be duplicated, 1062 error indicates that
            if(strpos($e->getMessage(), "1062") !== FALSE)
            {
                throw new ApiException( ApiHttpErrors::duplicatedEntryInDatabase("alias"), $e);    
            }
            else
            {
               throw new ApiException( ApiHttpErrors::invalidDatabaseOperation(), $e );    
            }
        }                
    }    
}

?>
