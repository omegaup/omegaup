<?php

  $args = extract_query_arguments();
  $link = mysql_connect(
      $args["host"]
    , $args["db_admin"]
    , $args["db_admin_pass"]
  );
  if( !$link )
    die('Failed to connect to database: ' . mysql_error());
  
  // Create DB
  $db_name  = $args["db_name"];
  $stmt_create_db
    = "CREATE DATABASE IF NOT EXISTS $db_name;";
  mysql_query($stmt_create_db, $link)
    or die('Failed to create database: ' . mysql_error());
  
  // Create DB user
  $user = $args["user"];
  $pass = $args["pass"];
  $stmt_create_user
    = "CREATE USER '$user'@'localhost' IDENTIFIED BY '$pass';";

  mysql_query($stmt_create_user, $link);
  // Ignore error in case user already exists.
  
  // Grant DB permissions
  $stmt_grant
    = "GRANT ALL ON $db_name.* TO '$user'@'localhost';";
  mysql_query($stmt_grant, $link)
    or die('Failed to grant user permissions: ' . mysql_error());
  
  $stmt_flush
    = "FLUSH PRIVILEGES;";
  mysql_query($stmt_flush, $link)
    or die('Failed to reload permissions: ' . mysql_error());
    
  // Create DB structure
  $script_info = run_sql_script(
      $args["host"]
    , $user
    , $pass
    , $db_name
    , "../../private/bd.sql"
    , $args["admin_user"]
    , $args["admin_pass"]
  );
  
  $num_statements = $script_info["statements"];
  $num_errors     = $script_info["errors"];
  echo "$num_errors errors encountered while after running $num_statements statements.<br/>";

  create_config_php($args["host"], $user, $pass, $db_name);
  
  // @todo  notify success
  //        instruct user to delete the folder containing this file
  echo "Installation finished.<br/><br/>Now delete the install folder.";
  
  /* End */
  
  /**
    * Extract POST fields and sanitize them.
    * Perform content validation (e.g. valid chars int the db and user name)
    *
    * @todo Actually perform validation!
    */
  function extract_query_arguments(
  ) {
    $args = Array(
        "host"          => "localhost"
      , "db_admin"      => "root"
      , "db_admin_pass" => ""
      , "db_name"       => "omegaup"
      , "user"          => "omegaup"
      , "pass"          => "omegauppwd"
      , "admin_user"    => "admin"
      , "admin_pass"    => "admin"
    );
    foreach( $args as $key => $default ) {
      if( array_key_exists($key, $_POST) ) {
        $args[$key] = $_POST[$key];
      }
    }
    // This should look better than just dying, but then the
    // frontend .html should validate this before POSTing
    if( $args["pass"] != $_POST["pass_confirm"] )
      die("Database use passwords don't match");
    if( $args["admin_pass"] != $_POST["admin_pass_confirm"] )
      die("Passwords for admin user don't match");

    // @todo Validate chars
    // host:      [a-z0-9\-]
    // db_name:   [a-z\_]
    // user:      [a-z0-9]
    // admin_user:[a-z\.\_]
    
    return $args;
  }
  /**
    * Run a SQL script
    *
    * @param string $host DB host
    * @param string $user Username under which to run the script
    * @param string $path Path of the script to run
    */
  function run_sql_script(
      $host
    , $user
    , $pass
    , $db_name
    , $path
    , $admin_user
    , $admin_pass
  ) {
    $statements = parse_sql_script($path);    
    $link       = mysql_connect($host, $user, $pass);
    mysql_select_db($db_name, $link);
    
    $errors = 0;
    foreach( $statements as $statement ) {
      if( trim($statement) != '' && !mysql_query($statement, $link) ) {
        $errors++;
        echo "Failed query: <pre>'" . $statement . "'</pre><br/>" . mysql_error() . "<br/>";
      }
    }
    register_admin_user($link, $admin_user, $admin_pass);
    return Array(
        "statements"  => count($statements)
      , "errors"      => $errors
    );
  }
  function parse_sql_script($path) {
    $lines = file(
        $path
      , FILE_SKIP_EMPTY_LINES
    );
    $non_comments = array_filter(
        $lines
      , function( $line ) {
        return (strstr($line, "-- ") !== 0);
      }
    );
    $statements = explode(';', implode($non_comments));
    return $statements;
  }
  /**
    * Creates a config.php file with the credentials used to create
    * the database.
    */
  function create_config_php(
      $host
    , $user
    , $pass
    , $db_name
  ) {
    $native_dir     = realpath(dirname($_SERVER['SCRIPT_FILENAME']) . '/../..');
    $root_dir       = str_replace('\\', '/', $native_dir);
    $file_contents  =
"<?php
  # #####################################
  # DATABASE CONFIG
  # ####################################
  define('OMEGAUP_DB_USER',         '$user');
  define('OMEGAUP_DB_PASS',         '$pass');
  define('OMEGAUP_DB_HOST',         '$host');
  define('OMEGAUP_DB_NAME',         '$db_name');	
  define('OMEGAUP_DB_DRIVER',       'mysqlt');
  define('OMEGAUP_DB_DEBUG',        false);	
  define('OMEGAUP_ROOT',            '$root_dir');

  define('OMEGAUP_LOG_TO_FILE',     true);
  define('OMEGAUP_LOG_ACCESS_FILE', '$root_dir/log/omegaup.log');
  define('OMEGAUP_LOG_ERROR_FILE',  '$root_dir/log/omegaup.log');
  define('OMEGAUP_LOG_TRACKBACK',   false);
  define('OMEGAUP_LOG_DB_QUERYS',   true);

  ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . OMEGAUP_ROOT . '/server');
";
    
    file_put_contents("$root_dir/server/config.php", $file_contents) !== FALSE
      or die("Unable to write $root_dir/server/config.php. Make sure it is writable.");

    file_put_contents("$root_dir/log/omegaup.log", '') !== FALSE
      or die("Unable to create logfile in $root_dir/log/omegaup.log. Make sure the server has the correct permissions.");
  }

  /**
    *
    */
  function register_admin_user($link, $admin_user, $admin_pass) {
    $stmt_insert
      = "INSERT INTO Users (username, password) VALUES "
      . "('$admin_user', PASSWORD('$admin_pass'));";
    mysql_query($stmt_insert, $link);
  }
  
