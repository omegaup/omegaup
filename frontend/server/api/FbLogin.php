<?php

/**
 * 
 * 
 * 
 *
 *
 * 
  *
 * */
require_once("ApiHandler.php");

class FbLogin extends ApiHandler {


    protected function CheckAuthToken() 
    {       
        // Bypass authorization
        return true;
    }

    protected function RegisterValidatorsToRequest() 
    {                                
        ValidatorFactory::stringNotEmptyValidator()->validate(
                RequestContext::get("username"), "username");
        
        ValidatorFactory::stringNotEmptyValidator()->validate(
                RequestContext::get("password"), "password");
    }

    protected function GenerateResponse() 
    {        
      
    }
}
