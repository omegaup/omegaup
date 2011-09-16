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

// Login will load bootstrap as needed
require_once("../../../server/api/Login.php");

$apiHandler = new Login();
$apiHandler->ExecuteApi();

