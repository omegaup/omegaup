<?php

    // Set timezone to UTC
    // Set default time
    date_default_timezone_set('UTC');   

    // Load tess specific config globals
    require_once("test_config.php");

    define( "OMEGAUP_FRONTEND_SERVER_ROOT", "C:\\xampp\\htdocs\\omegaup\\omegaup\\frontend\\server" );

    //set paths
    define( 'SERVER_PATH', OMEGAUP_FRONTEND_SERVER_ROOT .'/' );    
    ini_set( 'include_path', ini_get('include_path') . PATH_SEPARATOR . SERVER_PATH );

    // Load log
    require_once(OMEGAUP_FRONTEND_SERVER_ROOT."/libs/logger/Logger.php");    
    
    // Load test utils
    require_once("Utils.php");

    // Clean previous log
    Utils::CleanLog();
    
    // Clean problems and runs path
    Utils::CleanProblemsPath();
    Utils::CleanRunsPath();
    
    // Connect to DB
    Utils::ConnectToDB();
    
    // Clean DB
    Utils::CleanupDB();
     
    /* @todo activar cuando sea necesario
    // Create users needed for testing    
    Utils::$contestant = Utils::CreateUser("user", "password");    
    Utils::$contestant_2 = Utils::CreateUser("user2", "password");
    Utils::$judge = Utils::CreateUser("judge", "password");
    Utils::$problem_author = Utils::CreateUser("problem_author", "password");       
    
    // Create an admin
    Utils::$admin = Utils::CreateUser("admin", "password");
    
    $ur = new UserRoles();
    $ur->setRoleId("1"); //admin
    $ur->setUserId(Utils::$admin->getUserId());
    UserRolesDAO::save($ur);
    
    // Initialize time counters
    Utils::$counttime = 0;
    Utils::$inittime = Utils::GetPhpUnixTimestamp();
    */
