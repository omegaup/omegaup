<?php

define('IS_TEST', TRUE);

// Set timezone to UTC  
date_default_timezone_set('UTC');

define("OMEGAUP_ROOT", __DIR__ . "/../");
// Load test specific config globals
require_once("test_config.php");
require_once("test_config.default.php");
require_once(OMEGAUP_ROOT . "server/config.default.php");

// Load api caller
require_once(OMEGAUP_ROOT . "www/api/ApiCaller.php");
require_once("controllers/ApiCallerMock.php");

// Load test utils
require_once("controllers/OmegaupTestCase.php");
require_once("common/Utils.php");

// Load Factories
require_once 'factories/ProblemsFactory.php';
require_once 'factories/ContestsFactory.php';
require_once 'factories/ClarificationsFactory.php';
require_once 'factories/UserFactory.php';
require_once 'factories/RunsFactory.php';

// Clean previous log
Utils::CleanLog();

// Clean problems and runs path    
Utils::CleanPath(PROBLEMS_PATH);
Utils::CleanPath(PROBLEMS_GIT_PATH);
Utils::CleanPath(RUNS_PATH);
Utils::CleanPath(IMAGES_PATH);

// Clean DB
Utils::CleanupDB();

// Create a test default user for manual UI operations
UserController::$sendEmailOnVerify = false;
UserFactory::createUser("test", "testtesttest");
UserController::$sendEmailOnVerify = true;
