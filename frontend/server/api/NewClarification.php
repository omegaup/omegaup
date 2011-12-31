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
                
        ValidatorFactory::stringNotEmptyValidator()->validate(
                RequestContext::get("message"),
                "message");
        
        try
        {
            $contest = ContestsDAO::getByAlias(RequestContext::get("contest_alias"));                                        
            $problem = ProblemsDAO::getByAlias(RequestContext::get("problem_alias"));
        }
        catch(Exception $e)
        {
            throw new ApiException(ApiHttpErrors::invalidDatabaseOperation(), $e);
        }
        
        // Is the combination contest_id and problem_id valid?        
        if (is_null(
                ContestProblemsDAO::getByPK($contest->getContestId(), 
                                            $problem->getProblemId())))
        {
           throw new ApiException(ApiHttpErrors::notFound());
        }
        
    }
    
    protected function GenerateResponse() 
    {
        
        // Populate a new Clarification object
        try
        {
            $contest = ContestsDAO::getByAlias(RequestContext::get("contest_alias"));                                        
            $problem = ProblemsDAO::getByAlias(RequestContext::get("problem_alias"));
        }
        catch(Exception $e)
        {
            throw new ApiException(ApiHttpErrors::invalidDatabaseOperation(), $e);
        }
        
        $clarification = new Clarifications( array(
            "author_id" => $this->_user_id,
            "contest_id" => $contest->getContestId(),
            "problem_id" => $problem->getProblemId(),
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
           throw new ApiException( ApiHttpErrors::invalidDatabaseOperation(), $e );    
        }
        
        //Add the clarification id to the response
        $this->addResponse("clarification_id", $clarification->getClarificationId());
    }    
}

?>
