<?php

/*
* Si el usuario tiene permiso, la API regresa la pregunta y la respuesta asociada con la clarifiación :id.
*/


define("WHOAMI", "API");
require_once("../../../server/inc/bootstrap.php");
require_once("../../../server/api/ShowClarification.php");
require_once("../../../server/api/ApiOutputFormatter.php");


$api = new ShowClarification();
$apiOutput = ApiOutputFormatter::getInstance();
$apiOutput->PrintOuput($api);


