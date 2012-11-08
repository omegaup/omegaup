<?php
require_once("ApiHandler.php");
require_once("NewProblemInContest.php");

require_once(SERVER_PATH . '/libs/FileHandler.php');
require_once(SERVER_PATH . '/libs/FileUploader.php');
require_once(SERVER_PATH . '/libs/ZipHandler.php');
require_once(SERVER_PATH . '/libs/ProblemContentsZipValidator.php');
require_once(SERVER_PATH . '/libs/Markdown/markdown.php');
require_once(SERVER_PATH . '/libs/Grader.php');
require_once(SERVER_PATH . '/libs/Cache.php');

class UpdateProblem extends ApiHandler
{   
    
    private $problem;
    private $filesToUnzip;
    private $casesFiles;
    
    public function UpdateProblem(FileUploader $fileUploader = NULL)
    {
        // Set file uploader to the file handler
        if(is_null($fileUploader))
        {
            $fileUploader = new FileUploader();            
        }
        
        FileHandler::SetFileUploader($fileUploader);
    }

    protected function RegisterValidatorsToRequest()
    {   
        // Alias is required to know which contest to edit
        if (RequestContext::get("problem_alias") == null)
        {
            throw new ApiException( ApiHttpErrors::invalidParameter("Problem alias should be specified.") );    
        }
        
        // Get the contest from the DB
        try
        {
            $this->problem = ProblemsDAO::getByAlias(RequestContext::get("problem_alias"));
        }
        catch(Exception $e)
        {            
            throw new ApiException( ApiHttpErrors::invalidDatabaseOperation(), $e );
        }
        
        if ($this->problem == null)
        {
            throw new ApiException( ApiHttpErrors::invalidParameter("Problem alias specified does not exists.") );
        }              
        
        if(!Authorization::CanEditProblem($this->_user_id, $this->problem))
        {            
            throw new ApiException(ApiHttpErrors::forbiddenSite());
        }        
                           
        if (!is_null(RequestContext::get("title")))  
        {
            ValidatorFactory::stringNotEmptyValidator()->validate(
                    RequestContext::get("title"),
                    "title");
        }
        
        if (!is_null(RequestContext::get("source")))
        {
            ValidatorFactory::stringNotEmptyValidator()->validate(
                    RequestContext::get("source"),
                    "source");
        }        
        
        if (!is_null(RequestContext::get("validator")))
        {
            ValidatorFactory::enumValidator(array("remote", "literal", "token", "token-caseless", "token-numeric"))
                    ->validate(RequestContext::get("validator"), "validator");
        }
        
        if (!is_null(RequestContext::get("time_limit")))
        {
            ValidatorFactory::numericRangeValidator(0, INF)
                    ->validate(RequestContext::get("time_limit"), "time_limit");
        }
        
        if (!is_null(RequestContext::get("memory_limit")))
        {
            ValidatorFactory::numericRangeValidator(0, INF)
                    ->validate(RequestContext::get("memory_limit"), "memory_limit");                
        }
         
        if (!is_null(RequestContext::get("order")))
        {
            ValidatorFactory::enumValidator(array("normal", "inverse"))
                ->validate(RequestContext::get("order"), "order"); 
        }
        
        if (!is_null(RequestContext::get("points")))
        {
            ValidatorFactory::numericRangeValidator(0, INF)
                    ->validate(RequestContext::get("points"), "points");
        }
        
        if (isset($_FILES['problem_contents']))            
        {            
            NewProblemInContest::ValidateZip($this->filesToUnzip, $this->casesFiles);                
        }
        
    }       
    
    protected function GenerateResponse() 
    {
        // Update the Problem object
        
        if (!is_null(RequestContext::get("public")))
        {
            $this->problem->setPublic(RequestContext::get("public"));
        }
        
        if (!is_null(RequestContext::get("title")))
        {
            $this->problem->setTitle(RequestContext::get("title"));
        }              
        
        if (!is_null(RequestContext::get("validator")))
        {
            $this->problem->setValidator(RequestContext::get("validator"));
        }
        
        if (!is_null(RequestContext::get("time_limit")))
        {
            $this->problem->setTimeLimit(RequestContext::get("time_limit"));
        }
        
        if (!is_null(RequestContext::get("memory_limit")))
        {
            $this->problem->setMemoryLimit(RequestContext::get("memory_limit"));
        }

        if (!is_null(RequestContext::get("source")))
        {
            $this->problem->setSource(RequestContext::get("source"));
        }
        
        if (!is_null(RequestContext::get("order")))
        {
            $this->problem->setOrder(RequestContext::get("order"));                              
        }
                      
                
        // Insert new problem
        try
        {
            //Begin transaction
            ProblemsDAO::transBegin();
            
            // Save the contest object with data sent by user to the database
            ProblemsDAO::save($this->problem);
            
            if (isset($_FILES['problem_contents']))         
            {                
                // DeployProblemZip requires alias => problem_alias
                RequestContext::set("alias", RequestContext::get("problem_alias"));
                
                NewProblemInContest::DeployProblemZip($this->filesToUnzip, $this->casesFiles, true); 
                $this->addResponse("uploaded_files", $this->filesToUnzip);
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
            var_dump($e->getMessage());
           // Operation failed in the data layer, rollback transaction 
            ProblemsDAO::transRollback();
                       
            throw new ApiException( ApiHttpErrors::invalidDatabaseOperation(), $e );                
	}  

	$grader = new Grader();
        
        // Call Grader
        try
	{
            $runs = RunsDAO::search(new Runs(array(
                "problem_id" => $this->problem->getProblemId()
	    )));

	    foreach ($runs as $run) {
		    $run->setStatus('new');
		    $run->setVeredict('JE');
		    RunsDAO::save($run);
		    $grader->Grade($run->getRunId());
	    }
        }
        catch(Exception $e)
        {
            Logger::error($e);
            throw new ApiException( ApiHttpErrors::invalidDatabaseOperation(), $e );
	}

	if (RequestContext::get("redirect") === "true") {
		header('Location: ' . $_SERVER['HTTP_REFERER']);
	}
                
        // All clear
        $this->addResponse("status", "ok");
        
        // Invalidar cache @todo invalidar todos los lenguajes
        $statementCache = new Cache(Cache::PROBLEM_STATEMENT, $this->problem->getAlias() . "-es");
        $statementCache->delete();
        
    }  
}
