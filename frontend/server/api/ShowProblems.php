<?php

/**
 * 
 * Please read full (and updated) documentation at: 
 * https://github.com/omegaup/omegaup/wiki/Arena 
 *
 *
 * 
 * POST /contests/:id:/problem/new
 * Si el usuario tiene permisos de juez o admin, crea un nuevo problema para el concurso :id
 *
 * */

require_once("ApiHandler.php");
require_once(SERVER_PATH . '/libs/FileHandler.php');

class ShowProblems extends ApiHandler
{    
    protected function CheckAuthToken()
    {                
    }
	
    protected function RegisterValidatorsToRequest()
    {
    }            
    

    protected function GenerateResponse() 
    {
        // Create array of relevant columns
        $relevant_columns = array("title", "alias", "validator", "creation_date", "source", "order");

        // Get our problem given the problem_id         
        try
        {            
            $problems = ProblemsDAO::searchByAlias(RequestContext::get('search'));
        }
        catch(Exception $e)
        {
            // Operation failed in the data layer
           throw new ApiException( ApiHttpErrors::invalidDatabaseOperation(), $e );        
	}

	$response = array();

	foreach ($problems as $problem) {
		array_push($response, $problem->asFilteredArray($relevant_columns));
	}
        
        // Add the problem the response
        $this->addResponse('problems', $response);   
    }    
}

?>
