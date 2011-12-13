<?php

/**
 * 
 * Please read full (and updated) documentation at: 
 * https://github.com/omegaup/omegaup/wiki/Arena 
 *
 * 
 * GET /runs/problem/:id
 * Si el usuario tiene permiso, regresa las referencias a las últimas 5 soluciones a un problema en particular que el mismo usuario ha enviado, y su estado y calificación.
 *
 * */

require_once("ApiHandler.php");
require_once(SERVER_PATH . '/libs/FileHandler.php');

class ShowRun extends ApiHandler
{
    
    private $myRun;
    
    protected function RegisterValidatorsToRequest()
    {
        ValidatorFactory::numericValidator()->addValidator(new CustomValidator(
            function ($value)
            {
                // Check if the contest exists
                return RunsDAO::getByPK($value);
            }, "Run is invalid."))
        ->validate(RequestContext::get("run_id"), "run_id");
            
        try
        {
            // If user is not judge, must be the run's owner.
            $this->myRun = RunsDAO::getByPK(RequestContext::get("run_id"));
            $contest = ContestsDAO::getByPK($this->myRun->getContestId());
        }
        catch(Exception $e)
        {
            // Operation failed in the data layer
           throw new ApiException( ApiHttpErrors::invalidDatabaseOperation());        
        }                        
        
        if(!($this->myRun->getUserId() == $this->_user_id || $contest->getDirectorId() == $this->_user_id))
        {
           throw new ApiException(ApiHttpErrors::forbiddenSite());
        }                
    }   

    protected function GenerateResponse() 
    {
        
        // Fill response
        $relevant_columns = array( "run_id", "language", "status", "veredict", "runtime", "memory", "score", "contest_score", "time", "submit_delay" );
        $this->addResponseArray($this->myRun->asFilteredArray($relevant_columns));
        
        try
        {
            // Get source code
            $filepath = RUNS_PATH . $this->myRun->getGuid();
            RequestContext::set("source", FileHandler::ReadFile($filepath));                                        
        }
        catch (Exception $e)
        {
           throw new ApiException( ApiHttpErrors::invalidFilesystemOperation() );
        }        
    }
}

?>
