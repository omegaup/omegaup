<?php

/*
 * Scoreboard 
 * 
 */

require_once(SERVER_PATH . '/libs/Cache.php');
require_once('ShowRunDetails.php');

class Scoreboard 
{
    // Column to return total score per user
    const total_column = "total";
    const MEMCACHE_EVENTS_PREFIX = "scoreboard_events";
    
    // Contest's data
    private $data;
    private $contest_id;
    private $countProblemsInContest;
    private $showAllRuns;
        
    public function __construct($contest_id, $showAllRuns = false)
    {
        $this->data = array();
        $this->contest_id = $contest_id;
        $this->showAllRuns = $showAllRuns;
    }

    public function getScoreboardTimeLimitUnixTimestamp(Contests $contest)
    {
        $start = strtotime($contest->getStartTime());
        $finish = strtotime($contest->getFinishTime());
        
        if ($this->showAllRuns || ($contest->hasFinished() && $contest->getShowScoreboardAfter()))
        {
            // Show full scoreboard to admin users
            // or if the contest finished and the creator wants to show it at the end
            $percentage = 1.0;
	}
        else
        {
            $percentage = (double)$contest->getScoreboard() / 100.0;
	}

        $limit = $start + (int)(($finish - $start) * $percentage);
                                 
        return $limit;
    }
    
    public function getCountProblemsInContest()
    {
        return $this->countProblemsInContest;
    }
    
    public function generate($withRunDetails = false, $sortByName = false, $filterUsersBy = NULL)
    {
    	$result = null;        
        
        $contestantScoreboardCache = new Cache(Cache::CONTESTANT_SCOREBOARD_PREFIX, $this->contest_id);
        $adminScoreboardCache = new Cache(Cache::ADMIN_SCOREBOARD_PREFIX, $this->contest_id);
        
        $can_use_contestant_cache = !$this->showAllRuns 
                                && !$sortByName
                                && is_null($filterUsersBy);
        
        $can_use_admin_cache = $this->showAllRuns 
                                && !$sortByName
                                && is_null($filterUsersBy);
        
        // If cache is turned on and we're not looking for admin-only runs
        if ($can_use_contestant_cache)
        {
            $result = $contestantScoreboardCache->get();                       
        }
        else if ($can_use_admin_cache)
        {
            $result = $adminScoreboardCache->get();            
        }
        
        if (is_null($result))
        {
            try
            {
                $contest = ContestsDAO::getByPK($this->contest_id);	

                // Get whether we can cache this scoreboard.
                $pending_runs = RunsDAO::PendingRuns($this->contest_id, $this->showAllRuns);
                $cacheable_for_contestant = !$this->showAllRuns && !$pending_runs;
                $cacheable_for_admin = $this->showAllRuns && !$pending_runs;

                // Get all distinct contestants participating in the contest given contest_id
                $contest_users = RunsDAO::GetAllRelevantUsers($this->contest_id, $this->showAllRuns, $filterUsersBy);

                // Get all problems given contest_id
                $contest_problems = ContestProblemsDAO::GetRelevantProblems($this->contest_id);
            }
            catch(Exception $e)
            {
                throw new ApiException(ApiHttpErrors::invalidDatabaseOperation(), $e);
            }

            $result = array();

            // Save the number of problems internally
            $this->countProblemsInContest = count($contest_problems);

            // Calculate score for each contestant x problem
            foreach ($contest_users as $contestant)
            {
                $user_results = array();
                $user_problems = array();

                foreach ($contest_problems as $problems)
                {
                    $user_problems[$problems->getAlias()] = $this->getScore($problems->getProblemId(), $contestant->getUserId(), $this->getScoreboardTimeLimitUnixTimestamp($contest), $withRunDetails, $contest->getPenalty());
                }

                // Add the problems' information
                $user_results['problems'] = $user_problems;

                // Calculate total score for current user            
                $user_results[self::total_column] = $this->getTotalScore($user_problems);

                // And more information on the user
                $user_results['username'] = $contestant->getUsername();
                $user_results['name'] = $contestant->getName() ? $contestant->getName() : $contestant->getUsername();

                // Add contestant results to scoreboard data
                array_push($result, $user_results);
            }

            if ($sortByName == false)
            {
                // Sort users by their total column
                usort($result, array($this, 'compareUserScores'));
            }
            else 
            {
                // Sort users by their name
                usort($result, array($this, 'compareUserNames'));
            }

            // Cache scoreboard if there are no pending runs.
            if ($cacheable_for_contestant && $can_use_contestant_cache)
            {
                $contestantScoreboardCache->set($result, APC_USER_CACHE_SCOREBOARD_TIMEOUT);                
            }
            else if ($cacheable_for_admin && $can_use_admin_cache)
            {
                $adminScoreboardCache->set($result, APC_USER_CACHE_ADMIN_SCOREBOARD_TIMEOUT);                
            }
	}

    	$this->data = $result;
	return $this->data;                
    }

