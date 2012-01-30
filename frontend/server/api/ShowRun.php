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
        ValidatorFactory::stringNotEmptyValidator()->addValidator(new CustomValidator(
                function ($value)
                {
                    // Check if the contest exists
                    return RunsDAO::getByAlias($value);
                }, "Run is invalid."))
            ->validate(RequestContext::get("run_alias"), "run_alias");
            
        try
        {                        
            // If user is not judge, must be the run's owner.
            $this->myRun = RunsDAO::getByAlias(RequestContext::get("run_alias"));
            
            $contest = ContestsDAO::getByPK($this->myRun->getContestId());
            $problem = ProblemsDAO::getByPK($this->myRun->getProblemId());
        }
        catch(Exception $e)
        {
            // Operation failed in the data layer
           throw new ApiException( ApiHttpErrors::invalidDatabaseOperation(), $e);        
        }                        
        
        if(!($this->myRun->getUserId() == $this->_user_id || 
                $contest->getDirectorId() == $this->_user_id ||
                $problem->getAuthorId() == $this->_user_id ))
        {
           throw new ApiException(ApiHttpErrors::forbiddenSite());
        }                
    }   

    protected function GenerateResponse() 
    {
        
        // Fill response
	$relevant_columns = array( "guid", "language", "status", "veredict", "runtime", "memory", "score", "contest_score", "time", "submit_delay" );
	$filtered = $this->myRun->asFilteredArray($relevant_columns);
	$filtered['time'] = strtotime($filtered['time']);
        $this->addResponseArray($filtered);
        
        try
        {
            // Get source code
            $filepath = RUNS_PATH . DIRECTORY_SEPARATOR . $this->myRun->getGuid();
            RequestContext::set("source", FileHandler::ReadFile($filepath));                                        
        }
        catch (Exception $e)
        {
           throw new ApiException( ApiHttpErrors::invalidFilesystemOperation(), $e );
        }        
    }
}

?>
