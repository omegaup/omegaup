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
    public function DateRangeValidator($startDate, $finishDate)
    {        
        $this->startDate = $startDate;
        $this->finishDate = $finishDate;        
    }

    public function setLimits($startDate, $finishDate)
    {        
        $this->startDate = $startDate;
        $this->finishDate = $finishDate;        
    }
    
    public function validate($value, $value_name = null)
    {
        // Validate that is target date is inside the range
        if(is_null($value) || is_null($this->finishDate) || is_null($this->startDate))
        {
            $this->setError("Impossible to validate bounds.");
            return false;
        }
        
        // Cache time-to-int conversions
        $strValue = strtotime($value);
        $strStart = strtotime($this->startDate);
        $strFinish = strtotime($this->finishDate);
        
        // strtotime may fail..
        if($strValue === -1 || $strStart === -1 || $strFinish === -1)
        {
            $this->setError("Impossible to validate bounds.");
            return false;    
        }
        
        if (!($strValue >= $strStart && $strValue <= $strFinish))
        {
            $this->setError("Date is outside the range.");
            return false;
        }
        
        // Validation passed
        return true;        
    }
}

?>
