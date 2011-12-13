<?php

/*
 *  Numeric Validator
 * 
 */

require_once("Validator.php");

class CustomValidator extends Validator
{
    
    private $callback;
    private $message;
    
    // Save the reference to the callback
    public function CustomValidator($callback, $message)
    {        
        $this->callback = $callback;                
        $this->message = $message;
    }

    
    public function validate($data)
    {
        // Fix for bug in PHP 5.3. If we call $this->func(), PHP looks for func() inside the object
        $local_callback = $this->callback;
        
        // Delegate validation to callback
        if (!$local_callback($data))
        {
            $this->setError($this->message);
            return false;
        }
        
        return true;        
    }
}

?>
