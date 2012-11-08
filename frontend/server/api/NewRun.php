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
require_once(SERVER_PATH . '/libs/Authorization.php');
require_once("Scoreboard.php");

class NewRun extends ApiHandler
{     
	private $grader;
	private $problem;
	private $contest;
	private $practice;

	public function NewRun(Grader $grader = NULL)
	{
		if($grader === NULL)
		{
			$grader = new Grader();
		}
		$this->grader = $grader;
		$this->practice = false;
	}

	protected function RegisterValidatorsToRequest()
	{    
		ValidatorFactory::stringNotEmptyValidator()->addValidator(new CustomValidator(
			function ($value)
			{
				// Check if the contest exists
				return ProblemsDAO::getByAlias($value);
			}, "Problem requested is invalid."))
				->validate(RequestContext::get("problem_alias"), "problem_alias");

		ValidatorFactory::enumValidator(array ('kp','kj','c','cpp','java','py','rb','pl','cs','p'))->validate(
			RequestContext::get("language"), "language");

		ValidatorFactory::stringNotEmptyValidator()->validate(RequestContext::get("source"), "source");

		try
		{
			$this->problem = ProblemsDAO::getByAlias(RequestContext::get("problem_alias"));

			if (RequestContext::get("contest_alias") == "" && (Authorization::IsSystemAdmin($this->_user_id) || time() > ProblemsDAO::getPracticeDeadline($this->problem->getProblemId()))) {
				if (!RunsDAO::IsRunInsideSubmissionGap(
					null, 
					$this->problem->getProblemId(), 
					$this->_user_id)
					&& !Authorization::IsSystemAdmin($this->_user_id))
				{                
					throw new ApiException(ApiHttpErrors::notAllowedToSubmit("Unable to submit run: You have to wait 120 seconds between consecutive submissions."));
				}

				$this->practice = true;
				return;
			}

			ValidatorFactory::stringNotEmptyValidator()->addValidator(new CustomValidator(
				function ($value)
				{
					// Check if the contest exists
					return ContestsDAO::getByAlias($value);
				}, "Contest is invalid."))
					->validate(RequestContext::get("contest_alias"), "contest_alias");

			$this->contest = ContestsDAO::getByAlias(RequestContext::get("contest_alias"));

			// Validate that the combination contest_id problem_id is valid            
			if (!ContestProblemsDAO::getByPK(
				$this->contest->getContestId(),
				$this->problem->getProblemId()                
			))
			{
				throw new ApiException(ApiHttpErrors::invalidParameter("problem_alias and contest_alias combination is invalid."));
			}

			// Before submit something, contestant had to open the problem/contest
			if(!ContestsUsersDAO::getByPK($this->_user_id, 
				$this->contest->getContestId()))
			{
				throw new ApiException(ApiHttpErrors::forbiddenSite("Unable to submit run: You must open the problem before trying to submit a solution."));
			}

			// Validate that the run is timely inside contest
			if( !$this->contest->isInsideContest($this->_user_id) 
				&& !Authorization::IsContestAdmin($this->_user_id, $this->contest))
			{                
				throw new ApiException(ApiHttpErrors::forbiddenSite("Unable to submit run: Contest time has expired or not started yet."));
			}

			// Validate if contest is private then the user should be registered
			if ( $this->contest->getPublic() == 0 
				&& is_null(ContestsUsersDAO::getByPK(
					$this->_user_id, 
					$this->contest->getContestId()))
					&& !Authorization::IsContestAdmin($this->_user_id, $this->contest))
			{
				throw new ApiException(ApiHttpErrors::forbiddenSite("Unable to submit run: You are not registered to this contest."));
			}

			// Validate if the user is allowed to submit given the submissions_gap 
			if (!RunsDAO::IsRunInsideSubmissionGap(
				$this->contest->getContestId(), 
				$this->problem->getProblemId(), 
				$this->_user_id)
				&& !Authorization::IsContestAdmin($this->_user_id, $this->contest))
			{                
				throw new ApiException(ApiHttpErrors::notAllowedToSubmit("Unable to submit run: You have to wait " . $this->contest->getSubmissionsGap() . " seconds between consecutive submissions."));
			}

		}
		catch(ApiException $apiException)
		{
			// Propagate ApiException
			throw $apiException;
		}
		catch(Exception $e)
		{            
			// Operation failed in the data layer
			throw new ApiException( ApiHttpErrors::invalidDatabaseOperation(), $e );    
		}                 
	}


