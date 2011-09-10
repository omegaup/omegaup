<?php

/**
 * 
 * Please read full (and updated) documentation at: 
 * https://github.com/omegaup/omegaup/wiki/Arena 
 *
 *
 * 
 * POST /contests/new
 * Si el usuario tiene permisos de juez o admin, crea un nuevo concurso, sin problemas asociados.
 *
 * */



// Declare that the API is using the database
define("WHOAMI", "API");
require_once("../../../../server/inc/bootstrap.php");

// @TODO Reduce all these includes
require_once("../../../../server/libs/ApiExposedProperty.php");
require_once("../../../../server/libs/StringValidator.php");
require_once("../../../../server/libs/NumericRangeValidator.php");
require_once("../../../../server/libs/NumericValidator.php");
require_once("../../../../server/libs/DateRangeValidator.php");
require_once("../../../../server/libs/DateValidator.php");
require_once("../../../../server/libs/EnumValidator.php");
require_once("../../../../server/libs/ApiHttpErrors.php");

// User ID to verify permisions
$user_id = null;
$error_dispatcher = ApiHttpErrors::getInstance();


// Check if we have a logged user.
 if( isset($_REQUEST["auth_token"]) )
    {

        // Find out the token
        $token = AuthTokensDAO::getByPK( $_POST["auth_token"] );

        if($token !== null){
            
            // Get the user_id from the auth token    
            $user_id = $token->getUserId();         

        }else{

            // We have an invalid auth token. Dying.            
            die(json_encode( $error_dispatcher->invalidAuthToken() ));

        }

    }

// @TODO Validate if the user has admin or judge roles

// Array of parameters we're exposing through the API. If a parameter is required, maps to TRUE
$parameters = array(
    new ApiExposedProperty("title", true, $_POST["title"], array( 
        new StringValidator($_POST["title"]))),
    
    new ApiExposedProperty("description", true, $_POST["description"], array( 
        new StringValidator($_POST["description"]))),
    
    new ApiExposedProperty("start_time", true, $_POST["start_time"], array( 
        new DateValidator($_POST["start_time"]),
        new DateRangeValidator($_POST["start_time"], $_POST["start_time"], $_POST["finish_time"] ))),
    
    new ApiExposedProperty("finish_time", true, $_POST["finish_time"], array( 
        new DateValidator($_POST["finish_time"]),
        new DateRangeValidator($_POST["finish_time"], $_POST["start_time"], $_POST["finish_time"] ))),
    
    new ApiExposedProperty("window_length", false, $_POST["window_length"], array( 
        new NumericValidator($_POST["window_length"]),
        new NumericRangeValidator($_POST["window_length"], 0, floor( strtotime($_POST["finish_time"]) - strtotime($_POST["start_time"]))/60 ))),
    
    new ApiExposedProperty("director_id", false, $user_id),
    
    new ApiExposedProperty("rerun_id", false, $_POST["rerun_id"]),
    
    new ApiExposedProperty("public", true, $_POST["public"], array(
        new NumericValidator($_POST["public"]))),
    
    new ApiExposedProperty("token", true, $_POST["token"], array( 
        new StringValidator($_POST["token"]))),
    
    new ApiExposedProperty("scoreboard", true, $_POST["scoreboard"], array( 
        new NumericValidator($_POST["scoreboard"]),
        new NumericRangeValidator($_POST["scoreboard"], 0, 100))),
    
    new ApiExposedProperty("points_decay_factor", true, $_POST["points_decay_factor"], array( 
        new NumericValidator($_POST["points_decay_factor"]),
        new NumericRangeValidator($_POST["points_decay_factor"], 0, 1))),
    
    new ApiExposedProperty("partial_score", true, $_POST["partial_score"], array( 
        new NumericValidator($_POST["partial_score"]))),
    
    new ApiExposedProperty("submissions_gap", true, $_POST["submissions_gap"], array(
        new NumericValidator($_POST["submissions_gap"]),
        new NumericRangeValidator($_POST["submissions_gap"], 0, strtotime($_POST["finish_time"]) - strtotime($_POST["start_time"]) ))),
    
    new ApiExposedProperty("feedback", true, $_POST["feedback"], array(
            new EnumValidator($_POST["feedback"], array("no", "yes", "partial")))),
    
    new ApiExposedProperty("penalty", true, $_POST["penalty"], array(
        new NumericValidator($_POST["penalty"]),
        new NumericRangeValidator($_POST["penalty"], 0, INF ))),
    
    new ApiExposedProperty("time_start", true, $_POST["time_start"], array(
        new EnumValidator($_POST["time_start"], array("contest", "problem")))) 
    );
  

// Validate all data 
foreach($parameters as $parameter)
{
    
    if ( !$parameter->validate() )
    {
        // In case of missing or validation failed parameters, send a BAD REQUEST        
        die(json_encode( $error_dispatcher->invalidParameter( $parameter->getError())));   
    }
}


// Fill $values array with values sent to the API
$contests_insert_values = array();
foreach($parameters as $parameter)
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
{  
    // Operation failed in the data layer
    die(json_encode( $error_dispatcher->invalidDatabaseOperation() ));    
}

// Happy ending.
die(json_encode(array(
    "status" => "ok"
)));
