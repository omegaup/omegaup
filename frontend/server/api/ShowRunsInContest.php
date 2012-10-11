<?php

/**
 * 
 * Please read full (and updated) documentation at: 
 * https://github.com/omegaup/omegaup/wiki/Arena 
 *
 * 
 * GET /runs/problem/:id
 * Si el usuario tiene permiso, regresa las referencias a las últimas 5 soluciones a un problema en particular que el mismo usuario ha enviado, y su estado y calificación.
 *
 * */

require_once("ApiHandler.php");
require_once(SERVER_PATH ."/libs/Authorization.php");

class ShowRunsInContest extends ApiHandler
{
    private $contest;
    private $offset = 0;
    private $rowcount = 100;

    protected function RegisterValidatorsToRequest()
    {
	$user_id = $this->_user_id;

	ValidatorFactory::stringNotEmptyValidator()->validate(RequestContext::get("contest_alias"), "contest_alias");

	try {
		$this->contest = ContestsDAO::getByAlias(RequestContext::get("contest_alias"));
	} catch(Exception $e) {
		// Operation failed in the data layer
		throw new ApiException(ApiHttpErrors::invalidDatabaseOperation(), $e);
	}

	$customValidator = new CustomValidator(
		function ($contest) use ($user_id)  {
			return Authorization::IsContestAdmin($user_id, $contest);
		},
		"Contest requested is invalid."
	);
	$customValidator->validate($this->contest, "contest_alias");
        
        // Check offset, is optional
        if (!is_null(RequestContext::get("offset")))
        {
            ValidatorFactory::numericValidator()->validate(RequestContext::get("offset"), "offset");
            $this->offset = RequestContext::get("offset");
	}
        
        // Check rowcount, is optional
        if (!is_null(RequestContext::get("rowcount")))
        {
            ValidatorFactory::numericValidator()->validate(RequestContext::get("rowcount"), "rowcount");
            $this->rowcount = RequestContext::get("rowcount");
	}
        
        // Check filter by status, is optional
        if (!is_null(RequestContext::get("status")))
        {
            ValidatorFactory::enumValidator(array("AC", "PA", "WA", "TLE", "MLE", "OLE", "RTE", "RFE", "CE", "JE"))
                ->validate(RequestContext::get("status"), "status");
        }
    }   
            
    protected function GenerateResponse() 
    {        
        $runs_mask = null;
        
        // Get all runs for problem given        
        $runs_mask = new Runs( array (                
            "contest_id" => $this->contest->getContestId(),
            "status" => RequestContext::get("status")
            ));
        
        // Filter relevant columns
        $relevant_columns = array( "run_id", "guid", "language", "status", "veredict", "runtime", "memory", "score", "contest_score", "time", "submit_delay", "Users.username", "Problems.alias" );
        
        // Get our runs
        try
        {            
            $runs = RunsDAO::search($runs_mask, "time", "DESC", $relevant_columns, $this->offset, $this->rowcount);
        }
        catch(Exception $e)
        { 
            // Operation failed in the data layer
           throw new ApiException( ApiHttpErrors::invalidDatabaseOperation(), $e );                
	}                

	$relevant_columns[11] = 'username';
	$relevant_columns[12] = 'alias';
        
	$result = array();

	foreach ($runs as $run) {
		$filtered = $run->asFilteredArray($relevant_columns);
		$filtered['time'] = strtotime($filtered['time']);
		array_push($result, $filtered);
	}

        // Add the run to the response
        $this->addResponse('runs', $result);               
        
        // All clear
        $this->addResponse("status", "ok");
    }    
}

?>
