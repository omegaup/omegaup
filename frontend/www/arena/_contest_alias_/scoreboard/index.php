<?php

/**
 * 
 * Please read full (and updated) documentation at: 
 * https://github.com/omegaup/omegaup/wiki/Arena 
 *
 *
 * 
 * GET /contests/:id/ranking/
 * Si el usuario puede verlo, Muestra el ranking completo del contest ID.
 *
 * */

define("WHOAMI", "API");
require_once("../../../../server/inc/bootstrap.php");
require_once("../../../../server/api/ShowScoreboard.php");


$apiHandler = new ShowScoreboard();
$apiHandler->ExecuteApi();