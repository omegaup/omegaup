<?php

/**
 * 
 * Please read full (and updated) documentation at: 
 * https://github.com/omegaup/omegaup/wiki/Arena 
 *
 * 
GET /clarifications/problem/:id
Regresa TODAS las clarificaciones de un problema en particular, a las cuales el usuario puede ver (equivale a las que el personalmente mandó más todas las clarificaciones del problema marcadas como globales)
 *
 * */

require_once("ApiHandler.php");

class ShowClarificationsInProblem extends ApiHandler
{
    
    protected function RegisterValidatorsToRequest()
    {
        ValidatorFactory::numericValidator()->addValidator(new CustomValidator(
            function ($value)
            {
                // Check if the contest exists
                return ProblemsDAO::getByPK($value);
            }, "Problem is invalid."))
        ->validate(RequestContext::get("problem_id"), "problem_id");    
                
    }   
   

    protected function GenerateResponse() 
    {
       // Create array of relevant columns
        $relevant_columns = array("message", "answer", "time");
        
        //Get all public clarifications
        $public_clarification_mask = new Clarifications ( array (
           "public" => '1',
           "problem_id" => RequestContext::get("problem_id")
        ));
        
        try
        {
            // Get contest to get Director Id
            $contest_problem = ContestProblemsDAO::search(new ContestProblems(array(
                "problem_id" => RequestContext::get("problem_id")
            )));
            $contest_problem = $contest_problem[0];        
            $contest = ContestsDAO::getByPK($contest_problem->getContestId());        
        }
        catch(Exception $e)
        {
            throw new ApiException( ApiHttpErrors::invalidDatabaseOperation() );                
        }
        
        // If user is the contest director, get all private clarifications        
        if($contest->getDirectorId() == $this->_user_id)
        {                        
            // Get all private clarifications 
            $private_clarification_mask = new Clarifications ( array (
               "public" => '0',
               "problem_id" => RequestContext::get("problem_id")
            )); 
        }        
        else
        {        
            // Get private clarifications of the user 
            $private_clarification_mask = new Clarifications ( array (
               "public" => '0',
               "problem_id" => RequestContext::get("problem_id"),
               "author_id" => $this->_user_id
            ));
        }       
        
        //@todo This query could be merged and optimized 
        // Get our clarifications given the masks
        try
        {               
            $clarifications_public = ClarificationsDAO::search($public_clarification_mask);
            $clarifications_private = ClarificationsDAO::search($private_clarification_mask);            
        }
        catch(Exception $e)
        {
            // Operation failed in the data layer
           throw new ApiException( ApiHttpErrors::invalidDatabaseOperation() );        
        }

        $clarifications_array = array();
        // Filter each Public clarification and add it to the response        
        foreach($clarifications_public as $clarification)
        {
            array_push($clarifications_array, $clarification->asFilteredArray($relevant_columns));            
        }
         
        // Filter each Private clarification and add it to the response
        foreach($clarifications_private as $clarification)
        {
            array_push($clarifications_array, $clarification->asFilteredArray($relevant_columns));            
        }
        
        // Sort final array by time
        usort($clarifications_array, function ($a,$b) 
            { 
                $t1 = strtotime($a["time"]);
                $t2 = strtotime($b["time"]);
                
                if($t1 == $t2)
                    return 0;
                
                return ($t1 > $t2) ? -1 : 1;             
            });
            
        // Add response to array
        $this->addResponseArray($clarifications_array);
    }
        
}

?>
