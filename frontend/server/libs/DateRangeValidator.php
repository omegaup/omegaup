<?php

/*
 *  Date Range Validator
 * 
 */

require_once("Validator.php");

class DateRangeValidator extends Validator
{
    
    private $startDate;
    private $finishDate;
    
    // Save the reference
    public function DateRangeValidator( $startDate, $finishDate )
    {
        
        $this->startDate = $startDate;
        $this->finishDate = $finishDate;
        Validator::Validator();
    }

    
    public function validate($targetDate)
    {
        // Validate that is target date is inside the range
        if ( ! (strtotime($targetDate) >= strtotime($this->startDate) && strtotime($targetDate) <= strtotime($this->finishDate)))
        {
            $this->setError("Date is outside the range.");
            return false;
        }
        
        // Validation passed
        return true;
        
    }
}

?>
