<?php

require_once("ApiHandler.php");
require_once(SERVER_PATH . '/libs/FileHandler.php');

class ShowRunSource extends ApiHandler
{     
	private $run;

	protected function RegisterValidatorsToRequest()
	{    
		ValidatorFactory::stringNotEmptyValidator()->addValidator(
			new CustomValidator(function ($value)
			{
				// Check if the contest exists
				return RunsDAO::getByAlias($value);
			}, "Run is invalid.")
		)->validate(RequestContext::get("run_alias"), "run_alias");
	    
		try
		{                        
			$this->run = RunsDAO::getByAlias(RequestContext::get("run_alias"));
	    
		}
		catch(Exception $e)
		{
			// Operation failed in the data layer
			throw new ApiException( ApiHttpErrors::invalidDatabaseOperation(), $e);        
		}                        

                if (!Authorization::IsSystemAdmin($this->_user_id) || ($this->run->getUserId() != $this->_user_id))
		{
                    throw new ApiException(ApiHttpErrors::forbiddenSite());
		}                                
	}


	protected function GenerateResponse() 
	{		
            $this->addResponse('source', file_get_contents(RUNS_PATH . '/' . $this->run->getGuid()));
	}
	
}
