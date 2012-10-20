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
    private $problem;
    private $offset = 0;
    private $rowcount = 100;

    protected function RegisterValidatorsToRequest()
    {
	$user_id = $this->_user_id;
	
	ValidatorFactory::stringNotEmptyValidator()->validate(RequestContext::get("contest_alias"), "contest_alias");

	try 
        {
            $this->contest = ContestsDAO::getByAlias(RequestContext::get("contest_alias"));
	} 
        catch(Exception $e) 
        {
            // Operation failed in the data layer
            throw new ApiException(ApiHttpErrors::invalidDatabaseOperation(), $e);
	}
        
        if (is_null($this->contest))
        {
            throw new ApiException(ApiHttpErrors::notFound("Contest selected not found."));
        }

	if(!Authorization::IsContestAdmin($this->_user_id, $this->contest))
        {
            throw new ApiException(ApiHttpErrors::forbiddenSite());
        }       
 
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
            ValidatorFactory::enumValidator(array('new','waiting','compiling','running','ready'))
                ->validate(RequestContext::get("status"), "status");
        }
        
        // Check filter by veredict, is optional
        if (!is_null(RequestContext::get("veredict")))
        {
            ValidatorFactory::enumValidator(array("AC", "PA", "WA", "TLE", "MLE", "OLE", "RTE", "RFE", "CE", "JE", "NO-AC"))
                ->validate(RequestContext::get("veredict"), "veredict");
        }        
        
        // Check filter by problem, is optional
        if (!is_null(RequestContext::get("problem")))
        {
            ValidatorFactory::stringNotEmptyValidator()->validate(RequestContext::get("problem"), "problem");
            
            try
            {
                $this->problem = ProblemsDAO::getByAlias(RequestContext::get("problem"));
            }
            catch(Exception $e)
            {
                // Operation failed in the data layer
		throw new ApiException(ApiHttpErrors::invalidDatabaseOperation(), $e);
            }
            
            if (is_null($this->problem))
            {
                throw new ApiException(ApiHttpErrors::notFound("Problem selected not found."));
            }
        }  
    }   
            
    protected function GenerateResponse() 
    {        
        $runs_mask = null;
        
        // Get all runs for problem given        
        $runs_mask = new Runs( array (                
            "contest_id" => $this->contest->getContestId(),
            "status" => RequestContext::get("status"),
            "veredict"=> RequestContext::get("veredict"),
            "problem_id" => $this->problem->getProblemId(),
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
		$filtered['score'] = round((float)$filtered['score'], 4);
		$filtered['contest_score'] = round((float)$filtered['contest_score'], 2);
		array_push($result, $filtered);
	}

        // Add the run to the response
        $this->addResponse('runs', $result);               
        
        // All clear
        $this->addResponse("status", "ok");
    }    
}

?>
