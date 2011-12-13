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
    
    protected function RegisterValidatorsToRequest()
    {    
        
        ValidatorFactory::numericValidator()->addValidator(new CustomValidator(
                function ($value)
                {
                    // Check if the contest exists
                    return ContestsDAO::getByPK($value);
                }, "Contest requested is invalid."))
            ->validate(RequestContext::get("contest_id"), "contest_id");
                
        ValidatorFactory::numericValidator()->addValidator(new CustomValidator(
                function ($value)
                {
                    // Check if the contest exists
                    return ProblemsDAO::getByPK($value);
                }, "Problem requested is invalid."))
            ->validate(RequestContext::get("problem_id"), "problem_id");
                
        ValidatorFactory::stringNotEmptyValidator()->validate(
                RequestContext::get("message"),
                "message");
        
        
        // Is the combination contest_id and problem_id valid?        
        if (is_null(
                ContestProblemsDAO::getByPK(RequestContext::get("contest_id"), 
                                            RequestContext::get("problem_id"))))
        {
           throw new ApiException(ApiHttpErrors::notFound());
        }
        
    }
    
    protected function GenerateResponse() 
    {
        
        // Populate a new Clarification object
        $clarification = new Clarifications( array(
            "author_id" => $this->_user_id,
            "contest_id" => RequestContext::get("contest_id"),
            "problem_id" => RequestContext::get("problem_id"),
            "message" => RequestContext::get("message"),
            "public" => '0'
        ));

        // Insert new Clarification
        try
        {            
            // Save the clarification object with data sent by user to the database
            ClarificationsDAO::save($clarification);            

        }catch(Exception $e)
        {              
            // Operation failed in the data layer
           throw new ApiException( ApiHttpErrors::invalidDatabaseOperation() );    
        }
        
        //Add the clarification id to the response
        $this->addResponse("clarification_id", $clarification->getClarificationId());
    }    
}

?>
