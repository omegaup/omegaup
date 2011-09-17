<?php

/*
 *  Enum Validator
 * 
 */
require_once("Validator.php");

class EnumValidator extends Validator
{
    
    private $enum;
    
    // Save the reference
    public function EnumValidator($enum_array )
    {        
        $this->enum = $enum_array;
        Validator::Validator();
    }

    
    public function validate($value)
    {
        // Validate that string is inside the options
        foreach($this->enum as $option)
        {
            if ( $value === $option)
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
