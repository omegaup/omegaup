<?php

/*
 *  Numeric Validator
 * 
 */

require_once("Validator.php");

class CustomValidator extends Validator
{
    
    private $callback;
    
    // Save the reference
    public function CustomValidator($callback )
    {        
        $this->callback = $callback;
        
        Validator::Validator();
    }

    
    public function validate($value)
    {
        // Fix for bug in PHP 5.3. If we call $this->func(), PHP looks for func() inside the object
        $local_callback = $this->callback;
        
        // Delegate validation to callback
        if ( !$local_callback($value))
        {
            $this->setError("Validation failed.");
            return false;
        }
        
        return true;
        
    }
}

?>
