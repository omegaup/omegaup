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
require_once("../../../../../server/api/NewProblemInContest.php");

$apiHandler = new NewProblemInContest();
$apiHandler->ExecuteApi();


