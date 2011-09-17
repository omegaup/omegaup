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

class NewContest extends ApiHandler
{
    
    protected function ProcessRequest()
    {  
        
        // Required parameteres to avoid Warnings
        // @todo Refactor this somehow        
        $finish_time = isset ($_POST["finish_time"]) ? $_POST["finish_time"] : die(json_encode($this->error_dispatcher->invalidParameter()));
        $start_time = isset ($_POST["start_time"]) ? $_POST["start_time"] : die(json_encode($this->error_dispatcher->invalidParameter()));
        
        
        
        // Array of parameters we're exposing through the API. If a parameter is required, maps to TRUE
        $this->request = array(
            new ApiExposedProperty("title", true, POST, array( 
                new StringValidator())),

            new ApiExposedProperty("description", true, POST, array( 
                new StringValidator())),

            new ApiExposedProperty("start_time", true, POST, array( 
                new DateValidator(),
                new DateRangeValidator($start_time, $finish_time ))),

            new ApiExposedProperty("finish_time", true, POST, array( 
                new DateValidator(),
                new DateRangeValidator( $start_time, $finish_time ))),

            new ApiExposedProperty("window_length", false, POST, array( 
                new NumericValidator(),
                new NumericRangeValidator( 0, floor( strtotime($finish_time) - strtotime($start_time))/60 ))),

            new ApiExposedProperty("director_id", false, $this->user_id),

            new ApiExposedProperty("rerun_id", false, 0),

            new ApiExposedProperty("public", true, POST, array(
                new NumericValidator())),

            new ApiExposedProperty("token", true, POST, array( 
                new StringValidator())),

            new ApiExposedProperty("scoreboard", true, POST, array( 
                new NumericValidator(),
                new NumericRangeValidator( 0, 100))),

            new ApiExposedProperty("points_decay_factor", true, POST, array( 
                new NumericValidator(),
                new NumericRangeValidator(0, 1))),

            new ApiExposedProperty("partial_score", true, POST, array( 
                new NumericValidator())),

            new ApiExposedProperty("submissions_gap", true, POST, array(
                new NumericValidator(),
                new NumericRangeValidator(0, strtotime($finish_time) - strtotime($start_time) ))),

            new ApiExposedProperty("feedback", true, POST, array(
                    new EnumValidator(array("no", "yes", "partial")))),

            new ApiExposedProperty("penalty", true, POST, array(
                new NumericValidator(),
                new NumericRangeValidator(0, INF ))),

            new ApiExposedProperty("time_start", true, POST, array(
                new EnumValidator(array("contest", "problem")))) 
            );
    }
    
    protected function GenerateResponse() 
    {
        
        // Fill $values array with values sent to the API
        $contests_insert_values = array();
        foreach($this->request as $parameter)
        {
            $contests_insert_values[$parameter->getPropertyName()] = $parameter->getValue();        
        }

        // Populate a new Contests object
        $contest = new Contests($contests_insert_values);

        // Push changes
        try
        {
            // Save the contest object with data sent by user to the database
            ContestsDAO::save($contest);

        }catch(Exception $e)
        {   var_dump($e);
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
