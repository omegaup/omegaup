<?php

/**
 * 
 * Please read full (and updated) documentation at: 
 * https://github.com/omegaup/omegaup/wiki/Arena 
 *
 *
 * 
 * POST /auth/logout
 * Se envía auth token para terminar la sesión.
 *
 * */

/**
 * Ok, we are ready to roll. Bootstrap.
 * */
define("WHOAMI", "API");

require_once("../../../server/inc/bootstrap.php");
require_once("../../../server/api/logout.php");


$apiHandler = new Logout();
echo $apiHandler->ExecuteApi();

