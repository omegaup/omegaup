<?php

    // Set timezone to UTC
    // Set default time
    date_default_timezone_set('UTC');   

    // Load tess specific config globals
    require_once("test_config.php");
    
    //set paths
    define( 'SERVER_PATH', OMEGAUP_FRONTEND_SERVER_ROOT .'/' );    
    ini_set( 'include_path', ini_get('include_path') . PATH_SEPARATOR . SERVER_PATH );

    // Load log
    require_once(OMEGAUP_FRONTEND_SERVER_ROOT."libs/logger/Logger.php");    
    
    // Load test utils
    require_once("Utils.php");

    // Clean previous log
    Utils::CleanLog();
    
    // Clean problems and runs path    
    Utils::CleanPath(PROBLEMS_PATH);    
    Utils::CleanPath(RUNS_PATH);    
    
    // Connect to DB
    Utils::ConnectToDB();
    
    // Clean DB
    Utils::CleanupDB();         