    public function events()
    {
        $cache = new Cache(Cache::CONTESTANT_SCOREBOARD_EVENTS_PREFIX, $this->contest_id);
        $result = $cache->get();

        if( $this->showAllRuns || is_null($result))
        {
            try
            {
                $contest = ContestsDAO::getByPK($this->contest_id);
                    
                // Gets whether we can cache this scoreboard.
                $cacheable = !$this->showAllRuns && !RunsDAO::PendingRuns($this->contest_id, $this->showAllRuns);

                // Get all distinct contestants participating in the contest given contest_id
		$raw_contest_users = RunsDAO::GetAllRelevantUsers($this->contest_id, $this->showAllRuns); 

                // Get all problems given contest_id
                $raw_contest_problems = ContestProblemsDAO::GetRelevantProblems($this->contest_id);

                $run = new Runs();
                $run->setContestId($this->contest_id);
		$run->setStatus('ready');
		if (!$this->showAllRuns) {
			$run->setTest(0);
		}

                $contest_runs = RunsDAO::search($run, 'submit_delay');
            }
            catch(Exception $e)
            {
                throw new ApiException(ApiHttpErrors::invalidDatabaseOperation(), $e);
            }

            $contest_users = array();
            $contest_problems = array();

            foreach ($raw_contest_users as $user)
            {
                    $contest_users[$user->getUserId()] = $user;
            }


            foreach ($raw_contest_problems as $problem)
            {
                    $contest_problems[$problem->getProblemId()] = $problem;
            }

            $result = array();

            // Save the number of problems internally
            $this->countProblemsInContest = count($contest_problems);

            $user_problems_score = array();

            // Calculate score for each contestant x problem
            foreach ($contest_runs as $run)
            {
                if (!isset($user_problems_score[$run->getUserId()]))
                {
                    $user_problems_score[$run->getUserId()] = array();
                }

                if (!isset($user_problems_score[$run->getUserId()][$run->getProblemId()]))
                {
                    $user_problems_score[$run->getUserId()][$run->getProblemId()] = array('points'=>0,'penalty'=>0);
                }

                if ($user_problems_score[$run->getUserId()][$run->getProblemId()]['points'] >= $run->getContestScore())
                {
                        continue;
                }
                
                if (strtotime($run->getTime()) >= $this->getScoreboardTimeLimitUnixTimestamp($contest))
                {
                        continue;
                }

                $user_problems_score[$run->getUserId()][$run->getProblemId()]['points'] = round((float)$run->getContestScore(), 2);
		$user_problems_score[$run->getUserId()][$run->getProblemId()]['penalty'] = 0;

                $data = array();
		$user = $contest_users[$run->getUserId()];

                $data['name'] = $user->getName() ? $user->getName() : $user->getUsername();
                $data['username'] = $user->getUsername();
                $data['delta'] = (int)$run->getSubmitDelay();

                $data['problem'] = array(
                        'alias' => $contest_problems[$run->getProblemId()]->getAlias(),
                        'points' => round((float)$run->getContestScore(), 2),
                        'penalty' => 0
                );

                $data['total'] = array(
                        'points' => 0,
                        'penalty' => 0
                );

                foreach ($user_problems_score[$run->getUserId()] as $problem)
                {
                        $data['total']['points'] += $problem['points'];
                        $data['total']['penalty'] += $problem['penalty'];
                }

                // Add contestant results to scoreboard data
                array_push($result, $data);
            }

            // Cache scoreboard if there are no pending runs
            if ($cacheable)
            {
                $cache->set($result, APC_USER_CACHE_SCOREBOARD_TIMEOUT);
            }
	}

	$this->data = $result;
	return $this->data;                
    }
    
