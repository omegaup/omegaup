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

class ShowRunsInContest extends ApiHandler
{   
    protected function RegisterValidatorsToRequest()
    {
	$user_id = $this->_user_id;
        ValidatorFactory::stringNotEmptyValidator()->addValidator(new CustomValidator(
            function ($value) use ($user_id)
	    {
                // Check if the contest exists
		$contest = ContestsDAO::getByAlias($value);

		return ($contest->getDirectorId() == $user_id || $user_id == 3 || $user_id == 37);
            }, "Contest requested is invalid."))
        ->validate(RequestContext::get("contest_alias"), "contest_alias");
    }   
            
    protected function GenerateResponse() 
    {        
        $runs_mask = null;
        
        // If user is contest director or problem author, he will be able to see all runs
        try
        {
		$contest = ContestsDAO::getByAlias(RequestContext::get("contest_alias"));
        }
        catch(Exception $e)
        {
           // Operation failed in the data layer
           throw new ApiException( ApiHttpErrors::invalidDatabaseOperation(), $e );        
        }

        // Get all runs for problem given        
        $runs_mask = new Runs( array (                
            "contest_id" => $contest->getContestId()));
        
        // Filter relevant columns
        $relevant_columns = array( "run_id", "guid", "language", "status", "veredict", "runtime", "memory", "score", "contest_score", "time", "submit_delay", "Users.username", "Problems.alias" );
        
        // Get our runs
        try
        {            
            $runs = RunsDAO::search($runs_mask, "time", "DESC", $relevant_columns );
        }
        catch(Exception $e)
        { 
            // Operation failed in the data layer
           throw new ApiException( ApiHttpErrors::invalidDatabaseOperation(), $e );                
	}                

	$relevant_columns[11] = 'username';
	$relevant_columns[12] = 'alias';
        
	$result = array();

	foreach ($runs as $run) {
		$filtered = $run->asFilteredArray($relevant_columns);
		$filtered['time'] = strtotime($filtered['time']);
		array_push($result, $filtered);
	}

        // Add the run to the response
        $this->addResponse('runs', $result);               
        
        // All clear
        $this->addResponse("status", "ok");
    }    
}

?>
