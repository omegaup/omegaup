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

class NewRun extends ApiHandler
{     
    protected function RegisterValidatorsToRequest()
    {    
        
        ValidatorFactory::numericValidator()->addValidator(new CustomValidator(
            function ($value)
            {
                // Check if the contest exists
                return ProblemsDAO::getByPK($value);
            }, "Problem requested is invalid."))
        ->validate(RequestContext::get("problem_id"), "problem_id");
            
        ValidatorFactory::numericValidator()->addValidator(new CustomValidator(
            function ($value)
            {
                // Check if the contest exists
                return ContestsDAO::getByPK($value);
            }, "Contest requested is invalid."))
        ->validate(RequestContext::get("contest_id"), "contest_id");
            
        ValidatorFactory::enumValidator(array ('c','cpp','java','py','rb','pl','cs','p'))->validate(
            RequestContext::get("language"), "language");
        
        ValidatorFactory::stringNotEmptyValidator()->validate(RequestContext::get("source"), "source");
        
        try
        {
            // Validate that the combination contest_id problem_id is valid            
            if (!ContestProblemsDAO::getByPK(
                    RequestContext::get("contest_id"),
                    RequestContext::get("problem_id")                    
                ))
            {
               throw new ApiException(ApiHttpErrors::invalidParameter("problem_id and contest_id combination is invalid."));
            }
            
            // Before submit something, contestant had to open the problem/contest
            if(!ContestsUsersDAO::getByPK($this->_user_id, 
                    RequestContext::get("contest_id")))
            {
                throw new ApiException(ApiHttpErrors::forbiddenSite());
            }
                                    
            // Validate that the run is inside contest
            $contest = ContestsDAO::getByPK(RequestContext::get("contest_id"));
            if( !$contest->isInsideContest($this->_user_id))
            {                
                throw new ApiException(ApiHttpErrors::forbiddenSite());
            }
            
            // Validate if contest is private then the user should be registered
            if ( $contest->getPublic() == 0 
                && is_null(ContestsUsersDAO::getByPK(
                        $this->_user_id, 
                        RequestContext::get("contest_id")))
               )
            {
               throw new ApiException(ApiHttpErrors::forbiddenSite());
            }

            // Validate if the user is allowed to submit given the submissions_gap 
            if (!RunsDAO::IsRunInsideSubmissionGap(
                    RequestContext::get("contest_id"), 
                    RequestContext::get("problem_id"), 
                    $this->_user_id)
               )
            {                
               throw new ApiException(ApiHttpErrors::notAllowedToSubmit());
            }
        
        
            // @todo Validate window_length
        }
        catch(ApiException $apiException)
        {
            // Propagate ApiException
            throw $apiException;
        }
        catch(Exception $e)
        {            
            // Operation failed in the data layer
           throw new ApiException( ApiHttpErrors::invalidDatabaseOperation() );    
        }                 
    }
    
    protected function ValidateRequest() 
    {                            
        
    }
    
    protected function GenerateResponse() 
    {                                        
        // Populate new run object
        $run = new Runs(array(
            "user_id" => $this->_user_id,
            "problem_id" => RequestContext::get("problem_id"),
            "contest_id" => RequestContext::get("contest_id"),
            "language" => RequestContext::get("language"),
            "source" => RequestContext::get("source"),
            "status" => "new",
            "runtime" => 0,
            "memory" => 0,
            "score" => 0,
            "contest_score" => 0,
            "ip" => $_SERVER['REMOTE_ADDR'],
            "submit_delay" => 0,
            "guid" => md5(uniqid(rand(), true)),
            "veredict" => "JE"
        ));                
        try
        {
            // Push run into DB
            RunsDAO::save($run);
        }
        catch(Exception $e)
        {   
            // Operation failed in the data layer
           throw new ApiException( ApiHttpErrors::invalidDatabaseOperation() );    
        }
        
        try
        {
            // Create file for the run        
            $filepath = RUNS_PATH . $run->getGuid();
            FileHandler::CreateFile($filepath, RequestContext::get("source"));            
        }
        catch (Exception $e)
        {
            throw new ApiException( ApiHttpErrors::invalidFilesystemOperation() );                            
        }
        
        // @TODO Call lhchavez to evaluate run     
        
        // Happy ending
        $this->addResponse("status", "ok");        
    }
}

?>
