<?php

/**
 * 
 * Please read full (and updated) documentation at: 
 * https://github.com/omegaup/omegaup/wiki/Arena 
 *
 *
 * 
 * POST /runs/new
 * Si el usuario tiene permiso, El usuario envía una solución. En los parámetros se envía el ID del problema y del concurso.
 *
 * */



// Declare that the API is using the database
define("WHOAMI", "API");
require_once("../../../../server/inc/bootstrap.php");
require_once("../../../../server/api/NewRun.php");

$apiHandler = new NewRun();
$apiHandler->ExecuteApi();

