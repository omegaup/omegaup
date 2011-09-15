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
            new ApiExposedProperty("contest_id", true, $_GET["contest_id"], array(
                new NumericValidator($_GET["contest_id"]),
                new CustomValidator($_GET["contest_id"], function ($value)
                        {
                            // Check if the contest exists
                            return ContestsDAO::getByPK($value);
                        })                        
            )),

            new ApiExposedProperty("public", false, FALSE), // All problems created through this API will be private at their creation 

            new ApiExposedProperty("author_id", true, $this->user_id),

            new ApiExposedProperty("title", true, $_POST["title"], array(
                new StringValidator($_POST["title"]))),

            new ApiExposedProperty("alias", false, is_null($_POST["alias"]) ? substr($_POST["title"], 0, 10) : $_POST["alias"] ),

            new ApiExposedProperty("validator", true, $_POST["validator"], array(
                new EnumValidator($_POST["validator"], array("remote", "literal", "token", "token-caseless", "token-numeric"))
            )),

            new ApiExposedProperty("time_limit", true, (int)$_POST["time_limit"], array(
                new NumericValidator($_POST["time_limit"]),
                new NumericRangeValidator($_POST["time_limit"], 0, INF)
            )),

            new ApiExposedProperty("memory_limit", true, $_POST["memory_limit"], array(
                new NumericValidator($_POST["memory_limit"]),
                new NumericRangeValidator($_POST["memory_limit"], 0, INF)
            )),

            new ApiExposedProperty("visits", true, 0),
            new ApiExposedProperty("submissions", true, 0),
            new ApiExposedProperty("accepted", true, 0),
            new ApiExposedProperty("difficulty", true, 0),

            new ApiExposedProperty("source", true, $_POST["source"], array(
                new HtmlValidator($_POST["source"])
            )),

            new ApiExposedProperty("order", true, $_POST["order"], array(
                new EnumValidator($_POST["order"], array("normal", "inverse"))
            )),

            new ApiExposedProperty("points", true, $_POST["points"], array(
                new NumericValidator($_POST["points"]),
                new NumericRangeValidator($_POST["points"], 0, INF)
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
