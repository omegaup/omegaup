<?php

   // Loads configs
   define('SERVER_PATH', dirname(dirname(__DIR__)));     
   define('RUNS_PATH', SERVER_PATH ."/../runs/");
   define('PROBLEMS_PATH', SERVER_PATH ."/../problems/");   
   ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . SERVER_PATH);

   // Load config globals
   require_once("config.php");
   require_once("Utils.php");

   // Define POST and GET constants for simplicity
   define('POST', "__ISPOST__");
   define('GET', "__ISGET__");
   

   // Cache of roles_id-s
   define('ADMIN', '1');
   define('CONTESTANT', '2');
   define('JUDGE', '3');
   define('VISITOR', '4');
   define('BYPASS', '-1');
   

   require_once("libs/Logger/Logger.php");
   require_once('dao/model.inc.php');
   
    require_once('adodb5/adodb.inc.php');
    require_once('adodb5/adodb-exceptions.inc.php');


    $conn = null;

    try
    {
        $conn = ADONewConnection(OMEGAUP_DB_DRIVER);                    
        $conn->debug = OMEGAUP_DB_DEBUG;
        $conn->PConnect(OMEGAUP_DB_HOST, OMEGAUP_DB_USER, OMEGAUP_DB_PASS, OMEGAUP_DB_NAME);

        if(!$conn) 
        {
                    /**
                     * Dispatch missing parameters
                     * */
                    header('HTTP/1.1 500 INTERNAL SERVER ERROR');

                    die(json_encode(array(
                            "status" => "error",
                            "error"	 => "Conection to the database has failed.",
                            "errorcode" => 1
                    )));

        }

    } 
    catch (Exception $e) {

            header('HTTP/1.1 500 INTERNAL SERVER ERROR');

            die(json_encode(array(
                    "status" => "error",
                    "error"	 => $e,
                    "errorcode" => 2
            )));

    }
    $GLOBALS["conn"] = $conn;
    
    
    // Create users needed for testing    
    Utils::$contestant = Utils::CreateUser("user", "password");    
    Utils::$contestant_2 = Utils::CreateUser("user2", "password");
    Utils::$judge = Utils::CreateUser("judge", "password");
    Utils::$problem_author = Utils::CreateUser("problem_author", "password");       
    
    // Initialize time counters
    Utils::$counttime = 0;
    Utils::$inittime = Utils::GetPhpUnixTimestamp();
    