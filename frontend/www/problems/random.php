<?php
namespace OmegaUp;
require_once(dirname(__DIR__, 2) . '/server/bootstrap.php');
$alias = \OmegaUp\DAO\Problems::getRandomProblemAlias();
header('HTTP/1.1 303 See Other');
header("Location: /arena/problem/$alias/");
