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
    
    function __construct($arrayMsg = NULL) 
    {
        $this->arrayMsg = $arrayMsg;
    }
    
    public function getArrayMessage()
    {
        return $this->arrayMsg;
    }
    
}

?>
