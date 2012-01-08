<?php

/*
 *  String Validator
 * 
 */
require_once("Validator.php");

class StringValidator extends Validator
{    
    public function validate($value, $value_name = null)
    {
        // Validate data is string        
        if(!is_string($value))            
        {            
            $this->setError("Value is not a string.");
            return false;
        }
                
        // Validation passed
        return true;       
    }
}

?>
