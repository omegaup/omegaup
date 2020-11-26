<?php
namespace OmegaUp;
require_once(dirname(__DIR__, 3) . '/server/bootstrap.php');
$alias = \OmegaUp\DAO\Problems::getRandomLanguageProblemAlias();
header('HTTP/1.1 303 See Other');
header("Location: /arena/problem/$alias/");
