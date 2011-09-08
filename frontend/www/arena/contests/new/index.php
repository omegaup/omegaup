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

// User ID to verify permisions
$user_id = null;


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
            header('HTTP/1.1 401 FORBIDDEN');

            die(json_encode(array(
                    "status" => "error",
                    "error"	 => "You supplied an invalid auth token, or maybe it expired.",
                    "errorcode" => 500
            )));

        }

    }

// @TODO Validate if the user has admin or judge roles

// Array of parameters we're exposing through the API. If a parameter is required, maps to TRUE
$relevant_columns = array(
    "title" => true,
    "description" => true,
    "start_time" => true,
    "finish_time" => true,
    "window_length" => false,
    "director_id" => false,
    "rerun_id" => false,
    "public" => true,
    "token" => true,
    "scoreboard" => true,
    "points_decay_factor" => true,
    "partial_score" => true,
    "submissions_gap" => true,
    "feedback" => true,
    "penalty" => true,
    "time_start" => true,
    );  

// Validate if we have all required paramaters
foreach($relevant_columns as $key => $isRequired)
{
    
    if ( $isRequired && !isset($_POST[$key]) )
    {
        // In case of missing parameters, send a BAD REQUEST
        header('HTTP/1.1 400 BAD REQUEST');

        die(json_encode(array(
                "status" => "error",
                "error"	 => "You are missing some required parameters. (".$key.")",
                "errorcode" => 100
        )));   
    }
}

// Sanity check for strings
if( $_POST["title"] == "" || $_POST["description"] == "" || $_POST["token"] == "")
{
    // @TODO What about token?
    // 
    // In case of bad parameters, send a BAD REQUEST
    header('HTTP/1.1 400 BAD REQUEST');

    die(json_encode(array(
        "status" => "error",
        "error"	 => "You are sending an empty string that is required.",
        "errorcode" => 100
    )));  
}

// Sanity check for window_length, assuming minutes
$diffTimeInSeconds = strtotime($_POST["finish_time"]) >= strtotime($_POST["start_time"]);
if( $_POST["window_length"] < 0 || $_POST["window_length"] > floor( $diffTimeInMinutes / 60 ) )
{
    // In case of bad parameters, send a BAD REQUEST
    header('HTTP/1.1 400 BAD REQUEST');

    die(json_encode(array(
        "status" => "error",
        "error"	 => "window_length shuold be between contest's boundaries",
        "errorcode" => 100
    )));              
}

// Sanity check for submissions_gap
if ($_POST["submissions_gap"] > 0 && $_POST["submissions_gap"] > $diffTimeInSeconds)
{
    // In case of bad parameters, send a BAD REQUEST
    header('HTTP/1.1 400 BAD REQUEST');

    die(json_encode(array(
        "status" => "error",
        "error"	 => "Submissions_gap should be between contest's boundaries.",
        "errorcode" => 100
    ))); 
}

// Sanity check for scoreboard
if ( $_POST["scoreboard"] < 0 || $_POST["scoreboard"] > 100 )
{
    // In case of bad parameters, send a BAD REQUEST
    header('HTTP/1.1 400 BAD REQUEST');

    die(json_encode(array(
        "status" => "error",
        "error"	 => "Scoreboard should be an integer between 0 and 100",
        "errorcode" => 100
    )));    
}

// @TODO What are the boundaries of points_decay_factor ? 


// Sanity check of dates
if ( strtotime($_POST["start_time"]) >= strtotime($_POST["finish_time"]) )
{
    // In case of bad parameters, send a BAD REQUEST
    header('HTTP/1.1 400 BAD REQUEST');

    die(json_encode(array(
        "status" => "error",
        "error"	 => "finish_time should be later than start_time",
        "errorcode" => 100
    )));   
        
}

// Sanity check for penalty
if ( $_POST["penalty"] < 0)
{
    // In case of bad parameters, send a BAD REQUEST
    header('HTTP/1.1 400 BAD REQUEST');

    die(json_encode(array(
        "status" => "error",
        "error"	 => "Penalty should be greater than 0.",
        "errorcode" => 100
    )));     
    
}


// Fill $values array with values sent to the API
$values = array();
foreach($relevant_columns as $key => $value)
{
    $values[$key] = $_POST[$key];        
}

// Manually add special parameters
$values["director_id"] = $user_id;

// Populate a new Contests object
$contest = new Contests($values);

// Push changes
try
{
    // We are assuming that the DAO layer is responsible of data validation
    ContestsDAO::save($contest);
    
}catch(Exception $e)
{
    header('HTTP/1.1 500 INTERNAL SERVER ERROR');
    var_dump($e);
    die(json_encode(array(
        "status" => "error",
        "error"	 => "Whops. Ive encoutered an error while writing your session to the database.",
        "errorcode" => 105
    )));
    
}

// Happy ending.
die(json_encode(array(
    "status" => "ok"
)));
