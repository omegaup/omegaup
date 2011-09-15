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
require_once("../../../../server/api/NewContest.php");

$apiHandler = new NewContest();
$apiHandler->ExecuteApi();


