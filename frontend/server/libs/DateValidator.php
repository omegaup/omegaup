<?php

/*
 *  Date Validator
 * 
 */

require_once("Validator.php");

class DateValidator extends Validator
{        
    
    
    public function DateValidator( )
    {
        
        Validator::Validator();
    }

    
    public function validate($target)
    {
        // Validate that we are working with a date
        // @TODO This thing allows nice strings like "next Thursday". Is there a better way to verify Dates?
        if (strtotime($target) === false)
        {
            $this->setError("Date is malformed.");
            return false;
        }
        
        return true;
        
    }
}

?>
