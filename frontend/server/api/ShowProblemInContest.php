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

class ShowProblemInContest extends ApiHandler
{    
    protected function RegisterValidatorsToRequest()
    {
        ValidatorFactory::stringNotEmptyValidator()->addValidator(new CustomValidator(
                function ($value)
                {
                    // Check if the contest exists
                    return ContestsDAO::getByAlias($value);
                }, "Contest is invalid."))
            ->validate(RequestContext::get("contest_alias"), "contest_alias");
            
        ValidatorFactory::stringNotEmptyValidator()->addValidator(new CustomValidator(
            function ($value)
            {
                // Check if the contest exists
                return ProblemsDAO::getByAlias($value);
            }, "Problem requested is invalid."))
        ->validate(RequestContext::get("problem_alias"), "problem_alias");
         
            
            
        // Is the combination contest_id and problem_id valid?        
        $contest = ContestsDAO::getByAlias(RequestContext::get("contest_alias"));                                        
        $problem = ProblemsDAO::getByAlias(RequestContext::get("problem_alias"));
        if (is_null(
                ContestProblemsDAO::getByPK($contest->getContestId(), 
                                            $problem->getProblemId())))
        {
           throw new ApiException(ApiHttpErrors::notFound());
        }
                        
        // If the contest is private, verify that our user is invited                        
        if ($contest->getPublic() == 0)
        {                    
            if (is_null(ContestsUsersDAO::getByPK($this->_user_id, $contest->getContestId())))
            {                
                throw new ApiException(ApiHttpErrors::forbiddenSite());
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
            $problem = ProblemsDAO::getByAlias(RequestContext::get("problem_alias"));
        }
        catch(Exception $e)
        {
            // Operation failed in the data layer
           throw new ApiException( ApiHttpErrors::invalidDatabaseOperation() );        
        }        
        
        // Read the file that contains the source
        $source_path = PROBLEMS_PATH . $problem->getSource();
        try
        {
            $file_content = FileHandler::ReadFile($source_path);
        }
        catch(Exceptio $e)
        {
            throw new ApiException( ApiHttpErrors::invalidFilesystemOperation() );
        }        
        
        // Add the problem the response
        $this->addResponseArray($problem->asFilteredArray($relevant_columns));   
        
        // Overwrite source
        $this->addResponse("source", $file_content);        
             
        // Create array of relevant columns
        $relevant_columns = array("run_id", "language", "status", "veredict", "runtime", "memory", "score", "contest_score", "ip", "time", "submit_delay");
        
        // Search the relevant runs from the DB
        $contest = ContestsDAO::getByAlias(RequestContext::get("contest_alias"));                                        
        $keyrun = new Runs( array (
            "user_id" => $this->_user_id,
            "problem_id" => $problem->getProblemId(),
            "contest_id" => $contest->getContestId()
        ));
        
        // Get all the available runs
        try
        {            
            $runs_array = RunsDAO::search($keyrun);
        }
        catch(Exception $e)
        {
            // Operation failed in the data layer
           throw new ApiException( ApiHttpErrors::invalidDatabaseOperation() );        
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
        
        // At this point, contestant_user relationship should be established.        
        try
        {
            $contest_user = ContestsUsersDAO::CheckAndSaveFirstTimeAccess(
                    $this->_user_id, $contest->getContestId());
        }
        catch(Exception $e)
        {
             // Operation failed in the data layer
             throw new ApiException( ApiHttpErrors::invalidDatabaseOperation() );        
        }                
                        
        // As last step, register the problem as opened                
        if (! ContestProblemOpenedDAO::getByPK(
                $contest->getContestId(), $problem->getProblemId(), $this->_user_id ))
        {
            //Create temp object
            $keyContestProblemOpened = new ContestProblemOpened( array( 
                "contest_id" => $contest->getContestId(),
                "problem_id" => $problem->getProblemId(),
                "user_id" => $this->_user_id            
            ));
            
            try
            {
                // Save object in the DB
                ContestProblemOpenedDAO::save($keyContestProblemOpened);
                
            }catch (Exception $e)
            {
                // Operation failed in the data layer
               throw new ApiException( ApiHttpErrors::invalidDatabaseOperation() );        
            }                        
        }
        
        // Add the procesed runs to the request
        $this->addResponse("runs", $runs_filtered_array);        
    }    
}

?>
