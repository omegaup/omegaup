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

class NewRun extends ApiHandler
{     
    protected function GetRequest()
    {                  
        
        // Array of parameters we're exposing through the API. If a parameter is required, maps to TRUE
        $this->request = array(
            "user_id" => new ApiExposedProperty("user_id", false, $this->user_id),
            "problem_id" => new ApiExposedProperty("problem_id", true, POST, array (
                new NumericValidator(),
                new CustomValidator( function ($value)
                        {
                            // Check if the problem exists
                            return ProblemsDAO::getByPK($value);
                        })  
            )),
            "contest_id" => new ApiExposedProperty("contest_id", true, POST, array(
                new NumericValidator(),
                new CustomValidator( function ($value)
                        {
                            // Check if the contest exists
                            return ContestsDAO::getByPK($value);
                        })                        
            )),
            "language" => new ApiExposedProperty("language", true, POST, array(
                new EnumValidator(array ('c','cpp','java','py','rb','pl','cs','p'))
            )),
            
            "source" => new ApiExposedProperty("source", true, POST, array(
                new StringValidator()
            )),
                                
            // Not-required properties:                   
            "status" => new ApiExposedProperty("status", false, "new"),
            "runtime" => new ApiExposedProperty("runtime", false, 0),
            "memory" => new ApiExposedProperty("memory", false, 0),
            "score" => new ApiExposedProperty("score", false, 0),
            "contest_score" => new ApiExposedProperty("contest_score", false, 0),
            "ip" => new ApiExposedProperty("ip", false, $_SERVER['REMOTE_ADDR']),
            "submit_delay" => new ApiExposedProperty("submit_delay", false, 0),
            "guid" => new ApiExposedProperty("guid", false, md5(uniqid(rand(), true))),
            "veredict" => new ApiExposedProperty("veredict", false, "JE")                                
            
        );        
    }
    
    protected function ValidateRequest() 
    {
        parent::ValidateRequest();
                    
        try
        {
            // Validate that the combination contest_id problem_id is valid
            // @todo Cache this!
            if (!ContestProblemsDAO::getByPK(
                    $this->request["contest_id"]->getValue(), 
                    $this->request["problem_id"]->getValue() 
                ))
            {
               throw new ApiException($this->error_dispatcher->invalidParameter("problem_id and contest_id combination is invalid."));
            }
            
            // Before submit something, contestant had to open the problem/contest
            if(!ContestsUsersDAO::getByPK($this->user_id, 
                    $this->request["contest_id"]->getValue()))
            {
                throw new ApiException($this->error_dispatcher->forbiddenSite());
            }
                                    
            // Validate that the run is inside contest
            $contest = ContestsDAO::getByPK($this->request["contest_id"]->getValue());
            if( !$contest->isInsideContest($this->user_id))
            {                
                throw new ApiException($this->error_dispatcher->forbiddenSite());
            }
            
            // Validate if contest is private then the user should be registered
            if ( $contest->getPublic() == 0 
                && is_null(ContestsUsersDAO::getByPK(
                        $this->user_id, 
                        $this->request["contest_id"]->getValue()))
               )
            {
               throw new ApiException($this->error_dispatcher->forbiddenSite());
            }

            // Validate if the user is allowed to submit given the submissions_gap 
            if (!RunsDAO::IsRunInsideSubmissionGap(
                    $this->request["contest_id"]->getValue(), 
                    $this->request["problem_id"]->getValue(), 
                    $this->user_id)
               )
            {                
               throw new ApiException($this->error_dispatcher->notAllowedToSubmit());
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
           throw new ApiException( $this->error_dispatcher->invalidDatabaseOperation() );    
        }
    }
    
    protected function GenerateResponse() 
    {
                        
        // Fill values 
        $run_insert_values = array();
        foreach($this->request as $parameter)
        {
            $run_insert_values[$parameter->getPropertyName()] = $parameter->getValue();        
        }
        
        // Populate new run object
        $run = new Runs($run_insert_values);                
        try
        {
            // Push run into DB
            RunsDAO::save($run);
        }
        catch(Exception $e)
        {   
            // Operation failed in the data layer
           throw new ApiException( $this->error_dispatcher->invalidDatabaseOperation() );    
        }
        
        try
        {
            // Create file for the run        
            $filename = $this->request["guid"]->getValue();
            $fileHandle = fopen(RUNS_PATH . $filename, 'w');
            fwrite($fileHandle, $this->request["source"]->getValue());
            fclose($fileHandle);
        }
        catch (Exception $e)
        {
            throw new ApiException( $this->error_dispatcher->invalidFilesystemOperation() );                            
        }
        
        // @TODO Call lhchavez to evaluate run
     
        
        // Happy ending
        $this->response["status"] = "ok";
    }

}

?>
