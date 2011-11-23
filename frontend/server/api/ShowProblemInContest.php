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
    protected function DeclareAllowedRoles() 
    {
        return BYPASS;
    }
    
    protected function GetRequest()
    {
        $this->request = array(
            "contest_id" => new ApiExposedProperty("contest_id", true, GET, array(
                new NumericValidator(),                
                new CustomValidator( 
                    function ($value)
                    {
                        // Check if the contest exists
                        return ContestsDAO::getByPK($value);
                    }) 
            )),
            "problem_id" => new ApiExposedProperty("problem_id", true, GET, array(
                new NumericValidator(),
                new CustomValidator( 
                    function ($value)
                    {
                        // Check if the problem exists
                        return ProblemsDAO::getByPK($value);
                    }) 
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
           throw new ApiException($this->error_dispatcher->notFound());
        }
                
        
        // If the contest is private, verify that our user is invited                
        $contest = ContestsDAO::getByPK($this->request["contest_id"]->getValue());                                        
        if ($contest->getPublic() == 0)
        {                    
            if (is_null(ContestsUsersDAO::getByPK($this->user_id, $this->request["contest_id"]->getValue())))
            {                
                throw new ApiException($this->error_dispatcher->forbiddenSite());
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
           throw new ApiException( $this->error_dispatcher->invalidDatabaseOperation() );        
        }        
        
        // Read the file that contains the source
        $source_path = PROBLEMS_PATH . $problem->getSource();
        if(file_exists($source_path))
        {            
            $file_content = file_get_contents($source_path);
            if( !$file_content )
            {
                throw new ApiException( $this->error_dispatcher->invalidFilesystemOperation() );
            }            
        }        
        else
        {
            throw new ApiException( $this->error_dispatcher->invalidFilesystemOperation() );                    
        }        
        
        // Add the problem the response
        $this->response = $problem->asFilteredArray($relevant_columns);   
        
        // Overwrite source
        $this->response["source"] = $file_content;
             
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
           throw new ApiException( $this->error_dispatcher->invalidDatabaseOperation() );        
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
                        
        // As last step, register the problem as opened                
        if (! ContestProblemOpenedDAO::getByPK($this->request["contest_id"]->getValue(), $this->request["problem_id"]->getValue(), $this->user_id ))
        {
            //Create temp object
            $keyContestProblemOpened = new ContestProblemOpened( array( 
                "contest_id" =>   $this->request["contest_id"]->getValue(),
                "problem_id" =>   $this->request["problem_id"]->getValue(),
                "user_id" => $this->user_id            
            ));
            
            try
            {
                // Save object in the DB
                ContestProblemOpenedDAO::save($keyContestProblemOpened);
                
            }catch (Exception $e)
            {
                // Operation failed in the data layer
               throw new ApiException( $this->error_dispatcher->invalidDatabaseOperation() );        
            }                        
        }
        
        // Add the procesed runs to the request
        $this->response["runs"] = $runs_filtered_array;
        
    }
    
}

?>
