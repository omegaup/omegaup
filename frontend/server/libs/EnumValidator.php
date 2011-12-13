<?php

/*
 *  Enum Validator
 * 
 */
require_once("Validator.php");

class EnumValidator extends Validator
{
    
    private $_enum;
    
    // Save the reference
    public function EnumValidator(array $enum_array)
    {        
        $this->_enum = $enum_array;        
    }

    
    public function validate($value)
    {
        // Validate that value is inside the options
        foreach($this->_enum as $option)
        {
            if ($value === $option)
            {                
                // Validation passed
                return true;
            }
        }
        
        // Validation failed
        $this->setError("Value not within valid set of options.");
        return false;
    }
}

?>
