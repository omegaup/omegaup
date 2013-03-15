<?php

	define('IS_TEST', TRUE);

    // Set timezone to UTC  
    date_default_timezone_set('UTC');   

    // Load tess specific config globals
    require_once("test_config.php");

	define("OMEGAUP_ROOT", __DIR__ . "/../../" );

	// Load api caller
    require_once(OMEGAUP_ROOT."www/api/ApiCaller.php");
	require_once("ApiCallerMock.php");
	
    // Load test utils
	require_once("OmegaupTestCase.php");
	require_once("OmegaupUITestCase.php");
    require_once("Utils.php");
	
	// Load Factories
	require_once '../factories/ProblemsFactory.php';
	require_once '../factories/ContestsFactory.php';
	require_once '../factories/ClarificationsFactory.php';
	require_once '../factories/UserFactory.php';
	require_once '../factories/RunsFactory.php';

    // Clean previous log
    Utils::CleanLog();
    
    // Clean problems and runs path    
    Utils::CleanPath(PROBLEMS_PATH);    
    Utils::CleanPath(RUNS_PATH);            
    
    // Clean DB
    Utils::CleanupDB(); 
