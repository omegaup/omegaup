<?php

/** 
 *   ApiException
 * 
 *   Exception that works with arrays instead of plain strings 
 * 
 * 
 */
class ApiException extends Exception{
     
    protected $header;
        
    /**
     * Builds an api exception
     * 
     * @param string $message
     * @param string $header
     * @param string $code
     * @param Exception $previous
     */    
    function __construct($message, $header, $code, Exception $previous = NULL){
        parent::__construct($message, $code, $previous);
        
        $this->header = $header;
    }
    
    /**
     * Returns header
     * 
     * @return string
     */
    public function getHeader()
    {
        return $this->header;
    }           
}

/**
 * InvalidArgumentException
 * 
 */
class InvalidParameterException extends ApiException{
    
    /**
     * Builds an api exception
     * 
     * @param string $message
     * @param string $header
     * @param string $code
     * @param Exception $previous
     */ 
    function __construct($message, $header = 'HTTP/1.1 400 BAD REQUEST', $code = 400, \Exception $previous = NULL) {
        parent::__construct($message, $header, $code, $previous);
    }
}

?>
