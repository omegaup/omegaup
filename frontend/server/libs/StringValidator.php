<?php

/*
 *  String Validator
 * 
 */
require_once("Validator.php");

class StringValidator extends Validator
{
        
    // Save the reference
    public function StringValidator( )
    {        
        Validator::Validator();
    }

    
    public function validate($value)
    {
        // Validate that is not empty
        if ( $value === "")
        {
            $this->setError("String cannot be empty.");
            return false;
        }
        
        // Validation passed
        return true;
        
    }
}

?>
