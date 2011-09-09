<?php

/*
 *  String Validator
 * 
 */
require_once("validator.php");

class StringValidator extends Validator
{
    // Reference to string
    private $str;
    
    // Save the reference
    public function StringValidator( &$string_ref )
    {
        $this->str = $string_ref;
        Validator::Validator();
    }

    
    public function validate()
    {
        // Validate that is not empty
        if ( $this->str == "")
        {
            $this->setError("String cannot be empty.");
            return false;
        }
        
        // Validation passed
        return true;
        
    }
}

?>
