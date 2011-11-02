<?php

/*
* GET /clarification/new
Si el usuario tiene permiso, envía una clarificación sobre un problema en particular. En los parámetros se envía el ID del problema. La API regresa un ID para trackearla de alguna forma.
*/


define("WHOAMI", "API");
require_once("../../../../server/inc/bootstrap.php");
require_once("../../../../server/api/NewClarification.php");
require_once("../../../server/api/ApiOutputFormatter.php");


$api = new NewClarification();
$apiOutput = ApiOutputFormatter::getInstance();
$apiOutput->PrintOuput($api);
