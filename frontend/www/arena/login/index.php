<?php

/**
 * 
 * Please read full (and updated) documentation at: 
 * https://github.com/omegaup/omegaup/wiki/Arena 
 *
 *
 * 
 * POST /auth/login
 * Se envía user name y password, se recibe auth token
 *
 * */


define("WHOAMI", "API");
require_once("../../../server/inc/bootstrap.php");
require_once("../../../server/api/Login.php");
require_once("../../../server/api/ApiOutputFormatter.php");

$api = new Login();
$apiOutput = ApiOutputFormatter::getInstance();
$apiOutput->PrintOuput($api);






