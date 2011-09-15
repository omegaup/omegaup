<?php


require_once(SERVER_PATH ."/libs/ApiExposedProperty.php");
require_once(SERVER_PATH ."/libs/ApiHttpErrors.php");

require_once(SERVER_PATH . "/libs/StringValidator.php");
require_once(SERVER_PATH . "/libs/NumericRangeValidator.php");
require_once(SERVER_PATH . "/libs/NumericValidator.php");
require_once(SERVER_PATH . "/libs/DateRangeValidator.php");
require_once(SERVER_PATH . "/libs/DateValidator.php");
require_once(SERVER_PATH . "/libs/EnumValidator.php");
require_once(SERVER_PATH . "/libs/HtmlValidator.php");
require_once(SERVER_PATH . "/libs/CustomValidator.php");


/*
 * Basic Abstraction of an API
 */

abstract class ApiHandler
{
    // Container of input parameters
    protected $request;
    
    // Containter of output parameters
    protected $response;
    
    // User who calls the API
    protected $user_id;
    
    // Holder of error dispatcher
    protected $error_dispatcher;
     
    public function __construct() 
    {        
        
        // Get an error dispatcher
        $this->error_dispatcher = ApiHttpErrors::getInstance();
    }
    
    protected function CheckAuthorization()
    {
        // @TODO Idealy we should decouple the validation in delegates or something more mantainable
        
        // Check if we have a logged user.
        if( isset($_REQUEST["auth_token"]) )
        {

            // Find out the token
            $token = AuthTokensDAO::getByPK( $_POST["auth_token"] );

            if($token !== null)
            {

                // Get the user_id from the auth token    
                $this->user_id = $token->getUserId();         

            }
            else
            {

                // We have an invalid auth token. Dying.            
                die(json_encode( $this->error_dispatcher->invalidAuthToken() ));
            }
        }
        else
        {
            
          // Login is required
          die(json_encode( $this->error_dispatcher->invalidAuthToken() ));
        }
                
    }
    
    protected function CheckPermissions()
    {
        // @TODO Check permssions here
        return true;
    }

    protected function ValidateRequest()
    {
     
        // Validate all data 
        foreach($this->request as $parameter)
        {

            if ( !$parameter->validate() )
            {
                // In case of missing or validation failed parameters, send a BAD REQUEST        
                die(json_encode( $this->error_dispatcher->invalidParameter( $parameter->getError())));   
            }
        }
    }
    
    protected abstract function ProcessRequest();
    
    protected abstract function GenerateResponse();
    
    protected abstract function SendResponse();
    
    // This function should be called 
    public function ExecuteApi()
    {
        // Check authorization
        $this->CheckAuthorization();
        $this->CheckPermissions();
        
        // Process input
        $this->ProcessRequest();       
        $this->ValidateRequest();
        
        // Send output
        $this->GenerateResponse();
        $this->SendResponse();        
    }
}

?>
