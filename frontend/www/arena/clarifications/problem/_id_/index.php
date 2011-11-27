<?php

/*
* GET /clarifications/problem/:id
Regresa TODAS las clarificaciones de un problema en particular, a las cuales el usuario puede ver (equivale a las que el personalmente mandó más todas las clarificaciones del problema marcadas como globales)
 */


define("WHOAMI", "API");
require_once("../../../../../server/inc/bootstrap.php");
require_once("../../../../../server/api/ShowClarificationsInProblem.php");
require_once("../../../server/api/ApiOutputFormatter.php");


$api = new ShowClarificationsInProblem();
$apiOutput = ApiOutputFormatter::getInstance();
$apiOutput->PrintOuput($api);