	protected function GenerateResponse() 
	{   
		Logger::log("New run being submitted !!");

		if ($this->practice) {
			$submit_delay = 0;
			$contest_id = null;
			$test = 0;
		} else {
			//check the kind of penalty_time_start for this contest
			$penalty_time_start = $this->contest->getPenaltyTimeStart();

			switch($penalty_time_start){
			case "contest":
				// submit_delay is calculated from the start
				// of the contest
				$start = $this->contest->getStartTime();
				break;

			case "problem":
				// submit delay is calculated from the 
				// time the user opened the problem
				$opened = ContestProblemOpenedDAO::getByPK(
					$this->contest->getContestId(), 
					$this->problem->getProblemId(), 
					$this->_user_id
				);

				if(is_null($opened)){
					//holy moly, he is submitting a run 
					//and he hasnt even opened the problem
					//what should be done here?
					Logger::error("User is submitting a run and he has not even opened the problem");
					throw new Exception("User is submitting a run and he has not even opened the problem");
				}

				$start = $opened->getOpenTime();
				break;

			case "none":
				//we dont care
				$start = null;
				break;

			default:
				Logger::error("penalty_time_start for this contests is not a valid option, asuming `none`.");
				$start = null;
			}

			if(!is_null($start)){
				//ok, what time is it now?
				$c_time = time();
				$start = strtotime( $start );

				//asuming submit_delay is in minutes
				$submit_delay = (int)(( $c_time - $start ) / 60);

			}else{
				$submit_delay = 0;
			}

			$contest_id = $this->contest->getContestId();
			$test = Authorization::IsContestAdmin($this->_user_id, $this->contest) ? 1 : 0;
		}

		// Populate new run object
		$run = new Runs(array(
			"user_id"		=> $this->_user_id,
			"problem_id" 	=> $this->problem->getProblemId(),
			"contest_id" 	=> $contest_id,
			"language" 		=> RequestContext::get("language"),
			"source" 		=> RequestContext::get("source"),
			"status" 		=> "new",
			"runtime" 		=> 0,
			"memory" 		=> 0,
			"score" 		=> 0,
			"contest_score" => 0,
			"ip" 			=> $_SERVER['REMOTE_ADDR'],
			"submit_delay" 	=> $submit_delay, /* based on penalty_time_start */ 
			"guid" 			=> md5(uniqid(rand(), true)),
			"veredict" 		=> "JE",
			"test"		=> $test
		));

		try
		{
			// Push run into DB
			RunsDAO::save($run);
		}
		catch(Exception $e)
		{   
			// Operation failed in the data layer
			throw new ApiException( ApiHttpErrors::invalidDatabaseOperation(), $e );    
		}

		try
		{
			// Create file for the run        
			$filepath = RUNS_PATH . DIRECTORY_SEPARATOR . $run->getGuid();
			FileHandler::CreateFile($filepath, RequestContext::get("source"));            
		}
		catch (Exception $e)
		{
			throw new ApiException( ApiHttpErrors::invalidFilesystemOperation(), $e );                            
		}

		// Call Grader
		try
		{            
			$this->grader->Grade($run->getRunId());
		}
		catch(Exception $e)
		{
			Logger::error($e);
			throw new ApiException( ApiHttpErrors::invalidFilesystemOperation(), $e );
		}

		if ($this->practice) {
			$this->addResponse('submission_deadline', 0);
		} else {
			// Add remaining time to the response
			try
			{
				$contest_user = ContestsUsersDAO::getByPK($this->_user_id, $this->contest->getContestId());

				if ($this->contest->getWindowLength() === null)
				{
					$this->addResponse('submission_deadline', strtotime($this->contest->getFinishTime()));
				}
				else
				{
					$this->addResponse('submission_deadline', min(strtotime($this->contest->getFinishTime()),
						strtotime($contest_user->getAccessTime()) + $this->contest->getWindowLength() * 60));
				}
			}
			catch(Exception $e)
			{            
				// Operation failed in the data layer
				throw new ApiException( ApiHttpErrors::invalidDatabaseOperation(), $e );    
			}  
		}

		// Happy ending
		$this->addResponse("guid", $run->getGuid());
		$this->addResponse("status", "ok");

		if (!$this->practice) {
			/// @todo Invalidate cache only when this run changes a user's score
			///       (by improving, adding penalties, etc)
			$this->InvalidateScoreboardCache($this->contest->getContestId());  
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
