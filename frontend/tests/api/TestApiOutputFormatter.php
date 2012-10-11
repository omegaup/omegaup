<?php

/*
 * This helper singleton class is responsible of print API's output given the format, handling exceptions and
 * setting proper headers
 * 
 */
require_once '../ApiOutputFormatter.php';

class TestApiOutputFormatter extends ApiOutputFormatter
{
    protected function SetHeader($header)
    {        
        //echo 'Setting header to: '. $header ;                  
    }    
}

?>
