<?php

/* 
 *   ApiException
 * 
 *   Exception that works with arrays instead of plain strings 
 * 
 * 
 */
class ApiException extends Exception
{
   
    protected $arrayMsg;
    protected $wrappedException;
    
    function __construct(array $arrayMsg, Exception $e = NULL) 
    {
        $this->wrappedException = $e;
        $this->arrayMsg = $arrayMsg;
    }
    
    public function getArrayMessage()
    {
        return $this->arrayMsg;
    }
    
    public function getWrappedException()
    {
        return $this->wrappedException;
    }
    
}

?>