   protected function getScore($problem_id, $user_id, $limit_timestamp, $withRunDetails, $penalty)
   {
       $wrong_runs_count = 0;
        try
        {
		$bestRun = RunsDAO::GetBestRun($this->contest_id, $problem_id, $user_id, $limit_timestamp, $this->showAllRuns);
		$extra_penalty = 0;                

		if ($penalty > 0 && !is_null($bestRun) && (int)$bestRun->getContestScore() > 0) {
                        $wrong_runs_count = RunsDAO::GetWrongRuns($this->contest_id, $problem_id, $user_id, $bestRun->getRunId(), $this->showAllRuns);
			$extra_penalty = $penalty * $wrong_runs_count;
		}
	}
        catch(Exception $e)
        {
            throw new ApiException(ApiHttpErrors::invalidDatabaseOperation(), $e);
        }
        
        // Penalty should not be added if the best run was 0 pts       
	$final_penalty = $extra_penalty + ( ((int)$bestRun->getContestScore() > 0) ? (int)round($bestRun->getSubmitDelay()) : 0) ;
 
        // If we want all the details with the run (diff of cases, etc..)
        if ($withRunDetails && !is_null($bestRun))
        {
	    $runDetails = array();

            if ($bestRun->getGuid() != "")
            {
                $runDetailGenerator = new ShowRunDetails();
                RequestContext::set("run_alias", $bestRun->getGuid());

                $runDetails = $runDetailGenerator->ExecuteApi();                        
                
		// If STATUS="OK" and out_diff is not null, then status is WA
                // OK just means that runner didn't crash. Grader grades after that.
                foreach($runDetails["cases"] as &$case)
                {
                    if ($case["meta"]["status"] == "OK" && !is_null($case["out_diff"]))
                    {
                        $case["meta"]["status"] = "WA";
                    }
                }
                
                unset($runDetails["source"]);
                
            }
            return array(
                "points" => (int)round($bestRun->getContestScore()),
                "penalty" => $final_penalty,
                "run_details" => $runDetails
            );
        }        
        else
        {
            return array(
                "points" => (int)round($bestRun->getContestScore()),
                "penalty" => $final_penalty,
                "wrong_runs_count" => $wrong_runs_count,
            );
        }
    }
        
    protected function getTotalScore($scores)
    {        
        
        $sumPoints = 0;
        $sumPenalty = 0;
        // Get sum of all scores
        foreach($scores as $score)
        {
            $sumPoints += $score["points"];
            $sumPenalty += $score["penalty"];
        }
                        
        return array(
          "points" => $sumPoints,
          "penalty" => $sumPenalty
        );
    }
    
    private function compareUserScores($a, $b)
    {        
	if ($a[self::total_column]["points"] == $b[self::total_column]["points"])
	{
		if ($a[self::total_column]["penalty"] == $b[self::total_column]["penalty"])
			return 0;

		return ($a[self::total_column]["penalty"] > $b[self::total_column]["penalty"]) ? 1 : -1;
	}
        
        return ($a[self::total_column]["points"] < $b[self::total_column]["points"]) ? 1 : -1;
    }    
    
    
    private function compareUserNames($a, $b)
    {        	        
        return strcmp($a['username'], $b['username']);
    }   
}

?>
