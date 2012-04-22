<?php

   // Set timezone to UTC
   date_default_timezone_set('UTC');

   // Loads configs
   define('SERVER_PATH', dirname(dirname(__DIR__)));     
   ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . SERVER_PATH);

   // Load config globals
   require_once("test_config.php");

   require_once("Utils.php");

   require_once("libs/Logger/Logger.php");
   require_once('dao/model.inc.php');
   
    require_once('adodb5/adodb.inc.php');
    require_once('adodb5/adodb-exceptions.inc.php');

    // Clean previous log
    Utils::CleanLog();
    
    // Clean problems and runs path
    Utils::CleanProblemsPath();
    Utils::CleanRunsPath();
    
    // Connect to DB
    Utils::ConnectToDB();
    
    // Clean DB
    Utils::CleanupDB();
        
    // Create users needed for testing    
    Utils::$contestant = Utils::CreateUser("user", "password");    
    Utils::$contestant_2 = Utils::CreateUser("user2", "password");
    Utils::$judge = Utils::CreateUser("judge", "password");
    Utils::$problem_author = Utils::CreateUser("problem_author", "password");       
    
    // Initialize time counters
    Utils::$counttime = 0;
    Utils::$inittime = Utils::GetPhpUnixTimestamp();
    