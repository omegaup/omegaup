<?php

/*
 * This helper singleton class is responsible of print API's output given the format, handling exceptions and
 * setting proper headers
 * 
 */

class ApiOutputFormatter 
{
               
    protected function FormatOuput($array)
    {


        //return json_encode($array);
        return $array; 
    }
    
    protected function ContentType()
    {
        return 'Content-Type: application/json';
    }
    
    protected function SetHeader($header)
    {        
        // Delegate call to PHP
        header($header);                  
    }

    public final function PrintOuput($return_array, $header = NULL)
    {
        if($header === NULL)
        {
            $this->SetHeader($this->ContentType());        
        }
        else
        {
            $this->SetHeader($header);
        }
        return self::FormatOuput($return_array);        
    }
    
}

?>
