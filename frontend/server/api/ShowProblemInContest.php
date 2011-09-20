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

class ShowProblemInContest extends ApiHandler
{
    
    protected function ProcessRequest()
    {
        $this->request = array(
            "contest_id" => new ApiExposedProperty("contest_id", true, GET, array(
                new NumericValidator()
            )),
            "problem_id" => new ApiExposedProperty("problem_id", true, GET, array(
                new NumericValidator()
            ))                        
        );
                
    }
    
    protected function ValidateRequest()
    {
        
        // Call parent
        parent::ValidateRequest();
    
        // Is the combination contest_id and problem_id valid?        
        if (is_null(
                ContestProblemsDAO::getByPK($this->request["contest_id"]->getValue(), 
                        $this->request["problem_id"]->getValue())))
        {
            die(json_encode($this->error_dispatcher->notFound()));
        }
                
        
        // If the contest is private, verify that our user is invited                
        $contest = ContestsDAO::getByPK($this->request["contest_id"]->getValue());                
                
        if ($contest->getPublic() === 0)
        {        
            if (is_null(ContestsUsersDAO::getByPK($this->user_id, $this->request["contest_id"]->getValue())))
            {
                die(json_encode($this->error_dispatcher->forbiddenSite()));
            }        
        }
    }
    
    protected function GenerateResponse() 
    {
       // Create array of relevant columns
        $relevant_columns = array("title", "author_id", "alias", "validator", "time_limit", "memory_limit", "visits", "submissions", "accepted", "difficulty", "creation_date", "source", "order");
        
        // Get our problem given the problem_id         
        try
        {
            
            $problem = ProblemsDAO::getByPK($this->request["problem_id"]->getValue());
        }
        catch(Exception $e)
        {
            // Operation failed in the data layer
            die(json_encode( $this->error_dispatcher->invalidDatabaseOperation() ));        
        
        }
        
        // Add the problem the response
        array_push($this->response, $problem->asFilteredArray($relevant_columns));               
     
        
        // Create array of relevant columns
        $relevant_columns = array("run_id", "language", "status", "veredict", "runtime", "memory", "score", "contest_score", "ip", "time", "submit_delay");
        
        // Search the relevant runs from the DB
        $keyrun = new Runs( array (
            "user_id" => $this->user_id,
            "problem_id" => $this->request["problem_id"]->getValue(),
            "contest_id" => $this->request["contest_id"]->getValue()
        ));
        
        // Get all the available runs
        try
        {            
            $runs_array = RunsDAO::search($keyrun);
        }
        catch(Exception $e)
        {
            // Operation failed in the data layer
            die(json_encode( $this->error_dispatcher->invalidDatabaseOperation() ));        
        }
        
        // Add each filtered run to an array
        if (count($runs_array) >= 0)
        {
            $runs_filtered_array = array();
            foreach($runs_array as $run)
            {
                array_push($runs_filtered_array, $run->asFilteredArray($relevant_columns));
            }
        }
        
        // Add the procesed runs to the request
        $this->response["runs"] = $runs_filtered_array;
        
    }
    
    protected function SendResponse() 
    {
        // There should not be any failing path that gets into here
        
        // Happy ending.
        die(json_encode(array(
                    "status"  => "ok",
                    "problem" => $this->response
        )));
               
    }
}

?>
