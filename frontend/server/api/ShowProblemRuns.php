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
        ->validate(RequestContext::get("alias"), "alias");
         
    }   
        
    
    protected function GenerateResponse() 
    {        
        $runs_mask = null;
        
        // If user is contest director, he will be able to see all runs
        try
        {
            $problem = ProblemsDAO::getByAlias(RequestContext::get("alias"));
            
            $contest_problems = ContestProblemsDAO::search(new ContestProblems(array(
                "problem_id" => $problem->getProblemId()
            )));
            $contest = ContestsDAO::getByPK($contest_problems[0]->getContestId());
        }
        catch(Exception $e)
        {
           // Operation failed in the data layer
           throw new ApiException( ApiHttpErrors::invalidDatabaseOperation() );        
        }

        if ($contest->getDirectorId() == $this->_user_id )
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
        
        $relevant_columns = array( "run_id", "language", "status", "veredict", "runtime", "memory", "score", "contest_score", "time", "submit_delay" );
        
        // Get our problems given the user_id and problem_id
        try
        {            
            $runs = RunsDAO::search($runs_mask, "time", "DESC", $relevant_columns );
        }
        catch(Exception $e)
        {
            // Operation failed in the data layer
           throw new ApiException( ApiHttpErrors::invalidDatabaseOperation() );        
        
        }
        
        // Add the clarificatoin the response
        foreach($runs as $run)
        { 
            $this->addResponse($run->getRunId(), $run->asFilteredArray($relevant_columns));               
        }                     
    }    
}

?>
