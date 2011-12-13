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

    protected function RegisterValidatorsToRequest()
    {
        ValidatorFactory::numericValidator()->addValidator(new CustomValidator(
            function ($value)
            {
                // Check if the contest exists
                return ClarificationsDAO::getByPK($value);
            }, "Contest is invalid."))
        ->validate(RequestContext::get("clarification_id"), "clarification_id");    
        
            
        // If the clarification is private, verify that our user is invited or is contest director               
        $clarification = ClarificationsDAO::getByPK(RequestContext::get("clarification_id"));                                
        if ($clarification->getPublic() === '0')
        {
            $contest = ContestsDAO::getByPK($clarification->getContestId());            
            if (!($clarification->getAuthorId() == $this->_user_id || $contest->getDirectorId() == $this->_user_id))
            {
               throw new ApiException(ApiHttpErrors::forbiddenSite());
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
            $clarification = ClarificationsDAO::getByPK(RequestContext::get("clarification_id"));
        }
        catch(Exception $e)
        {
            // Operation failed in the data layer
           throw new ApiException( ApiHttpErrors::invalidDatabaseOperation() );                
        }
        
        // Add the clarificatoin the response
        $this->addResponseArray($clarification->asFilteredArray($relevant_columns));                             
    }        
}

?>
