<?php

/*
 *  String Validator
 * 
 */
require_once("Validator.php");

class NotEmptyValidator extends Validator
{    
    public function validate($value, $value_name = null)
    {
        // Validate data not empty
        if($value === "")
        {
            $this->setError("Value is empty.");
            return false;
        }
                
        // Validation passed
        return true;       
    }
}

?>
