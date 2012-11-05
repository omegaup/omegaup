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
    
    function __construct( $arrayMsg, Exception $e = NULL) 
    {
        $this->wrappedException = $e;
        $this->arrayMsg = $arrayMsg;
    }
    
    public function getApiMessage( )
    {
        return $this->arrayMsg;
    }
    
    public function getWrappedException( )
    {
        return $this->wrappedException;
    }
    
}

?>
