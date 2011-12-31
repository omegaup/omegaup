<?php

/*
 *  String Validator
 * 
 */
require_once("Validator.php");

class StringLengthValidator extends Validator
{    
    private $_length;
    
    public function StringLengthValidator($length)
    {
        $this->_length = $length;
    }
        
    public function validate($value)
    {
        // Validate data is string 
        if(!(is_string($value) && strlen($value) <= $this->_length))            
        {            
            $this->setError("Value is not a string or is larger than allowed. (".$this->_length.")");
            return false;
        }
                
        // Validation passed
        return true;       
    }
}

?>
