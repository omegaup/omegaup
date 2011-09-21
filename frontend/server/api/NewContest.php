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
    
    private $private_users_list;
    
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

            "public" => new ApiExposedProperty("public", true, POST, array(
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
                new EnumValidator(array("contest", "problem")))),
            
            "private_users" => new ApiExposedProperty("private_users", false, POST)
            );
    }
    
    protected function ValidateRequest() 
    {
        parent::ValidateRequest();
                    
        // Validate private_users request, only if the contest is private        
        if($this->request["public"]->getValue() === "0")
        {
            if(is_null($this->request["private_users"]->getValue()))
            {
                die(json_encode( $this->error_dispatcher->invalidParameter("If the Contest is not Public, private_users is required") ));    
            }
            else
            {
                // Validate that the request is well-formed
                $this->private_users_list = json_decode($this->request["private_users"]->getValue());
                if (is_null($this->private_users_list))
                {
                    die(json_encode( $this->error_dispatcher->invalidParameter("private_users is malformed") ));    
                }
                
                // Validate that all users exists in the DB
                foreach($this->private_users_list as $userkey)
                {
                    if (!UsersDAO::getByPK($userkey))
                    {
                        die(json_encode( $this->error_dispatcher->invalidParameter("private_users contains a user that doesn't exists") ));    
                    }
                }                               
            }
        }
        
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
        
        // If the contest is Private, add the list of allowed users that is comming from private_users
        

        // Push changes
        try
        {
            // Begin a new transaction
            ContestsDAO::transBegin();
            
            // Save the contest object with data sent by user to the database
            ContestsDAO::save($contest);
            
            // If the contest is private, add the list of allowed users
            if ($this->request["public"]->getValue() === "0")
            {
               
                foreach($this->private_users_list as $userkey)
                {
                    // Create a temp DAO for the relationship
                    $temp_user_contest = new ContestsUsers( array(
                        "contest_id" => $contest->getContestId(),
                        "user_id" => $userkey,
                        "score" => 0,
                        "time" => 0
                    ));                    
                    
                    // Save the relationship in the DB
                    ContestsUsersDAO::save($temp_user_contest);
                }
            }
            
            // End transaction transaction
            ContestsDAO::transEnd();

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
