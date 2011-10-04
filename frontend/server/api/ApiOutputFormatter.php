<?php

/*
 * This helper singleton class is responsible of print API's output given the format, handling exceptions and
 * setting proper headers
 * 
 */

require_once("ApiHandler.php");

class ApiOutputFormatter 
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
    
    
    
    private function FormatOuput(array $array)
    {
        return json_encode($array);
    }
    
    
    private function SetHeaderContentType()
    {
        // Set JSON as output
        header('Content-Type: application/json');  
    }




    public function PrintOuput(ApiHandler $api)
    {
        $this->SetHeaderContentType();
        
        try
        {
            $val = $api->ExecuteApi();
        }
        catch(ApiException $e)
        {
            // If something goes wrong, set the proper header
            $temp_arr = $e->getArrayMessage();
            header($temp_arr["header"]);

            // Ouput the error JSONized without the header info
            unset($temp_arr["header"]);
            die( $this->FormatOuput($temp_arr) );

        }

        echo $this->FormatOuput($val);
        
    }
    
}

?>
