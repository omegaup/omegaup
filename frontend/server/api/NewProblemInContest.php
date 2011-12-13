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

class NewProblemInContest extends ApiHandler
{            
    protected function RegisterValidatorsToRequest()
    {    
        // Only director is allowed to create problems in contest
        $contest = ContestsDAO::getByPK(RequestContext::get("contest_id"));                        
        if($contest->getDirectorId() !== $this->_user_id)
        {
            throw new ApiException(ApiHttpErrors::forbiddenSite());
        }
        
        ValidatorFactory::numericValidator()->addValidator(new CustomValidator(
                function ($value)
                {
                    // Check if the contest exists
                    return ContestsDAO::getByPK($value);
                }, "contest_id is invalid."))
            ->validate(RequestContext::get("contest_id"), "contest_id");
        
        ValidatorFactory::numericValidator()->addValidator(new CustomValidator(
                function ($value)
                {
                    // Check if the contest exists
                    return UsersDAO::getByPK($value);
                }, "author_id is invalid."))
            ->validate(RequestContext::get("author_id"), "author_id");
                
        ValidatorFactory::stringNotEmptyValidator()->validate(
                RequestContext::get("title"),
                "title");
                
        ValidatorFactory::stringNotEmptyValidator()->validate(
                RequestContext::get("alias"),
                "alias");
        
        ValidatorFactory::enumValidator(array("remote", "literal", "token", "token-caseless", "token-numeric"))
                ->validate(RequestContext::get("validator"), "validator");
        
        ValidatorFactory::numericRangeValidator(0, INF)
                ->validate(RequestContext::get("time_limit"), "time_limit");
        
        ValidatorFactory::numericRangeValidator(0, INF)
                ->validate(RequestContext::get("memory_limit"), "memory_limit");
        
        ValidatorFactory::htmlValidator()->validate(RequestContext::get("source"), "source");
                
        ValidatorFactory::enumValidator(array("normal", "inverse"))
                ->validate(RequestContext::get("order"), "order"); 
        
        ValidatorFactory::numericRangeValidator(0, INF)
                ->validate(RequestContext::get("points"), "points");

    }       
    
    protected function GenerateResponse() 
    {
        // Crete file
        try 
        {
            $filename = md5(uniqid(rand(), true));
            FileHandler::CreateFile(PROBLEMS_PATH . $filename, RequestContext::get("source"));                        
        }
        catch (Exception $e)
        {
            throw new ApiException( ApiHttpErrors::invalidFilesystemOperation() );
        }
        
        // Populate a new Problem object
        $problem = new Problems();
        $problem->setPublic(false);
        $problem->setAuthorId(RequestContext::get("author_id"));
        $problem->setTitle(RequestContext::get("title"));
        $problem->setAlias(RequestContext::get("alias"));
        $problem->setValidator(RequestContext::get("validator"));
        $problem->setTimeLimit(RequestContext::get("time_limit"));
        $problem->setMemoryLimit(RequestContext::get("memory_limit"));
        $problem->setVisits(0);
        $problem->setSubmissions(0);
        $problem->setAccepted(0);
        $problem->setDifficulty(0);
        $problem->setSource($filename);
        $problem->setOrder(RequestContext::get("order"));                              
                
        // Insert new problem
        try
        {
            //Begin transaction
            ProblemsDAO::transBegin();

            // Save the contest object with data sent by user to the database
            ProblemsDAO::save($problem);

            // Save relationship between problems and contest_id
            $relationship = new ContestProblems( array(
                "contest_id" => RequestContext::get("contest_id"),
                "problem_id" => $problem->getProblemId(),
                "points"     => RequestContext::get("points")));
            ContestProblemsDAO::save($relationship);

            //End transaction
            ProblemsDAO::transEnd();

        }catch(Exception $e)
        {  
            // Operation failed in the data layer
           throw new ApiException( ApiHttpErrors::invalidDatabaseOperation() );    
        }                
    }    
}

?>
