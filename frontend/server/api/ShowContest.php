<?php

/**
 * 
 * Please read full (and updated) documentation at: 
 * https://github.com/omegaup/omegaup/wiki/Arena 
 *
 * 
 * POST /contests/:id
 * Si el usuario puede verlos, muestra los detalles del concurso :id.
 *
 * */

require_once("ApiHandler.php");
require_once(SERVER_PATH . '/libs/Cache.php');

class ShowContest extends ApiHandler
{    
    const MEMCACHE_PREFIX = 'contest_info';
    
    protected function RegisterValidatorsToRequest()
    {
        ValidatorFactory::stringNotEmptyValidator()->addValidator(new CustomValidator(
                function ($value)
                {
                    // Check if the contest exists
                    return ContestsDAO::getByAlias($value);
                }, "Contest is invalid."))
            ->validate(RequestContext::get("contest_alias"), "contest_alias");
        
        // If the contest is private, verify that our user is invited                
        $contest = ContestsDAO::getByAlias(RequestContext::get("contest_alias"));                                
        if ($contest->getPublic() === '0')            
        {      
            try
            {
                if (is_null(ContestsUsersDAO::getByPK($this->_user_id, $contest->getContestId())))
                {
                    throw new ApiException(ApiHttpErrors::forbiddenSite());
                }
            }
            catch(ApiException $e)
            {
                // Propagate exception
                throw $e;
            }
            catch(Exception $e)
            {
                 // Operation failed in the data layer
                 throw new ApiException( ApiHttpErrors::invalidDatabaseOperation(), $e );                
            }
        }                                                
    }      


    protected function GenerateResponse() 
    {
    	// Check cache first
    	$cache = new Cache(self::MEMCACHE_PREFIX);
    	$cache_key = RequestContext::get("contest_alias");
    	$result = $cache->get($cache_key);
    	
    	if( $result == null )
    	{
	       // Create array of relevant columns
	        $relevant_columns = array("title", "description", "start_time", "finish_time", "window_length", "alias", "scoreboard", "points_decay_factor", "partial_score", "submissions_gap", "feedback", "penalty", "time_start", "penalty_time_start", "penalty_calc_policy");
	        
	        // Get our contest given the alias
	        try
	        {            
	            $contest = ContestsDAO::getByAlias(RequestContext::get("contest_alias"));
	        }
	        catch(Exception $e)
	        {
	            // Operation failed in the data layer
	           throw new ApiException( ApiHttpErrors::invalidDatabaseOperation(), $e );                
	        }
	        
	        // Initialize response to be the contest information
		$result = $contest->asFilteredArray($relevant_columns);                    

		$result['start_time'] = strtotime($result['start_time']);
		$result['finish_time'] = strtotime($result['finish_time']);

	        // Get problems of the contest
	        $key_problemsInContest = new ContestProblems(
	            array("contest_id" => $contest->getContestId()));        
	        try
	        {
	            $problemsInContest = ContestProblemsDAO::search($key_problemsInContest);
	        }
	        catch(Exception $e)
	        {
	            // Operation failed in the data layer
	           throw new ApiException( ApiHttpErrors::invalidDatabaseOperation(), $e);        
	        }        
	        
	        // Add info of each problem to the contest
	        $problemsResponseArray = array();
	
	        // Set of columns that we want to show through this API. Doesn't include the SOURCE
	        $relevant_columns = array("title", "alias", "validator", "time_limit", "memory_limit", "visits", "submissions", "accepted", "dificulty", "order");
	        
	        foreach($problemsInContest as $problemkey)
	        {
	            try
	            {
	                // Get the data of the problem
	                $temp_problem = ProblemsDAO::getByPK($problemkey->getProblemId());
	            }
	            catch(Exception $e)
	            {
	                // Operation failed in the data layer
	               throw new ApiException( ApiHttpErrors::invalidDatabaseOperation(), $e );        
	            }
	            
	            // Add the 'points' value that is stored in the ContestProblem relationship
	            $temp_array = $temp_problem->asFilteredArray($relevant_columns);
	            $temp_array["points"] = $problemkey->getPoints();
	                    
	            // Save our array into the response
	            array_push($problemsResponseArray, $temp_array);
	            
	        }
	        
	        // Save the time of the first access
	        try
	        {
	            $contest_user = ContestsUsersDAO::CheckAndSaveFirstTimeAccess(
	                    $this->_user_id, $contest->getContestId());
	        }
	        catch(Exception $e)
	        {
	             // Operation failed in the data layer
	             throw new ApiException( ApiHttpErrors::invalidDatabaseOperation(), $e );        
	        }
	        
	        // Add problems to response
	        $result['problems'] = $problemsResponseArray;
                
                // Add time left to response
                if ($contest->getWindowLength() === null)
                {
                    $result['submission_deadline'] = strtotime($contest->getFinishTime());
                }
                else
                {
                    $result['submission_deadline'] = min(strtotime($contest->getFinishTime()),
                    strtotime($contest_user->getAccessTime()) + $contest->getWindowLength() * 60);
                }
	                
	        // @TODO Add mini ranking here
	        
	    	$cache->set($cache_key, $result, OMEGAUP_MEMCACHE_CONTEST_TIMEOUT);
	    	
    	}	// closes if( $result == null )
    	
    	$this->addResponseArray($result);
    }    
}

?>
