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

class ShowProblemRuns extends ApiHandler
{   
    protected function RegisterValidatorsToRequest()
    {
        ValidatorFactory::stringNotEmptyValidator()->addValidator(new CustomValidator(
            function ($value)
            {
                // Check if the contest exists
                return ProblemsDAO::getByAlias($value);
            }, "Problem requested is invalid."))
        ->validate(RequestContext::get("problem_alias"), "problem_alias");
         
    }   
            
    protected function GenerateResponse() 
    {        
        $runs_mask = null;
        
        // If user is contest director or problem author, he will be able to see all runs
        try
        {
            $problem = ProblemsDAO::getByAlias(RequestContext::get("problem_alias"));
            
            $contest_problems = ContestProblemsDAO::search(new ContestProblems(array(
                "problem_id" => $problem->getProblemId()
            )));
            $contest = ContestsDAO::getByPK($contest_problems[0]->getContestId());
        }
        catch(Exception $e)
        {
           // Operation failed in the data layer
           throw new ApiException( ApiHttpErrors::invalidDatabaseOperation(), $e );        
        }

        if ($contest->getDirectorId() == $this->_user_id || $problem->getAuthorId() == $this->_user_id)
        {
            // Get all runs for problem given        
            $runs_mask = new Runs( array (                
                "problem_id" => $problem->getProblemId()));
        }        
        else
        {        
            // Get runs only for current user 
            $runs_mask = new Runs( array (
                "user_id"    => $this->_user_id,
                "problem_id" => $problem->getProblemId()));
        }
        
        // Filter relevant columns
        $relevant_columns = array( "run_id", "guid", "language", "status", "veredict", "runtime", "memory", "score", "contest_score", "time", "submit_delay" );
        
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
        
        // DAOs need run_id but we dont want to expose it, deleting it
        array_shift($relevant_columns);
        
        // Add the run to the response
        $index = 0;
        foreach($runs as $run)
        { 
            $this->addResponse($index, $run->asFilteredArray($relevant_columns));               
            $index++;
        }     
        
        // All clear
        $this->addResponse("status", "ok");
    }    
}

?>
