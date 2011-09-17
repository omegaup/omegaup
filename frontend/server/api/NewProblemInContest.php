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

class NewProblemInContest extends ApiHandler
{
    
    protected function ProcessRequest()
    {        
        // Array of parameters we're exposing through the API. If a parameter is required, maps to TRUE
        $this->request = array(
            new ApiExposedProperty("contest_id", true, GET, array(
                new NumericValidator(),
                new CustomValidator( function ($value)
                        {
                            // Check if the contest exists
                            return ContestsDAO::getByPK($value);
                        })                        
            )),

            new ApiExposedProperty("public", false, FALSE), // All problems created through this API will be private at their creation 

            new ApiExposedProperty("author_id", true, $this->user_id),

            new ApiExposedProperty("title", true, POST, array(
                new StringValidator())),

            new ApiExposedProperty("alias", false, POST),

            new ApiExposedProperty("validator", true, POST, array(
                new EnumValidator(array("remote", "literal", "token", "token-caseless", "token-numeric"))
            )),

            new ApiExposedProperty("time_limit", true, POST, array(
                new NumericValidator(),
                new NumericRangeValidator(0, INF)
            )),

            new ApiExposedProperty("memory_limit", true, POST, array(
                new NumericValidator(),
                new NumericRangeValidator(0, INF)
            )),

            new ApiExposedProperty("visits", true, 0),
            new ApiExposedProperty("submissions", true, 0),
            new ApiExposedProperty("accepted", true, 0),
            new ApiExposedProperty("difficulty", true, 0),

            new ApiExposedProperty("source", true, POST, array(
                new HtmlValidator()
            )),

            new ApiExposedProperty("order", true, POST, array(
                new EnumValidator(array("normal", "inverse"))
            )),

            new ApiExposedProperty("points", true, POST, array(
                new NumericValidator(),
                new NumericRangeValidator(0, INF)
            ))
        );
        
    }
    
    protected function GenerateResponse() 
    {
        // Create file for problem content
        // @TODO clean the path
        $filename = md5(uniqid(rand(), true));
        $fileHandle = fopen(SERVER_PATH ."/../problems/".$filename, 'w') or die(json_encode( $this->error_dispatcher->invalidFilesystemOperation() ));    
        fwrite($fileHandle, $_POST["source"]) or die(json_encode( $this->error_dispatcher->invalidFilesystemOperation() ));
        fclose($fileHandle);
        
        
        // Fill $values array with values sent to the API
        $problems_insert_values = array();
        foreach($this->request as $parameter)
        {
            // Replace the HTML in source with the path to the file saved 
            if ($parameter->getPropertyName() == "source")
            {        
                $parameter->setValue($filename);
            }

            // Copy all except contest_id
            if ($parameter->getPropertyName() !== "contest_id") 
            {
                $problems_insert_values[$parameter->getPropertyName()] = $parameter->getValue();        
            }
        }
        
        
        // Populate a new Contests object
        $problem = new Problems($problems_insert_values);

        // Insert new problem
        try
        {
            //Begin transaction
            ProblemsDAO::transBegin();

            // Save the contest object with data sent by user to the database
            ProblemsDAO::save($problem);

            // Save relationship between problems and contest_id
            $relationship = new ContestProblems( array(
                "contest_id" => $_GET["contest_id"],
                "problem_id" => $problem->getProblemId(),
                "points"     => $_POST["points"]));
            ContestProblemsDAO::save($relationship);

            //End transaction
            ProblemsDAO::transEnd();

        }catch(Exception $e)
        {  

            // Operation failed in the data layer
            die(json_encode( $this->error_dispatcher->invalidDatabaseOperation() ));    
        }
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
