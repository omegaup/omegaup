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
    
    protected function DeclareAllowedRoles() 
    {
        return array(JUDGE);
    }
    
    protected function GetRequest()
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

            // Author may not necesarly be the person who submits the problem
            new ApiExposedProperty("author_id", true, POST, array(
                new NumericValidator(),
                new CustomValidator( function ($value)
                        {
                            // Check if the contest exists
                            return UsersDAO::getByPK($value);
                        })                
            )),

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

        try 
        {
    
            // Create file for problem content            
            $filename = md5(uniqid(rand(), true));
            $fileHandle = fopen(PROBLEMS_PATH . $filename, 'w'); 
            fwrite($fileHandle, $_POST["source"]);
            fclose($fileHandle);
        }
        catch (Exception $e)
        {
            throw new ApiException( $this->error_dispatcher->invalidFilesystemOperation() );
        }
        
        
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
           throw new ApiException( $this->error_dispatcher->invalidDatabaseOperation() );    
        }
        
        // Happy ending
        $this->response["status"] = "ok";
    }
    

}

?>
