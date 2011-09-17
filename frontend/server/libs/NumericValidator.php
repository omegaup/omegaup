<?php

/*
 *  Numeric Validator
 * 
 */

require_once("Validator.php");

class NumericValidator extends Validator
{
        
    // Save the reference
    public function NumericValidator( )
    {        
        
        Validator::Validator();
    }

    
    public function validate($target)
    {
        // Validate that we are working with a number
        if (!is_numeric($target))
        {
            $this->setError("Value is supposed to be numeric.");
            return false;
        }
        
        return true;
        
    }
}

?>
