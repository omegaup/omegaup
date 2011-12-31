<?php

/*
 *  Date Validator
 * 
 */

require_once("Validator.php");

class DateValidator extends Validator
{                  
    public function validate($value)
    {
        // Validate that we are working with a date
        // @TODO This strtotime() allows nice strings like "next Thursday". Is there a better way to verify Dates?
        if (strtotime($value) === -1)
        {
            $this->setError("Date is malformed.");
            return false;
        }
        
        return true;        
    }
}

?>
