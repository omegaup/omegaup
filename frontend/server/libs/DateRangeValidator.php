<?php

/*
 *  Date Range Validator
 * 
 */

require_once("Validator.php");

class DateRangeValidator extends Validator
{
    // Reference to string
    private $targetDate;
    private $startDate;
    private $finishDate;
    
    // Save the reference
    public function DateRangeValidator( &$targetDate, &$startDate, &$finishDate )
    {
        $this->targetDate = $targetDate;
        $this->startDate = $startDate;
        $this->finishDate = $finishDate;
        Validator::Validator();
    }

    
    public function validate()
    {
        // Validate that is target date is inside the range
        if ( ! (strtotime($this->targetDate) >= strtotime($this->startDate) && strtotime($this->targetDate) <= strtotime($this->finishDate)))
        {
            $this->setError("Date is outside the range.");
            return false;
        }
        
        // Validation passed
        return true;
        
    }
}

?>
