<?php

/**
 * 
 * Please read full (and updated) documentation at: 
 * https://github.com/omegaup/omegaup/wiki/Arena 
 *
 *
 * 
 * GET /runs/:id
 * Si el usuario tiene permiso, puede ver su solución y el estado de la misma (pending… grading… done… y la calificación).
 * */



// Declare that the API is using the database
define("WHOAMI", "API");
require_once("../../../../server/inc/bootstrap.php");
require_once("../../../../server/api/ShowRun.php");

$apiHandler = new ShowRun();
$apiHandler->ExecuteApi();

