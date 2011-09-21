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

class NewClarification extends ApiHandler
{
    
    protected function ProcessRequest()
    {        
        // Array of parameters we're exposing through the API. If a parameter is required, maps to TRUE
        $this->request = array(
            "author_id" => new ApiExposedProperty("author_id", false, $this->user_id),
            
            "contest_id" => new ApiExposedProperty("contest_id", true, POST, array(
                new NumericValidator(),
                new CustomValidator( function ($value)
                        {
                            // Check if the contest exists
                            return ContestsDAO::getByPK($value);
                        })                        
            )),
                               
            "problem_id" => new ApiExposedProperty("problem_id", true, POST, array(
                new NumericValidator(),
                new CustomValidator( function ($value)
                        {
                            // Check if the problem exists
                            return ProblemsDAO::getByPK($value);
                        })                        
            )),
                                
            "message" => new ApiExposedProperty("message", true, POST, array(
                new StringValidator()
            ))
            
        );
        
    }
    
    protected function GenerateResponse() 
    {
        
        
        
        // Populate a new Clarification object
        $clarification = new Clarifications( array(
            "author_id" => $this->request["author_id"]->getValue(),
            "contest_id" => $this->request["contest_id"]->getValue(),
            "problem_id" => $this->request["problem_id"]->getValue(),
            "message" => $this->request["message"]->getValue(),
        ));

        // Insert new Clarification
        try
        {            

            // Save the clarification object with data sent by user to the database
            ClarificationsDAO::save($clarification);            

        }catch(Exception $e)
        {              
            // Operation failed in the data layer
            die(json_encode( $this->error_dispatcher->invalidDatabaseOperation() ));    
        }
        
        //Add the clarification id to the response
        $this->response = $clarification->getClarificationId();
    }
    
    protected function SendResponse() 
    {
        // There should not be any failing path that gets into here
        
        // Happy ending.
        die(json_encode(array(
            "status" => "ok",
            "clarification_id" => $this->response
        )));        
    }
}

?>
