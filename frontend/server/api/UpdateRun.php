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
require_once(SERVER_PATH . '/libs/Grader.php');
require_once(SERVER_PATH . '/libs/Cache.php');
require_once("Scoreboard.php");

class UpdateRun extends ApiHandler
{     
	private $grader;

	public function UpdateRun(Grader $grader = NULL)
	{
		if($grader === NULL)
		{
			$grader = new Grader();
		}
		$this->grader = $grader;
	}

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
		}
		catch(Exception $e)
		{
			// Operation failed in the data layer
			throw new ApiException( ApiHttpErrors::invalidDatabaseOperation(), $e);        
		}                        

		if(!Authorization::CanEditRun($this->_user_id, $this->myRun))
		{
			throw new ApiException(ApiHttpErrors::forbiddenSite());
		}                
	}

	protected function ValidateRequest() 
	{                            

	}

	protected function GenerateResponse() 
	{   
		Logger::log("New run being submitted !!");

		// Try to delete compile message, if exists.
		try
		{
			$grade_err = RUNS_PATH . '/../grade/' . $this->myRun->getRunId() . '.err';
			if (file_exists($grade_err))
			{
				unlink($grade_err);
			}
		}
		catch(Exception $e)
		{
			// Soft error :P
			Logger::error($e);
		}


		// Call Grader
		try
		{            
			$contest = ContestsDAO::getByPK($this->myRun->getContestId());
			$this->grader->Grade($this->myRun->getRunId());
		}
		catch(Exception $e)
		{
			Logger::error($e);
			throw new ApiException( ApiHttpErrors::invalidFilesystemOperation(), $e );
		}

		// Happy ending
		$this->addResponse("status", "ok");

		/// @todo Invalidate cache only when this run changes a user's score
		///       (by improving, adding penalties, etc)
		$this->InvalidateScoreboardCache($contest->getContestId());  
	}

	private function InvalidateScoreboardCache($contest_id)
	{
		$cache = new Cache();
		$cache->delete($contest_id, Scoreboard::MEMCACHE_PREFIX);
		$cache->delete($contest_id, Scoreboard::MEMCACHE_EVENTS_PREFIX);
	}
}

?>
