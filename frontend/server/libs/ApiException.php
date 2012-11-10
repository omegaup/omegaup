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
    
    /**
     * 
     * @return array
     */
    public function asArray(){
        return array(
            "status" => "error",
            "error" => $this->message,
            "errorcode" => $this->code,
            "header" => $this->header,
        );
    }
}

/**
 * InvalidArgumentException
 * 
 */
class InvalidParameterException extends ApiException{
    
    /**
     * 
     * @param string $message
     * @param \Exception $previous
     */ 
    function __construct($message, \Exception $previous = NULL) {
        parent::__construct($message, 'HTTP/1.1 400 BAD REQUEST', 400, $previous);
    }
}

/**
 * DuplicatedEntryInDatabaseException
 * 
 */
class DuplicatedEntryInDatabaseException extends ApiException{
    
    /**
     * 
     * @param string $message
     * @param \Exception $previous
     */ 
    function __construct($message, \Exception $previous = NULL) {
        parent::__construct($message, 'HTTP/1.1 400 BAD REQUEST', 400, $previous);
    }
}

/**
 * DuplicatedEntryInDatabaseException
 * 
 */
class InvalidDatabaseOperation extends ApiException{
    
    /**
     * 
     * @param string $message
     * @param \Exception $previous
     */ 
    function __construct(\Exception $previous = NULL) {
        parent::__construct("Oops. Ive encoutered an internal error. Please try again.", 'HTTP/1.1 400 BAD REQUEST', 400, $previous);
    }
}

?>
