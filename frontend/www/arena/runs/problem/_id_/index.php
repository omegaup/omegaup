<?php

/**
 * 
 * Please read full (and updated) documentation at: 
 * https://github.com/omegaup/omegaup/wiki/Arena 
 *
 *
 * 
 * GET /runs/problem/:id
 * Si el usuario tiene permiso, regresa las referencias a las últimas 5 soluciones a un problema en particular que el mismo usuario ha enviado, y su estado y calificación.
 * */



// Declare that the API is using the database
define("WHOAMI", "API");
require_once("../../../../../server/inc/bootstrap.php");
require_once("../../../../../server/api/ShowProblemRuns.php");

$apiHandler = new ShowProblemRuns();
$apiHandler->ExecuteApi();

