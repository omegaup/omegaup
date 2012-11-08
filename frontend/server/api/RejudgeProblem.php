    <?php

/**
 * 
 * Please read full (and updated) documentation at: 
 * https://github.com/omegaup/omegaup/wiki/Arena 
 *
 *
 * 
 * POST /problems/:id:/rejudge
 * Si el usuario tiene permisos de juez o admin, rejuecea el problema :id:
 *
 * */

require_once("ApiHandler.php");
require_once(SERVER_PATH . '/libs/Grader.php');
require_once(SERVER_PATH . '/libs/Cache.php');
require_once("Scoreboard.php");

class RejudgeProblem extends ApiHandler
{     
	private $myProblem;
    
    protected function RegisterValidatorsToRequest()
    {    
        ValidatorFactory::stringNotEmptyValidator()->addValidator(new CustomValidator(
                function ($value)
                {
                    // Check if the contest exists
                    return ProblemsDAO::getByAlias($value);
                }, "Problem is invalid."))
            ->validate(RequestContext::get("problem_alias"), "problem_alias");
            
        try
        {                        
            $this->myProblem = ProblemsDAO::getByAlias(RequestContext::get("problem_alias"));            
        }
        catch(Exception $e)
        {
            // Operation failed in the data layer
           throw new ApiException( ApiHttpErrors::invalidDatabaseOperation(), $e);        
        }                        

        if(!Authorization::CanEditProblem($this->_user_id, $this->myProblem))
        {
           throw new ApiException(ApiHttpErrors::forbiddenSite());
        }
    }
    
    protected function ValidateRequest() 
    {                            
    }
    
    protected function GenerateResponse() 
    {   
	Logger::log("New run being submitted!!");
	$grader = new Grader();
        
        // Call Grader
        try
	{
            $runs = RunsDAO::search(new Runs(array(
                "problem_id" => $this->myProblem->getProblemId()
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
            throw new ApiException( ApiHttpErrors::invalidFilesystemOperation(), $e );
        }
        
        // Happy ending
	$this->addResponse("status", "ok");

	$cp = ContestProblemsDAO::search(new ContestProblems(array(
		"problem_id" => $this->myProblem->getProblemId()
	)));

	foreach ($cp as $contest) {
	        /// @todo Invalidate cache only when this run changes a user's score
        	///       (by improving, adding penalties, etc)
	        $this->InvalidateScoreboardCache($contest->getContestId());  
	}
    }
    
    private function InvalidateScoreboardCache($contest_id)
    {
    	// Invalidar cache del contestant
        $contestantScoreboardCache = new Cache(Cache::CONTESTANT_SCOREBOARD_PREFIX, $contest_id);
        $contestantScoreboardCache->delete();

        // Invalidar cache del admin
        $adminScoreboardCache = new Cache(Cache::ADMIN_SCOREBOARD_PREFIX, $contest_id);
        $adminScoreboardCache->delete(); 
    }
}
?>
