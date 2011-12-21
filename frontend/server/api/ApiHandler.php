<?php


require_once('RequestContext.php');

require_once(SERVER_PATH ."/libs/ApiExposedProperty.php");
require_once(SERVER_PATH ."/libs/ApiHttpErrors.php");
require_once(SERVER_PATH ."/libs/ApiException.php");

require_once(SERVER_PATH . "/libs/ValidatorFactory.php");

/*
 * Basic Abstraction of an API
 */
abstract class ApiHandler
{        
    // Containter of output parameters
    private $_response = array();
    
    // Cache of who calls the API
    protected $_user_id;
            
    // Cache of auth token
    protected $_auth_token;                                             
    
    protected function addResponse($key, $value)
    {
        $this->_response[$key] = $value;
    }
    
    protected function addResponseArray(array $array)
    {
        foreach ($array as $key => $value)
        {
            $this->_response[$key] = $value;
        }
    }    
    
    protected function getResponse()
    {
        return $this->_response;
    }
                          
    protected function CheckAuthToken()
    {                
        // Check if we have a logged user.               
        if(!is_null(RequestContext::get("auth_token")))
        {                
            // Find out the token
            $this->_auth_token = AuthTokensDAO::getByPK(RequestContext::get("auth_token"));
            
            if($this->_auth_token !== null)
            {

                // Get the user_id from the auth token    
                $this->_user_id = $this->_auth_token->getUserId();         
              
            }
            else
            {                
                // We have an invalid auth token. Dying.            
                throw new ApiException( ApiHttpErrors::invalidAuthToken() );
            }
        }
        else
        {                
          // Login is required
          throw new ApiException( ApiHttpErrors::invalidAuthToken() );
        }                
    }        
            
    protected abstract function RegisterValidatorsToRequest();        
    protected abstract function GenerateResponse();            
    
    // This function should be called 
    public function ExecuteApi()
    {
        try
        {   
            // Check if the user needs to be logged in
            $this->CheckAuthToken();
                                               
            // Process input
            $this->RegisterValidatorsToRequest();                               

            // Generate output
            $this->GenerateResponse();

            // If the request didn't fail nor output something, we're OK
            if(count($this->getResponse()) === 0)
            {
                $this->addResponse("status", "ok");
            }
            
            return $this->getResponse();       
        }
        catch (ApiException $e)
        {
            // Something bad happened, log error
            Logger::error( "ApiException thrown: " );
            Logger::error( $this->_user_id );
            Logger::error( $_REQUEST );
            Logger::error( $e->getFile() );
            Logger::error( $e->getArrayMessage() );
            Logger::error( $e->getTraceAsString() );
                        
            // Propagate the exception
            throw $e;
        }
        catch(Exception $e)
        {
            // Something VERY bad happened, log error
            Logger::error( "FATAL ERROR: Unwrapped exception thrown, wrapping: " );
            Logger::error( $this->_user_id );
            Logger::error( $_REQUEST );
            Logger::error( $e->getMessage() );
            Logger::error( $e->getFile() );
            Logger::error( $e->getCode() );
            Logger::error( $e->getTraceAsString() );
            Logger::error( $e->getPrevious() );
            
            throw new ApiException( ApiHttpErrors::unwrappedException() );
        }
    }
}

?>
