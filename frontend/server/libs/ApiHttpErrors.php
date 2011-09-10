<?php

/*
 *  Singleton that abstracts HTTP errors
 * 
 */

class ApiHttpErrors
{
    private static $instance;    
    
    // Hide constructor from public
    private function __construct()
    {
        
    }

    public function __clone()
    {
        trigger_error('Clone is not allowed.', E_USER_ERROR);
    }
    
    public function __wakeup() 
    { 
        trigger_error("Wakeup is not allowed.", E_USER_ERROR); 
    } 
    
    
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
                        
            self::$instance = new self;
        }
        return self::$instance;
    }
    
    // Sets the HTTP header and returns an array with error info
    public function invalidAuthToken($message = NULL)
    {
        // We have an invalid auth token. Dying.
        header('HTTP/1.1 401 FORBIDDEN');
        
        if ($message === NULL)
        {
            $message = "You supplied an invalid auth token, or maybe it expired.";
        }
        
        return array("status" => "error",
                     "error"	 => $message,
                     "errorcode" => 500 );
    }

    // Sets the HTTP header and returns an array with error info
    public function invalidParameter($message = NULL)
    {
        // We have an invalid auth token. Dying.
        header('HTTP/1.1 400 BAD REQUEST');
        
        if ($message === NULL)
        {
            $message = "You supplied an invalid value for a parameter.";
        }
        
        return array("status" => "error",
                     "error"	 => $message,
                     "errorcode" => 100 );
    }
    
    // Sets the HTTP header and returns an array with error info
    public function invalidDatabaseOperation($message = NULL)
    {
        // We have an invalid auth token. Dying.
        header('HTTP/1.1 500 INTERNAL SERVER ERROR');
        
        if ($message === NULL)
        {
            $message = "Whops. Ive encoutered an error while writing your session to the database.";
        }
        
        return array("status" => "error",
                     "error"	 => $message,
                     "errorcode" => 105 );
    }    
    
}

?>