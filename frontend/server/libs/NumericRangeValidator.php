<?php

/*
 *  Date Range Validator
 * 
 */

require_once("validator.php");

class NumericRangeValidator extends Validator
{
    // Reference to string
    private $target;
    private $start;
    private $finish;
    
    // Save the reference
    public function NumericRangeValidator( $target, $start, $finish )
    {
        $this->target = $target;
        $this->start = $start;
        $this->finish = $finish;
        Validator::Validator();
    }

    
    public function validate()
    {
        // Validate that is target date is inside the range
        if ( !($this->target >= $this->start && $this->target <= $this->finish))
        {
            $this->setError("Value is outside the range.");
            return false;
        }
        
        return true;
        
    }
}

?>
