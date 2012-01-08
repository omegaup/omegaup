<?php

/*
 *  Numeric Validator
 * 
 */

require_once("Validator.php");

class NumericValidator extends Validator
{
    
    public function validate($value, $value_name = null)
    {
        // Validate that we are working with a number
        if (!is_numeric($value))
        {
            $this->setError("Value should be numeric.");
            return false;
        }
        
        return true;        
    }
}

?>
