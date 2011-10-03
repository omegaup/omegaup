<?php

/**
 * 
 * Please read full (and updated) documentation at: 
 * https://github.com/omegaup/omegaup/wiki/Arena 
 *
 * 
 * POST /contests/update/:id
 * Si el usuario puede verlo, actualiza la clarificación ID.
 *
 * */

require_once("ApiHandler.php");

class UpdateClarification extends ApiHandler
{
    
    protected function DeclareAllowedRoles() 
    {
        return array(JUDGE);
    }
    
    protected function GetRequest()
    {
        $this->request = array(
            "clarification_id" => new ApiExposedProperty("clarification_id", true, GET, array(
                new NumericValidator(),
                new CustomValidator( 
                    function ($value)
                    {
                        // Check if the contest exists
                        return ClarificationsDAO::getByPK($value);
                    }) 
            )),
            
            "answer" => new ApiExposedProperty("answer", true, POST, array(
                new StringValidator()
            )),
                            
            "message" => new ApiExposedProperty("message", false, POST, array(
                new StringValidator()
            )),                
                            
            "public" => new ApiExposedProperty("public", true, POST, array(
                new NumericValidator()
            ))
                            
        );
                
    }   
       


    protected function GenerateResponse() 
    {
                
        // Get our clarificatoin given the id
        try
        {
            
            $clarification = ClarificationsDAO::getByPK($this->request["clarification_id"]->getValue());
        }
        catch(Exception $e)
        {
            // Operation failed in the data layer
           throw new ApiException( $this->error_dispatcher->invalidDatabaseOperation() );        
        
        }
        
        // Update clarification
        if(!is_null($this->request["message"]->getValue()) )
        {
            // The clarificator may opt to modify the message (typos)
            $clarification->setMessage($this->request["message"]->getValue());
        }        
        $clarification->setAnswer($this->request["answer"]->getValue());
        $clarification->setPublic($this->request["public"]->getValue());
        
        
        // Save the clarification
        try
        {
            
            ClarificationsDAO::save($clarification);
        }
        catch( Exception $e)
        {
            // Operation failed in the data layer
           throw new ApiException( $this->error_dispatcher->invalidDatabaseOperation() );         
        }
        
        // Happy ending
        $this->response["status"] = "ok";
        
    }
    
}

?>
