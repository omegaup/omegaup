<?php

/*
 *  Numeric Range Validator
 * 
 */

require_once("Validator.php");

class NumericRangeValidator extends Validator
{
    // Local copies of numeric data
    private $start;
    private $finish;
    
    // Save the reference
    public function NumericRangeValidator( $start, $finish )
    {        
        $this->start = $start;
        $this->finish = $finish;        
    }

    public function setLimits( $start, $finish )
    {        
        $this->start = $start;
        $this->finish = $finish;        
    }
    
    public function validate($value, $value_name = null)
    {
        // Validate that is target number is inside the range
        if ( !($value >= $this->start && $value <= $this->finish))
        {
            $this->setError("Value is outside the range.");
            return false;
        }
        
        return true;        
    }
}

?>
