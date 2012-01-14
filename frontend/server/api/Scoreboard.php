<?php

/*
 * Scoreboard 
 * 
 */

class Scoreboard 
{
    // Column to return total score per user
    const total_column = "total";
    const MEMCACHE_KEY = "scoreboard";
    
    // Contest's data
    private $data;
    private $contest_id;
    private $countProblemsInContest;
    
        
    public function __construct($contest_id)
    {
        $this->data = array();
        $this->contest_id = $contest_id;
        
    }
    
    public function getCountProblemsInContest()
    {
        return $this->countProblemsInContest;
    }



    public function generate()
    {
        $memcache = new Memcache;
		if( !$memcache->connect(OMEGAUP_MEMCACHE_HOST, OMEGAUP_MEMCACHE_PORT) )
		{
			$memcache = null;
		}

		$result = null;
		if( $memcache != null )
		{
			$result = $memcache->get(self::MEMCACHE_KEY);
		}
		
		if( $result == null )
		{
	        try
	        {
	            // Get all distinct contestants participating in the contest given contest_id
	            $contest_users = RunsDAO::GetAllRelevantUsers($this->contest_id);                             
	                        
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
	            foreach ($contest_problems as $problems)
	            {
	                $user_results[$problems->getAlias()] = $this->getScore($problems->getProblemId(), $contestant->getUserId());                                       
	            }
	            
	            // Calculate total score for current user            
	            $user_results[self::total_column] = $this->getTotalScore($user_results);            
	            
	            // Add contestant results to scoreboard data
	            $result[$contestant->getUsername()] = $user_results;
	        }
	        
	        // Sort users by their total column
	        uasort($result, array($this, 'compareUserScores'));
	         
	        // Cache scoreboard if a memcache connection is available
	        if( $memcache )
	        {
	        	$memcache->set(self::MEMCACHE_KEY, $result, 0, OMEGAUP_MEMCACHE_SCOREBOARD_TIMEOUT);
	        }
		}

	    $this->data = $result;
		return $this->data;                
    }
    
    
    protected function getScore($problem_id, $user_id)
    {
        try
        {
            $bestRun = RunsDAO::GetBestRun($this->contest_id, $problem_id, $user_id);        
        }
        catch(Exception $e)
        {
            throw new ApiException(ApiHttpErrors::invalidDatabaseOperation(), $e);
        }
        
        // @todo add support for penalties
        return array(
            "points" => (int)$bestRun->getContestScore(),
            "penalty" => 0
        );        
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
            return 0;
        
        return ($a[self::total_column]["points"] < $b[self::total_column]["points"]) ? 1 : -1;
    }    
}

?>
