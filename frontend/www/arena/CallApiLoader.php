<?php 

define("WHOAMI", "API");
require_once("../../server/inc/bootstrap.php");

require_once("../../server/api/ApiLoader.php");
echo ApiLoader::load();

?>