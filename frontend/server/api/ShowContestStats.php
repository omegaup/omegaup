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
require_once(SERVER_PATH ."/libs/Authorization.php");

class ShowContestStats extends ApiHandler
{
    private $contest;
    
    protected function RegisterValidatorsToRequest()
    {
	$user_id = $this->_user_id;

	ValidatorFactory::stringNotEmptyValidator()->validate(RequestContext::get("contest_alias"), "contest_alias");

	try 
        {
            $this->contest = ContestsDAO::getByAlias(RequestContext::get("contest_alias"));
	} 
        catch (Exception $e) 
        {
            // Operation failed in the data layer
            throw new ApiException(ApiHttpErrors::invalidDatabaseOperation(), $e);
	}

        // This API is Contest Admin only
        if (is_null($this->contest) || !Authorization::IsContestAdmin($user_id, $this->contest))
        {
            throw new ApiException(ApiHttpErrors::forbiddenSite());
        }        	                     
    }   
            
    protected function GenerateResponse() 
    {        
        // Get COUNT of runs != ready
        try
        {
            // Array of GUIDs of pending runs
            $pendingRunsGuids = RunsDAO::GetPendingRunsOfContest($this->contest->getContestId());
            
            // Count of pending runs (int)
            $totalRunsCount = RunsDAO::CountTotalRunsOfContest($this->contest->getContestId());
            
            // Wait time
            $waitTimeArray = RunsDAO::GetLargestWaitTimeOfContest($this->contest->getContestId());            
        }
        catch (Exception $e)
        {
            // Operation failed in the data layer
            throw new ApiException(ApiHttpErrors::invalidDatabaseOperation(), $e);
        }
        
        // Para darle gusto al Alanboy, regresando array
        return array(
            "total_runs" => $totalRunsCount,
            "pending_runs" => $pendingRunsGuids,            
            "max_wait_time" => is_null($waitTimeArray) ? 0 : $waitTimeArray[1],
            "max_wait_time_guid" => is_null($waitTimeArray) ? 0 : $waitTimeArray[0]->getGuid(),
        );                
    }    
}

?>
