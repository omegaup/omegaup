<?php

/**
 * 
 * Please read full (and updated) documentation at: 
 * https://github.com/omegaup/omegaup/wiki/Arena 
 *
 * 
 * GET /runs/problem/:id
 * Si el usuario tiene permiso, regresa las referencias a las últimas 5 soluciones a un problema en particular que el mismo usuario ha enviado, y su estado y calificación.
 *
 * */

require_once("ApiHandler.php");

class ShowRun extends ApiHandler
{
    
    private $myRun;
    
    protected function GetRequest()
    {
        $this->request = array(
            "run_id" => new ApiExposedProperty("run_id", true, GET, array(
                new NumericValidator(),
                new CustomValidator( 
                    function ($value)
                    {
                        // Check if the run exists
                        return RunsDAO::getByPK($value);
                    }) 
            ))                                 
        );
                
    }   
    
    protected function ValidateRequest() 
    {
        parent::ValidateRequest();
        
        try
        {
            //@ If user is not judge, must be the run's owner.
            $this->myRun = RunsDAO::getByPK($this->request["run_id"]->getValue());
        }
        catch(Exception $e)
        {
            // Operation failed in the data layer
           throw new ApiException( $this->error_dispatcher->invalidDatabaseOperation());        
        }
        
        
        if($this->myRun->getUserId() !== $this->user_id)
        {
           throw new ApiException($this->error_dispatcher->forbiddenSite());
        }
    }



    protected function GenerateResponse() 
    {
        
        // Fill response
        $relevant_columns = array( "run_id", "language", "status", "veredict", "runtime", "memory", "score", "contest_score", "time", "submit_delay" );
        $this->response = $this->myRun->asFilteredArray($relevant_columns);
        
        try
        {
            // Get source code
            $filename = RUNS_PATH . $this->myRun->getGuid();
            
            if(file_exists($filename))
            {                            
                $fileHandle = fopen($filename, 'r');                                    
                $this->response["source"] = fread($fileHandle, filesize($filename));                
                fclose($fileHandle);
            }
            else
            {
                throw new ApiException( $this->error_dispatcher->invalidFilesystemOperation() );
            }
                
        }
        catch (Exception $e)
        {
           throw new ApiException( $this->error_dispatcher->invalidFilesystemOperation() );
        }
        
    }
    
           
}

?>
