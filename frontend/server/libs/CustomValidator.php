<?php

/*
 *  Numeric Validator
 * 
 */

require_once("validator.php");

class CustomValidator extends Validator
{
    // Local copies of numeric data
    private $target;
    private $callback;
    
    // Save the reference
    public function CustomValidator( $target, $callback )
    {
        $this->target = $target;
        $this->callback = $callback;
        
        Validator::Validator();
    }

    
    public function validate()
    {
        // Fix for bug in PHP 5.3. If we call $this->func(), PHP looks for func() inside the object
        $local_callback = $this->callback;
        
        // Delegate validation to callback
        if ( !$local_callback($this->target))
        {
            $this->setError("Validation failed.");
            return false;
        }
        
        return true;
        
    }
}

?>
