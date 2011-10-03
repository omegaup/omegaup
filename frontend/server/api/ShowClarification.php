<?php

/**
 * 
 * Please read full (and updated) documentation at: 
 * https://github.com/omegaup/omegaup/wiki/Arena 
 *
 * 
 * POST /contests/:id
 * Si el usuario puede verlos, muestra los detalles del concurso :id.
 *
 * */

require_once("ApiHandler.php");

class ShowClarification extends ApiHandler
{
    protected function DeclareAllowedRoles() 
    {
        return BYPASS;
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
            ))                                 
        );
                
    }   
    
    protected function ValidateRequest() 
    {
        // Call generic validation
        parent::ValidateRequest();
        
        
        // If the clarification is private, verify that our user is invited                
        $clarification = ClarificationsDAO::getByPK($this->request["clarification_id"]->getValue());                
                
        if ($clarification->getPublic() === '0')
        {        
            if ($clarification->getAuthorId() != $this->user_id )
            {
               throw new ApiException($this->error_dispatcher->forbiddenSite());
            }        
        }                
 
    }


    protected function GenerateResponse() 
    {
       // Create array of relevant columns
        $relevant_columns = array("message", "answer", "time", "problem_id", "contest_id");
        
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
        
        // Add the clarificatoin the response
        $this->response = $clarification->asFilteredArray($relevant_columns);               
             
        
    }
        
}

?>
