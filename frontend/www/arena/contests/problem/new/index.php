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



// Declare that the API is using the database
define("WHOAMI", "API");
require_once("../../../../../server/inc/bootstrap.php");

// @TODO Reduce all these includes
require_once("../../../../../server/libs/ApiExposedProperty.php");

require_once("../../../../../server/libs/StringValidator.php");
require_once("../../../../../server/libs/NumericRangeValidator.php");
require_once("../../../../../server/libs/NumericValidator.php");
require_once("../../../../../server/libs/DateRangeValidator.php");
require_once("../../../../../server/libs/DateValidator.php");
require_once("../../../../../server/libs/EnumValidator.php");
require_once("../../../../../server/libs/HtmlValidator.php");

require_once("../../../../../server/libs/ApiHttpErrors.php");


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
    new ApiExposedProperty("contest_id", true, $_GET["contest_id"], array(
        new NumericValidator($_GET["contest_id"])
    )),
    
    new ApiExposedProperty("public", false, FALSE), // All problems created through this API will be private at their creation 
    
    new ApiExposedProperty("author_id", true, $user_id),
    
    new ApiExposedProperty("title", true, $_POST["title"], array(
        new StringValidator($_POST["title"]))),
    
    new ApiExposedProperty("alias", false, $_POST["alias"]),
    
    new ApiExposedProperty("validator", true, $_POST["validator"], array(
        new EnumValidator($_POST["validator"], array("remote", "literal", "token", "token-caseless", "token-numeric"))
    )),
    
    new ApiExposedProperty("time_limit", true, $_POST["time_limit"], array(
        new NumericValidator($_POST["time_limit"]),
        new NumericRangeValidator($_POST["time_limit"], 0, INF)
    )),
    
    new ApiExposedProperty("source", true, $_POST["source"], array(
        new HtmlValidator($_POST["source"])
    )), // 
    
    new ApiExposedProperty("order", true, $_POST["order"], array(
        new EnumValidator($_POST["order"], array("normal", "inverse"))
    ))    
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

// Create file for problem content
// @TODO clean the path
$filename = md5($_POST["title"]);
$fileHandle = fopen("../../../../../problems/".$filename, 'w') or die(json_encode( $error_dispatcher->invalidDatabaseOperation() ));    
fclose($fileHandle);



// Fill $values array with values sent to the API
$problems_insert_values = array();
foreach($parameters as $parameter)
{
    // Update source to path 
    if ($parameters->getPropertyName() === "source")
    {
        $parameters->setPropertyName($filename);
    }
    
    else if ($parameters->getPropertyName() !== "contest_id") // Contest_id doesn't go to problems table
    {
        $problems_insert_values[$parameter->getPropertyName()] = $parameter->getValue();        
    }
}


// Populate a new Contests object
$problem = new Problems($problems_insert_values);

// Insert new problem
try
{
    // Save the contest object with data sent by user to the database
    ProblemsDAO::save($problem);
    
    // Save relationship between problems and contest_id
    $relationship = new ContestProblems( array(
        "contest_id" => $_GET["contest_id"],
        "problem_id" => $problem->getProblemId() ));
    ContestProblemsDAO::save($relationship);
    
}catch(Exception $e)
{  
    // Operation failed in the data layer
    die(json_encode( $error_dispatcher->invalidDatabaseOperation() ));    
}




