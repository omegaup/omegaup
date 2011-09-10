<?php

/*
 *  Date Validator
 * 
 */

require_once("validator.php");

class DateValidator extends Validator
{
    // Local copies of data
    private $target;
    
    // Save the reference
    public function DateValidator( &$target )
    {
        $this->target = $target;
        
        Validator::Validator();
    }

    
    public function validate()
    {
        // Validate that we are working with a date
        // @TODO This thing allows nice strings like "next Thursday". Is there a better way to verify Dates?
        if (strtotime($this->target) === false)
        {
            $this->setError("Date is malformed.");
            return false;
        }
        
        return true;
        
    }
}

?>
