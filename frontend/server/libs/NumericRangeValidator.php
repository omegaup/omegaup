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
        Validator::Validator();
    }

    
    public function validate($target)
    {
        // Validate that is target number is inside the range
        if ( !($target >= $this->start && $target <= $this->finish))
        {
            $this->setError("Value is outside the range.");
            return false;
        }
        
        return true;
        
    }
}

?>
