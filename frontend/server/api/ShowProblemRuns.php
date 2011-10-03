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
    
    protected function ProcessRequest()
    {
        $this->request = array(
            "problem_id" => new ApiExposedProperty("problem_id", true, GET, array(
                new NumericValidator(),
                new CustomValidator( 
                    function ($value)
                    {
                        // Check if the contest exists
                        return ProblemsDAO::getByPK($value);
                    }) 
            ))                                 
        );
                
    }   
        

    protected function GenerateResponse() 
    {
               
        // Define what we are looking for        
        $runs_mask = new Runs( array (
            "user_id"    => $this->user_id,
            "problem_id" => $this->request["problem_id"]->getValue()));
        
        $relevant_columns = array( "run_id", "language", "status", "veredict", "runtime", "memory", "score", "contest_score", "time", "submit_delay" );
        
        // Get our problems given the user_id and problem_id
        try
        {
            
            $runs = RunsDAO::search($runs_mask, "time", "DESC", $relevant_columns );
        }
        catch(Exception $e)
        {
            // Operation failed in the data layer
            die(json_encode( $this->error_dispatcher->invalidDatabaseOperation() ));        
        
        }
        
        // Add the clarificatoin the response
        foreach($runs as $run)
        { 
            $this->response[$run->getRunId()] = $run->asFilteredArray($relevant_columns);               
        }
             
        
    }
    
    protected function SendResponse() 
    {
        // There should not be any failing path that gets into here
        
        // Happy ending.
        die(json_encode(array(
                    "status"  => "ok",
                    "runs" => $this->response
        )));
               
    }
}

?>
