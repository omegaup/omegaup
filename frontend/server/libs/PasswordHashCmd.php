<?php

require_once('SecurityTools.php');
require_once('Controller.php');

$password = Controller::randomString(8);
echo "$password\n";
echo SecurityTools::hashString($password) . "\n";
