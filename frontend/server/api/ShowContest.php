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
    private $contest;
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
        $this->contest = ContestsDAO::getByAlias(RequestContext::get("contest_alias"));                                
        if ($this->contest->getPublic() === '0')            
        {      
            try
            {
                if (is_null(ContestsUsersDAO::getByPK($this->_user_id, $this->contest->getContestId())) && !Authorization::IsContestAdmin($this->_user_id, $this->contest))
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
        
        // If the contest has not started, user should not see it, unless it is admin
        if (!$this->contest->hasStarted($this->_user_id) && !Authorization::IsContestAdmin($this->_user_id, $this->contest))
        {
            throw new ApiException(ApiHttpErrors::preconditionFailed("Contest has not started yet.", array("start_time" => strtotime($this->contest->getStartTime()))) );
        }
    }      


    protected function GenerateResponse() 
    {
    	// Check cache first
    	$cache = new Cache(Cache::CONTEST_INFO, RequestContext::get("contest_alias"));    	
    	$result = $cache->get();
    	
    	if(is_null($result))
    	{
            // Create array of relevant columns
             $relevant_columns = array("title", "description", "start_time", "finish_time", "window_length", "alias", "scoreboard", "points_decay_factor", "partial_score", "submissions_gap", "feedback", "penalty", "time_start", "penalty_time_start", "penalty_calc_policy");            

             // Initialize response to be the contest information
             $result = $this->contest->asFilteredArray($relevant_columns);                    

             $result['start_time'] = strtotime($result['start_time']);
             $result['finish_time'] = strtotime($result['finish_time']);

             // Get problems of the contest
             $key_problemsInContest = new ContestProblems(
                 array("contest_id" => $this->contest->getContestId()));        
             try
             {
                 $problemsInContest = ContestProblemsDAO::search($key_problemsInContest, "order");
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
                         $this->_user_id, $this->contest->getContestId());
             }
             catch(Exception $e)
             {
                  // Operation failed in the data layer
                  throw new ApiException( ApiHttpErrors::invalidDatabaseOperation(), $e );        
             }
             
             // Add problems to response
             $result['problems'] = $problemsResponseArray;
             
             $cache->set($result, APC_USER_CACHE_CONTEST_INFO_TIMEOUT);

        }// closes if( $result == null )
        
        // Adding timer info separately as it depends on the current user and we don't
        // want this to get generally cached
                     
        // Add time left to response
        if ($this->contest->getWindowLength() === null)
        {
            $result['submission_deadline'] = strtotime($this->contest->getFinishTime());
        }
        else
        {
            $result['submission_deadline'] = min(strtotime($this->contest->getFinishTime()),
            strtotime($contest_user->getAccessTime()) + $this->contest->getWindowLength() * 60);
        }
    	
    	$this->addResponseArray($result);
    }    
}

?>
