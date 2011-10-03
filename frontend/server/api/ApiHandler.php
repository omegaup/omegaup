<?php


require_once(SERVER_PATH ."/libs/ApiExposedProperty.php");
require_once(SERVER_PATH ."/libs/ApiHttpErrors.php");
require_once(SERVER_PATH ."/libs/ApiException.php");

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
    
    // Cache of who calls the API
    protected $user_id;
    
    // Holder of error dispatcher
    protected $error_dispatcher;
    
    // Cache of auth token
    protected $auth_token;
    
    // Cache of user roles
    protected $user_roles;
    
    // Holder of roles of each api
    protected $api_roles;
     
    public function __construct() 
    {        
        
        // Get an error dispatcher
        $this->error_dispatcher = ApiHttpErrors::getInstance();
        
        // Declare response as an array
        $this->response = array();
                
                
    }
           
    private function ApiEncode($message)
    {
        return json_encode($message);
    }
    
    protected function CheckAuthorization()
    {
        // @TODO Idealy we should decouple the validation in delegates or something more mantainable
        
        // Check if we have a logged user.
        if( isset($_REQUEST["auth_token"]) )
        {
                
            // Find out the token
            $this->auth_token = AuthTokensDAO::getByPK( $_POST["auth_token"] );
            
            if($this->auth_token !== null)
            {

                // Get the user_id from the auth token    
                $this->user_id = $this->auth_token->getUserId();         
                                
                // Get roles of user
                // @todo Cache this!
                $this->user_roles = UserRolesDAO::search(new UserRoles( array (
                    "user_id" => $this->user_id    
                    )));
                

            }
            else
            {

                // We have an invalid auth token. Dying.            
                throw new ApiException( $this->error_dispatcher->invalidAuthToken() );
            }
        }
        else
        {
      
          // Login is required
          throw new ApiException( $this->error_dispatcher->invalidAuthToken() );
        }
                
    }
    
    protected function CheckPermissions()
    {                
        
        if ($this->api_roles === BYPASS)
            return true;
        
        foreach($this->user_roles as $rol)
        {
            // By default, admin rocks
            if($rol->getRoleId() === ADMIN)
            {
                return true;
            }
                                    
            foreach ($this->api_roles as $allowedRol)
            {                                
                if($allowedRol === $rol->getRoleId())
                {
                    return true;
                }
            }
        }
        
        // Rol was not found
        throw new ApiException($this->error_dispatcher->forbiddenSite());
    }


    protected function ValidateRequest()
    {
     
        // If we didn't get any request, asume everything is OK.
        if(is_null($this->request)) return;
        
        // Validate all data 
        foreach($this->request as $parameter)
        {

            if ( !$parameter->validate() )
            {
                // In case of missing or validation failed parameters, send a BAD REQUEST        
                throw new ApiException( $this->error_dispatcher->invalidParameter( $parameter->getError()) );   
            }
        }
    }
    
    protected abstract function DeclareAllowedRoles();    
    
    protected abstract function GetRequest();
    
    protected abstract function GenerateResponse();
        
    
    // This function should be called 
    public function ExecuteApi()
    {
        try
        {
            // Set JSON as output
            header('Content-Type: application/json');   
                        
            $this->CheckAuthorization();
            
            // Each API should declare its allowed roles            
            $this->api_roles = $this->DeclareAllowedRoles();
            
            $this->CheckPermissions();
                        
            // Process input
            $this->GetRequest();       
            $this->ValidateRequest();

            // Generate output
            $this->GenerateResponse();

            return $this->ApiEncode($this->response);       
        }
        catch (ApiException $e)
        {
            return $this->ApiEncode($e->getArrayMessage());
        }
        
    }
}

?>
