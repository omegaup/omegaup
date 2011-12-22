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
            
        ValidatorFactory::enumValidator(array ('c','cpp','java','py','rb','pl','cs','p'))->validate(
            RequestContext::get("language"), "language");
        
        ValidatorFactory::stringNotEmptyValidator()->validate(RequestContext::get("source"), "source");
        
        try
        {
            $contest = ContestsDAO::getByAlias(RequestContext::get("contest_alias"));                                        
            $problem = ProblemsDAO::getByAlias(RequestContext::get("problem_alias"));
            
            // Validate that the combination contest_id problem_id is valid            
            if (!ContestProblemsDAO::getByPK(
                    $contest->getContestId(),
                    $problem->getProblemId()                
                ))
            {
               throw new ApiException(ApiHttpErrors::invalidParameter("problem_id and contest_id combination is invalid."));
            }
            
            // Before submit something, contestant had to open the problem/contest
            if(!ContestsUsersDAO::getByPK($this->_user_id, 
                    $contest->getContestId()))
            {
                throw new ApiException(ApiHttpErrors::forbiddenSite());
            }
                                    
            // Validate that the run is inside contest
            $contest = ContestsDAO::getByPK($contest->getContestId());
            if( !$contest->isInsideContest($this->_user_id))
            {                
                throw new ApiException(ApiHttpErrors::forbiddenSite());
            }
            
            // Validate if contest is private then the user should be registered
            if ( $contest->getPublic() == 0 
                && is_null(ContestsUsersDAO::getByPK(
                        $this->_user_id, 
                        $contest->getContestId()))
               )
            {
               throw new ApiException(ApiHttpErrors::forbiddenSite());
            }

            // Validate if the user is allowed to submit given the submissions_gap 
            if (!RunsDAO::IsRunInsideSubmissionGap(
                    $contest->getContestId(), 
                    $problem->getProblemId(), 
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
           throw new ApiException( ApiHttpErrors::invalidDatabaseOperation(), $e );    
        }                 
    }
    
    protected function ValidateRequest() 
    {                            
        
    }
    
    protected function GenerateResponse() 
    {          
        try
        {
            $contest = ContestsDAO::getByAlias(RequestContext::get("contest_alias"));                                        
            $problem = ProblemsDAO::getByAlias(RequestContext::get("problem_alias"));
        }
        catch(Exception $e)
        {
            throw new ApiException(ApiHttpErrors::invalidDatabaseOperation(), $e);
        }
        
        // Populate new run object
        $run = new Runs(array(
            "user_id" => $this->_user_id,
            "problem_id" => $problem->getProblemId(),
            "contest_id" => $contest->getContestId(),
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
           throw new ApiException( ApiHttpErrors::invalidDatabaseOperation(), $e );    
        }
        
        try
        {
            // Create file for the run        
            $filepath = RUNS_PATH . $run->getGuid();
            FileHandler::CreateFile($filepath, RequestContext::get("source"));            
        }
        catch (Exception $e)
        {
            throw new ApiException( ApiHttpErrors::invalidFilesystemOperation(), $e );                            
        }
        
        // @TODO Call lhchavez to evaluate run     
        
        // Happy ending
        $this->addResponse("run_alias", $run->getGuid());
        $this->addResponse("status", "ok");        
    }
}

?>
