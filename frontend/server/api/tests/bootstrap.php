<?php

   // Set timezone to UTC
   date_default_timezone_set('UTC');

   // Loads configs
   define('SERVER_PATH', dirname(dirname(__DIR__)));     
   ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . SERVER_PATH);

   // Load config globals
   require_once(SERVER_PATH . DIRECTORY_SEPARATOR .  "config.php");

   require_once("Utils.php");

   require_once("libs/Logger/Logger.php");
   require_once('dao/model.inc.php');
   
    require_once('adodb5/adodb.inc.php');
    require_once('adodb5/adodb-exceptions.inc.php');


    // Connect to DB
    Utils::ConnectToDB();
    
    
    // Create users needed for testing    
    Utils::$contestant = Utils::CreateUser("user", "password");    
    Utils::$contestant_2 = Utils::CreateUser("user2", "password");
    Utils::$judge = Utils::CreateUser("judge", "password");
    Utils::$problem_author = Utils::CreateUser("problem_author", "password");       
    
    // Initialize time counters
    Utils::$counttime = 0;
    Utils::$inittime = Utils::GetPhpUnixTimestamp();
    
    
function initialize_db($source_db, $testing_db)
{
    $testing = ADONewConnection(OMEGAUP_DB_DRIVER);
    $testing->NConnect(OMEGAUP_DB_HOST, OMEGAUP_DB_USER, OMEGAUP_DB_PASS, $testing_db);

    $testing->BeginTrans();
    $errors = array();
    
    try
    {
        $testing->Execute("DROP DATABASE `$testing_db`;");
        $testing->Execute("CREATE DATABASE `$testing_db`;");	

        $instalation_script = file_get_contents(SERVER_PATH . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "bd.sql");
        $testing->Execute($instalation_script);

        $testing->CommitTrans();
    }
    catch(ADODB_Exception $e)
    {
        $errors[] = array('sql' => $e->sql,
                        'msg' => $e->msg);        
    }
    
    if ($errors)
    {
        var_dump($errors);
        $testing->RollbackTrans();
    }
        
    $testing->Close();    
    
    return $errors;
  }