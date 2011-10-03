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
    
    protected function GetRequest()
    {
        $this->request = array(
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
   

    protected function GenerateResponse() 
    {
       // Create array of relevant columns
        $relevant_columns = array("message", "answer", "time");
        
        //Get all public clarifications
        $public_clarification_mask = new Clarifications ( array (
           "public" => '1',
           "problem_id" => $this->request["problem_id"]->getValue()
        ));
        
        // Get all private clarifications of the user 
        $private_clarification_mask = new Clarifications ( array (
           "public" => '0',
           "problem_id" => $this->request["problem_id"]->getValue(),
           "author_id" => $this->user_id
            
        ));
        
        //@todo This query should be merged and optimized ....
        // Get our clarification given the masks
        try
        {
            
            $clarifications_public = ClarificationsDAO::search($public_clarification_mask);
            $clarifications_private = ClarificationsDAO::search($private_clarification_mask);
        }
        catch(Exception $e)
        {
            // Operation failed in the data layer
           throw new ApiException( $this->error_dispatcher->invalidDatabaseOperation() );        
        
        }
                        
        // Filter each clarification and add it to the response
        foreach($clarifications_public as $clarification)
        {
            array_push($this->response, $clarification->asFilteredArray($relevant_columns));               
        }
         
        // Filter each clarification and add it to the response
        foreach($clarifications_private as $clarification)
        {
            array_push($this->response, $clarification->asFilteredArray($relevant_columns));               
        }
        
    }
        
}

?>
