<?php

/*
 *  Numeric Validator
 * 
 */

require_once("Validator.php");

class NumericValidator extends Validator
{
    // Local copies of numeric data
    private $target;
    
    // Save the reference
    public function NumericValidator( $target )
    {
        $this->target = $target;
        
        Validator::Validator();
    }

    
    public function validate()
    {
        // Validate that we are working with a number
        if (!is_numeric($this->target))
        {
            $this->setError("Value is supposed to be numeric.");
            return false;
        }
        
        return true;
        
    }
}

?>
