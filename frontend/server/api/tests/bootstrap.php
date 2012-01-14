<?php

   // Set timezone to UTC
   date_default_timezone_set('UTC');

   // Loads configs
   define('SERVER_PATH', dirname(dirname(__DIR__)));     
   ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . SERVER_PATH);

   // Load config globals
   require_once("config.php");
   require_once("Utils.php");

   require_once("libs/Logger/Logger.php");
   require_once('dao/model.inc.php');
   
    require_once('adodb5/adodb.inc.php');
    require_once('adodb5/adodb-exceptions.inc.php');


    $conn = null;

    try
    {
        $conn = ADONewConnection(OMEGAUP_DB_DRIVER);                    
        $conn->debug = OMEGAUP_DB_DEBUG;
        $conn->PConnect(OMEGAUP_DB_HOST, OMEGAUP_DB_USER, OMEGAUP_DB_PASS, OMEGAUP_TEST_DB_NAME);

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
        
        if( !initialize_db(OMEGAUP_DB_SOURCE, OMEGAUP_TEST_DB_NAME) )
        {
          die(json_encode(array(
            "status" => "error",
            "error" => "Failed to initialize the testing database from the source databse",
            "errorcode" => 3
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
    
  function initialize_db($source_db, $testing_db){

    $testing = ADONewConnection(OMEGAUP_DB_DRIVER);
    $testing->NConnect(OMEGAUP_DB_HOST, OMEGAUP_DB_USER, OMEGAUP_DB_PASS, $testing_db);

    $source = ADONewConnection(OMEGAUP_DB_DRIVER);
    $source->NConnect(OMEGAUP_DB_HOST, OMEGAUP_DB_USER, OMEGAUP_DB_PASS, $source_db);

    $tables_recordset = $source->Execute("SHOW TABLES;");
    $tables           = $tables_recordset->GetArray();

    $testing->BeginTrans();
    $ok = true;
    foreach( $tables as $table_name )
    {
        $table_name   = $table_name[0];
        $ok           = $ok && $testing->Execute("DROP TABLE IF EXISTS $table_name CASCADE");
    }

    if( !$ok )
    {
      $testing->RollbackTrans();

      $testing->Close();
      $source->Close();

      return false;
    }
    
    foreach( $tables as $table_name )
    {
        $table_name   = $table_name[0];
        $source_name  = $source_db.".".$table_name;
        $create       = $testing->Execute("CREATE TABLE $table_name LIKE $source_name");
        if(!$create) {
            $error    = true;
        }
        $insert       = $testing->Execute("INSERT INTO $table_name SELECT * FROM $source_name");
    }

    $testing->CommitTrans();
    
    $testing->Close();
    $source->Close();
    
    return !isset($error) ? true : false;
  }