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
          
    protected function ProcessRequest()
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
                    
        // Validate that the combination contest_id problem_id is valid
        
        // Validate if contest is private then the user should be registered
        
        // Validate if the user is allowed to submit given the submissions_gap 
        if (!RunsDAO::IsRunInsideSubmissionGap($this->request["contest_id"]->getValue(), 
                $this->request["problem_id"]->getValue(), 
                $this->request["user_id"]->getValue()))
        {
            die(json_encode($this->error_dispatcher->notAllowedToSubmit()));
        }
        

        
        // Validate window_length
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
            die(json_encode( $this->error_dispatcher->invalidDatabaseOperation() ));    
        }
        
        // Create file for the run        
        $filename = $this->request["guid"]->getValue();
        $fileHandle = fopen(SERVER_PATH ."/../runs/".$filename, 'w') or die(json_encode( $this->error_dispatcher->invalidFilesystemOperation() ));                            
        fwrite($fileHandle, $this->request["source"]->getValue()) or die(json_encode( $this->error_dispatcher->invalidFilesystemOperation() ));        
        fclose($fileHandle);
        
        // @TODO Call lhchavez to evaluate run
        
    }
    
    protected function SendResponse() 
    {
        // There should not be any failing path that gets into here
        
        // Happy ending.
        die(json_encode(array(
            "status" => "ok"
        )));        
    }
}

?>
