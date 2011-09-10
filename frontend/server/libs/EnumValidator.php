<?php

/*
 *  Enum Validator
 * 
 */
require_once("validator.php");

class EnumValidator extends Validator
{
    // Reference to string
    private $str;
    private $enum;
    
    // Save the reference
    public function EnumValidator( &$string_ref, $enum_array )
    {
        $this->str = $string_ref;
        $this->enum = $enum_array;
        Validator::Validator();
    }

    
    public function validate()
    {
        // Validate that string is inside the options
        foreach($this->enum as $option)
        {
            if ( $this->str === $option)
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
