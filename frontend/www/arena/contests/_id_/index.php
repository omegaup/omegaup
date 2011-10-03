<?php

/*
GET /contests/:id/
Si el usuario puede verlos, muestra los detalles del concurso :id ( info mínima de los problemas, tiempo restante, mini-ranking… un query sencillito, carismático y cacheable).
*/

define("WHOAMI", "API");
require_once("../../../../server/inc/bootstrap.php");
require_once("../../../../server/api/ShowContest.php");


$apiHandler = new ShowContest();
echo $apiHandler->ExecuteApi();
